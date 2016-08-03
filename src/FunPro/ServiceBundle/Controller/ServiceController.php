<?php

namespace FunPro\ServiceBundle\Controller;

use Doctrine\ORM\PessimisticLockException;
use Exception;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\DriverBundle\CarEvents;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\DriverBundle\Event\CarEvent;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\ServiceBundle\Entity\FloatingCost;
use FunPro\ServiceBundle\Entity\PropagationList;
use FunPro\ServiceBundle\Entity\Service;
use FunPro\ServiceBundle\Event\GetCarPointServiceEvent;
use FunPro\ServiceBundle\Event\ServiceEvent;
use FunPro\ServiceBundle\ServiceEvents;
use FunPro\ServiceBundle\Form\ServiceType;
use FunPro\UserBundle\Entity\Device;
use FunPro\UserBundle\Entity\Message;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class ServiceController
 *
 * @package FunPro\ServiceBundle\Controller
 *
 * @Rest\RouteResource("service", pluralize=false)
 * @Rest\NamePrefix("fun_pro_service_api_")
 *
 * @TODO: Move GCM to event listener or GCM listen to event
 * @TODO: GCM send request in http_kernel.terminated
 */
class ServiceController extends FOSRestController
{
    public function getForm(Service $service)
    {
        $options['method'] = 'POST';
        $options['action'] = $this->generateUrl('fun_pro_service_api_post_service');
        $options['validation_groups'] = array('Create', 'Point');

        $form = $this->createForm(new ServiceType(), $service, $options);
        return $form;
    }

    /**
     * Create a service
     *
     * @ApiDoc(
     *      section="Service",
     *      resource=true,
     *      views={"passenger"},
     *      input={
     *          "class"="FunPro\ServiceBundle\Form\ServiceType",
     *          "data"={
     *              "class"="FunPro\ServiceBundle\Entity\Service",
     *              "groups"={"Create", "Point"},
     *              "parsers"={
     *                  "Nelmio\ApiDocBundle\Parser\ValidationParser",
     *                  "Nelmio\ApiDocBundle\Parser\JmsMetadataParser",
     *              },
     *          },
     *      },
     *      output={
     *          "class"="FunPro\ServiceBundle\Entity\Service",
     *          "groups"={"Public", "Passenger", "Point"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          201="When success",
     *          400="When form validation failed.",
     *          403= {
     *              "when csrf token is invalid",
     *              "when you are not a passenger or agent",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_PASSENGER') or has_role('ROLE_AGENT')")
     */
    public function postAction(Request $request)
    {
        $logger = $this->get('logger');
        $manager = $this->getDoctrine()->getManager();
        $service = new Service();
        $context = new Context();
        $context->addGroups(['Public', 'Point', 'PropagationList']);

        if ($this->getUser() instanceof Passenger) {
            $logger->addInfo('set passenger');
            $service->setPassenger($this->getUser());
            $context->addGroup('Passenger');
        } else {
            $logger->addInfo('set agent');
            $agent = $manager->getRepository('FunProAgentBundle:Agent')
                ->findOneByAdmin($this->getUser());
            $service->setAgent($agent);
            $service->setStartPoint($agent->getAddress()->getPoint());
            $context->addGroup('Agent');
        }

        $form = $this->getForm($service);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $propagationCars = $form['propagationList']->getData();
            if (!empty($propagationCars)) {
                $logger->addInfo('Set propagationList');
                $propagationType = count($propagationCars) === 1 ?
                    Service::PROPAGATION_TYPE_SINGLE : Service::PROPAGATION_TYPE_LIST;
                $service->setPropagationType($propagationType);

                $number = 1;
                foreach ($propagationCars as $car) {
                    $propagationCar = new PropagationList($service, $car, $number);
                    $manager->persist($propagationCar);
                    $number++;
                }
            }

            $logger->addInfo('Dispatch service requested event');
            $event = new ServiceEvent($service);
            $this->get('event_dispatcher')->dispatch(ServiceEvents::SERVICE_REQUESTED, $event);

            $manager->persist($service);
            $manager->flush();

            return $this->view($service, Response::HTTP_CREATED)
                ->setSerializationContext($context);
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Accept service by driver
     *
     * @ApiDoc(
     *      section="Service",
     *      resource=true,
     *      views={"driver"},
     *      statusCodes={
     *          204="When success",
     *          400={
     *              "You have not active car(code: 1)",
     *          },
     *          403={
     *              "when you are not driver",
     *          },
     *          404={
     *              "service is not exists(code: 1)",
     *          },
     *          409="When another driver accept this service(code: 1)",
     *      }
     * )
     *
     * @Security("has_role('ROLE_DRIVER')")
     *
     * @Rest\RequestParam(name="latitude", nullable=false, requirements="\d+\.\d+", strict=true)
     * @Rest\RequestParam(name="longitude", nullable=false, requirements="\d+\.\d+", strict=true)
     *
     * @param $id
     * @return \FOS\RestBundle\View\View
     */
    public function acceptAction($id)
    {
        $logger = $this->get('logger');
        $translator = $this->get('translator');
        $serializer = $this->get('jms_serializer');
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $manager = $this->getDoctrine()->getManager();
        $driver = $this->getUser();
        $point = new Point($fetcher->get('longitude'), $fetcher->get('latitude'));

        if (!$driver instanceof Driver) {
            $context = SerializationContext::create()->setGroups('Public', 'Car');
            $logger->addError(
                'normal user have driver permission',
                array($serializer->serialize($driver, 'json', $context))
            );
            throw $this->createAccessDeniedException();
        }

        $car = $manager->getRepository('FunProDriverBundle:Car')->findOneBy(array(
            'driver' => $driver,
            'current' => true,
        ));

        if (!$car) {
            $context = SerializationContext::create()->setGroups('Public', 'Car');
            $logger->addError('driver have not car', array($serializer->serialize($driver, 'json', $context)));
            $error = array(
                'code' => 1,
                'message' => 'you.have.not.active.car',
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $manager->getConnection()->beginTransaction();
        try {
            $service = $manager->find('FunProServiceBundle:Service', $id, \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);

            if (!$service) {
                $logger->addError('service is not exists', array('service' => $id));
                $error = array(
                    'code' => 1,
                    'message' => $translator->trans('service.is.not.exists'),
                );
                return $this->view($error, Response::HTTP_NOT_FOUND);
            }

            if ($service->getCar()) {
                $context = SerializationContext::create()->setGroups(array('Car'));
                $logger->addNotice('service was taken', array($serializer->serialize($service, 'json', $context)));
                $error = array(
                    'code' => 1,
                    'message' => $translator->trans('this.service.done')
                );
                return $this->view($error, Response::HTTP_CONFLICT);
            }

            $service->setCar($car);
            $event = new GetCarPointServiceEvent($service, $point);
            $this->get('event_dispatcher')->dispatch(ServiceEvents::SERVICE_ACCEPTED, $event);
            $manager->flush();
            $manager->getConnection()->commit();
        } catch (PessimisticLockException $e) {
            $manager->getConnection()->rollBack();
            $manager->close();

            $context = SerializationContext::create()->setGroups(array('Car'));
            $logger->addNotice('service was taken', array($serializer->serialize($service, 'json', $context)));
            $error = array(
                'code' => 1,
                'message' => $translator->trans('this.service.done'),
            );
            return $this->view($error, Response::HTTP_CONFLICT);
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Change service status into ready
     *
     * @ApiDoc(
     *      section="Service",
     *      resource=true,
     *      views={"driver"},
     *      statusCodes={
     *          204="When success",
     *          400={
     *              "When you will finish a service that not started",
     *              "when status is not valid",
     *          },
     *          403= {
     *              "when csrf token is invalid",
     *              "when you are not a driver or driver of this service",
     *          },
     *          409= {
     *              "when you will start a started service or finish a finished service",
     *          },
     *      }
     * )
     *
     * @ParamConverter("service", class="FunProServiceBundle:Service")
     * @Security("has_role('ROLE_DRIVER') and service.getCar().getDriver() == user")
     */
    public function patchReadyAction(Request $request, $id)
    {
        /** @var Service $service */
        $service = $request->attributes->get('service');
        $manager = $this->getDoctrine()->getManager();
        $logger = $this->get('logger');

        #TODO: check service status, it must be accepted

        $event = new ServiceEvent($service);
        $this->get('event_dispatcher')->dispatch(ServiceEvents::SERVICE_READY, $event);
        $manager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Change service status into start
     *
     * @ApiDoc(
     *      section="Service",
     *      resource=true,
     *      views={"driver"},
     *      statusCodes={
     *          204="When success",
     *          400={
     *              "When you will finish a service that not started",
     *              "when status is not valid",
     *          },
     *          403= {
     *              "when csrf token is invalid",
     *              "when you are not a driver or driver of this service",
     *          },
     *          409= {
     *              "when you will start a started service or finish a finished service",
     *          },
     *      }
     * )
     *
     * @ParamConverter("service", class="FunProServiceBundle:Service")
     * @Security("has_role('ROLE_DRIVER') and service.getCar().getDriver() == user")
     */
    public function patchStartAction(Request $request, $id)
    {
        /** @var Service $service */
        $service = $request->attributes->get('service');
        $manager = $this->getDoctrine()->getManager();
        $logger = $this->get('logger');

        #TODO: check service status, it must be ready

        $event = new ServiceEvent($service);
        $this->get('event_dispatcher')->dispatch(ServiceEvents::SERVICE_START, $event);
        $manager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * update service status
     *
     * @ApiDoc(
     *      section="Service",
     *      resource=true,
     *      views={"driver"},
     *      statusCodes={
     *          204="When success",
     *          400={
     *              "When you will finish a service that not started",
     *              "when status is not valid",
     *          },
     *          403= {
     *              "when csrf token is invalid",
     *              "when you are not a driver or driver of this service",
     *          },
     *          409= {
     *              "when you will start a started service or finish a finished service",
     *          },
     *      }
     * )
     *
     * @ParamConverter("service", class="FunProServiceBundle:Service")
     * @Security("has_role('ROLE_DRIVER') and service.getCar().getDriver() == user")
     *
     * @Rest\RequestParam(name="price", nullable=false, requirements="\d+", strict=true)
     * @Rest\RequestParam(name="floatingCost", nullable=true, map=true, strict=true)
     */
    public function patchFinishAction(Request $request, $id)
    {
        /** @var Service $service */
        $service = $request->attributes->get('service');
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $manager = $this->getDoctrine()->getManager();

        #TODO: check service status, it must be in service

        $service->setPrice($fetcher->get('price'));
        foreach ($fetcher->get('floatingCost') as $floatCost) {
            $manager->persist(new FloatingCost($service, $floatCost['cost'], $floatCost['description']));
        }

        $event = new ServiceEvent($service);
        $this->get('event_dispatcher')->dispatch(ServiceEvents::SERVICE_FINISH, $event);
        $manager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get a service
     *
     * @ApiDoc(
     *      section="Service",
     *      resource=true,
     *      output={
     *          "class"="FunPro\ServiceBundle\Entity\Service",
     *          "groups"={"Passenger", "Driver", "Agent", "Admin", "Public", "Point"},
     *      },
     *      statusCodes={
     *          200="When success",
     *          403= {
     *              "when you are not a user",
     *          },
     *      }
     * )
     *
     * @ParamConverter(name="service", class="FunPro\ServiceBundle\Entity\Service")
     * @Security("has_role('ROLE_PASSENGER') or has_role('ROLE_DRIVER') or has_role('ROLE_AGENT')")
     *
     * @param $id
     * @return \FOS\RestBundle\View\View
     */
    public function getAction(Request $request, $id)
    {
        $context = new Context();
        $context->addGroups(array('Public', 'Point', 'Plaque', 'PassengerMobile', 'DriverMobile', 'Car'));

        $service = $request->attributes->get('service');
        $user = $this->getUser();

        if ($user instanceof Driver) {
            $context->addGroup('Driver');
        } elseif ($service->getPassenger() == $user) {
            $context->addGroups(array('Passenger', 'PropagationList', 'Cost'));
        } elseif ($service->getAgent() and $service->getAgent()->getAdmin() == $user) {
            $context->addGroup('Agent');
        } else {
            throw $this->createAccessDeniedException();
        }

        $context->setMaxDepth(3);
        return $this->view($service, Response::HTTP_OK)
            ->setSerializationContext($context);
    }
}
