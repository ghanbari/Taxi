<?php

namespace FunPro\ServiceBundle\Controller;

use Doctrine\ORM\PessimisticLockException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\DriverBundle\Exception\DriverNotFound;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\GeoBundle\Utility\Util;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\ServiceBundle\Entity\FloatingCost;
use FunPro\ServiceBundle\Entity\PropagationList;
use FunPro\ServiceBundle\Entity\Service;
use FunPro\ServiceBundle\Event\GetCarPointServiceEvent;
use FunPro\ServiceBundle\Event\ServiceEvent;
use FunPro\ServiceBundle\Exception\ServiceStatusException;
use FunPro\ServiceBundle\ServiceEvents;
use FunPro\ServiceBundle\Form\ServiceType;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ServiceController
 *
 * @package FunPro\ServiceBundle\Controller
 *
 * @Rest\RouteResource("service", pluralize=false)
 * @Rest\NamePrefix("fun_pro_service_api_")
 *
 */
class ServiceController extends FOSRestController
{
    public function getForm(Service $service)
    {
        $options['method'] = 'POST';
        $options['action'] = $this->generateUrl('fun_pro_service_api_post_service');
        $options['validation_groups'] = array('Create', 'Point');
        $options['allow_extra_fields'] = true;

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
     *          400={
     *              "When form validation failed.",
     *              "When propagation list is larger than allowed number(code: 1)",
     *              "When any driver are not online(code: 2)",
     *              "When specified Driver in propagationList is not found(code: 3)",
     *          },
     *          403= {
     *              "when csrf token is invalid",
     *              "when you are not a passenger or agent",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_PASSENGER') or has_role('ROLE_AGENT')")
     *
     * @Rest\RequestParam(name="pickup_name", nullable=false, strict=true, requirements=".{3,100}")
     * @Rest\RequestParam(name="dropoff_name", nullable=false, strict=true, requirements=".{3,100}")
     */
    public function postAction(Request $request)
    {
        $logger = $this->get('logger');
        $translator = $this->get('translator');
        $manager = $this->getDoctrine()->getManager();
        $fetcher = $this->get('fos_rest.request.param_fetcher');

        $service = new Service();
        $service->getExtraData()->add('dropoff_name', $fetcher->get('dropoff_name'));
        $service->getExtraData()->add('pickup_name', $fetcher->get('pickup_name'));

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
            $propagationList = $form['propagationList']->getData();
            $maxSize = $this->getParameter('service.propagation_list.max');
            if ($count = count($propagationList) and $count > $maxSize) {
                $logger->addError("you can select only $maxSize car for propagation list", array('count' => $count));
                $error = array(
                    'code' => 1,
                    'message' => $translator->trans('max.size.for.propagation.list.is.%maxSize%', array('%maxSize%' => $maxSize))
                );
                return $this->view($error, Response::HTTP_BAD_REQUEST);
            }

            if (!empty($propagationList)) {
                $logger->addInfo('Set propagationList');
                $propagationType = count($propagationList) === 1 ?
                    Service::PROPAGATION_TYPE_SINGLE : Service::PROPAGATION_TYPE_LIST;
                $service->setPropagationType($propagationType);

                $busyCounter = 0;
                foreach ($propagationList as $number => $driverId) {
                    /** @var Driver $driver */
                    $driver = $manager->getRepository('FunProDriverBundle:Driver')->getWithCar($driverId);

                    if (!$driver) {
                        $logger->addError('driver is not exists', array('driverId' => $driverId));
                        $error = array(
                            'code' => 3,
                            'message' => $translator->trans('driver.is.not.exists'),
                        );
                        return $this->view($error, Response::HTTP_BAD_REQUEST);
                    }

                    if ($driver->getCars()->first()->getStatus() !== Car::STATUS_WAKEFUL
                        and $driver->getCars()->first()->getStatus() !== Car::STATUS_SERVICE_IN
                        and $driver->getCars()->first()->getStatus() !== Car::STATUS_SERVICE_END
                    ) {
                        $logger->addNotice('driver is busy', array('driverId' => $driver->getId()));
                        $busyCounter++;
                        continue;
                    }

                    $propagationList = new PropagationList($service, $driver, $number - $busyCounter);
                    $manager->persist($propagationList);
                }
            }

            $manager->persist($service);
            $manager->flush();

            $logger->addInfo('Dispatch service requested event');
            $event = new ServiceEvent($service);
            try {
                $this->get('event_dispatcher')->dispatch(ServiceEvents::SERVICE_REQUESTED, $event);
            } catch (DriverNotFound $e) {
                $error = array(
                    'code' => 2,
                    'message' => $translator->trans($e->getMessage()),
                );
                return $this->view($error, Response::HTTP_BAD_REQUEST);
            }

            $manager->flush();

            return $this->view($service, Response::HTTP_CREATED)
                ->setSerializationContext($context);
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Cancel service
     *
     * @ApiDoc(
     *      section="Service",
     *      resource=true,
     *      views={"passenger"},
     *      statusCodes={
     *          204="When success",
     *          400={
     *              "Passenger can cancel service, only in n minute after request(code: 1)",
     *              "Reason is not found(code: 2)",
     *              "Service can be canceled when its status is requested, accepted or ready(code: 3)",
     *          },
     *          403= {
     *              "when service is not requested by this passenger",
     *          },
     *      }
     * )
     *
     * @ParamConverter(name="service", class="FunProServiceBundle:Service")
     * @Security("is_authenticated() and service.getPassenger() == user")
     *
     * @Rest\RequestParam(name="reason", requirements="\d+", nullable=false, strict=true)
     *
     * @param $id
     *
     * @return \FOS\RestBundle\View\View
     *
     * TODO: user get negative point
     */
    public function deleteAction(Request $request, $id)
    {
        /** @var Service $service */
        $service = $request->attributes->get('service');
        $logger = $this->get('logger');
        $translator = $this->get('translator');
        $manager = $this->getDoctrine()->getManager();
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $reasonId = $fetcher->get('reason', true);

        $authorizedTill = new \DateTime('-' . $this->getParameter('service.passenger.can_cancel_till'));
        if ($authorizedTill >= $service->getCreatedAt()) {
            $logger->addNotice('passenger can not cancel service, limited time');
            $error = array(
                'code' => 1,
                'message' => $translator->trans('you.can.cancel.service.only.in.one.minute'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $canceledReason = $manager->getRepository('FunProServiceBundle:CanceledReason')->find($reasonId);

        if (!$canceledReason) {
            $logger->addError('reason is not exists');
            $error = array(
                'code' => 2,
                'message' => $translator->trans('reason.is.not.exists'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $service->setCanceledBy($this->getUser());
        $service->setCanceledAt(new \DateTime());
        $service->setCanceledReason($canceledReason);

        $event = new ServiceEvent($service);
        try {
            $this->get('event_dispatcher')->dispatch(ServiceEvents::SERVICE_CANCELED, $event);
        } catch (ServiceStatusException $e) {
            $error = array(
                'code' => 3,
                'message' => $translator->trans('you.can.cancel.service.only.when.status.is.requested.or.accepted'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $manager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
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
     *
     * @Rest\RequestParam(name="latitude", nullable=false, requirements="\d+\.\d+", strict=true)
     * @Rest\RequestParam(name="longitude", nullable=false, requirements="\d+\.\d+", strict=true)
     */
    public function patchReadyAction(Request $request, $id)
    {
        $logger = $this->get('logger');
        $translator = $this->get('translator');
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $manager = $this->getDoctrine()->getManager();

        /** @var Service $service */

        $service = $request->attributes->get('service');

        $point = new Point($fetcher->get('longitude'), $fetcher->get('latitude'));
        $startPoint = $service->getStartPoint();
        $distance = Util::distance(
            $startPoint->getLatitude(),
            $startPoint->getLongitude(),
            $point->getLatitude(),
            $point->getLongitude()
        );

        if (($distance*1000) > $this->getParameter('service.driver.allowed_radius_for_ready')) {
            $logger->addInfo('driver is not in allowed radius till he can send ready alarm', array(
                'real' => $distance * 1000,
                'allowed' => $this->getParameter('service.driver.allowed_radius_for_ready'),
            ));
            $error = array(
                'code' => 1,
                'message' => $translator->trans('driver.is.away.from.start.point'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $event = new GetCarPointServiceEvent($service, $point);
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
     * @Rest\RequestParam(
     *      name="floatingCost",
     *      nullable=true,
     *      strict=true,
     *      requirements=@Assert\All({
     *          @Assert\Collection(
     *              fields={
     *                  "amount"=@Assert\Required({@Assert\NotBlank, @Assert\Type(type="numeric")}),
     *                  "description"=@Assert\Required({@Assert\NotBlank(), @Assert\Length(max="50")})
     *              }
     *          )
     *      })
     * )
     */
    public function patchFinishAction(Request $request, $id)
    {
        /** @var Service $service */
        $service = $request->attributes->get('service');
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $manager = $this->getDoctrine()->getManager();

        #TODO: check service status, it must be in service

        $service->setPrice($fetcher->get('price'));
        if ($floatingCosts = $fetcher->get('floatingCost')) {
            foreach ($floatingCosts as $floatCost) {
                $manager->persist(new FloatingCost($service, intval($floatCost['amount']), $floatCost['description']));
            }
        }

        #TODO: Convert CarStatusException to view
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
     *              "when you have not access to service",
     *          },
     *      }
     * )
     *
     * @ParamConverter(name="service", class="FunPro\ServiceBundle\Entity\Service")
     *
     * @param $id
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getAction(Request $request, $id)
    {
        $logger = $this->get('logger');
        $translator = $this->get('translator');
        $context = new Context();
        $context->addGroups(array('Public', 'Point', 'Plaque', 'PassengerMobile', 'DriverMobile', 'Car'));

        $service = $request->attributes->get('service');
        $user = $this->getUser();

        if ($user instanceof Driver and $service->getCar() === null) {
            $message = $this->getDoctrine()->getRepository('FunProUserBundle:Message')
                ->getRequestMessageToDriver($user, $service);
            if (!$message) {
                $logger->addWarning('how driver find out this id?, he no give any notification');
                throw $this->createAccessDeniedException($translator->trans('you.not.recive.any.request'));
            }
        } elseif ($user instanceof Driver and $service->getCar()->getDriver() === $user) {
            $context->addGroup('Driver');
        } elseif ($service->getPassenger() === $user) {
            $context->addGroups(array('Passenger', 'PropagationList', 'Cost'));
        } elseif ($service->getAgent() and $service->getAgent()->getAdmin() === $user) {
            $context->addGroup('Agent');
        } else {
            throw $this->createAccessDeniedException();
        }

        $context->setMaxDepth(3);
        return $this->view($service, Response::HTTP_OK)
            ->setSerializationContext($context);
    }
}
