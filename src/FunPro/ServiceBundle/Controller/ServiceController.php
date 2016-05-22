<?php

namespace FunPro\ServiceBundle\Controller;

use Exception;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\DriverBundle\CarEvents;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\DriverBundle\Event\CarEvent;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\ServiceBundle\Entity\Requested;
use FunPro\ServiceBundle\Form\ServiceType;
use FunPro\UserBundle\Entity\Device;
use FunPro\UserBundle\Entity\Message;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ServiceController
 *
 * @package FunPro\ServiceBundle\Controller
 *
 * @Rest\RouteResource("service", pluralize=false)
 * @Rest\NamePrefix("fun_pro_api_")
 *
 * @TODO: Move GCM to event listener or GCM listen to event
 * @TODO: GCM send request in http_kernel.terminated
 */
class ServiceController extends FOSRestController
{
    public function getForm(Requested $service)
    {
        $options['method'] = 'POST';
        $options['action'] = $this->generateUrl('fun_pro_api_post_service');

        $options['validation_groups'] = array('Create', 'Point');

        $requestFormat = $this->get('request_stack')->getCurrentRequest()->getRequestFormat('html');
        $options['csrf_protection'] = $requestFormat == 'html' ?: false;

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
     *              "class"="FunPro\ServiceBundle\Entity\Requested",
     *              "groups"={"Create", "Point"},
     *              "parsers"={
     *                  "Nelmio\ApiDocBundle\Parser\ValidationParser",
     *                  "Nelmio\ApiDocBundle\Parser\JmsMetadataParser",
     *              },
     *          },
     *      },
     *      output={
     *          "class"="FunPro\ServiceBundle\Entity\Requested",
     *          "groups"={"Public"},
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
     * @Rest\Post(path="/passenger/service")
     *
     * @Security("has_role('ROLE_PASSENGER') or has_role('ROLE_AGENT')")
     */
    public function postAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $service = new Requested();

        if ($this->getUser() instanceof Passenger) {
            $service->setPassenger($this->getUser());
        } else {
            $agent = $manager->getRepository('FunProAgentBundle:Agent')
                ->findOneByAdmin($this->getUser());
            $service->setAgent($agent);
            $service->setStartPoint($agent->getAddress()->getPoint());
        }

        $form = $this->getForm($service);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $manager->persist($service);
            $manager->flush();

            //TODO: Use Spatial Mysql Distance function for Mysql > 5.6.1
            $drivers = $this->getDoctrine()->getRepository('FunProDriverBundle:Driver')
                ->getAllAround($service->getStartPoint(), $this->getParameter('service.visible_radius'));

            $getDevices = function ($driver) {
                $devices = array();
                /** @var Device $device */
                foreach ($driver->getDevices()->toArray() as $device) {
                    if ($device->getStatus() == Device::STATUS_ACTIVE) {
                        $devices[] = $device;
                    }
                }

                return $devices;
            };

            $devices = array_map($getDevices, $drivers);
            if ($devices) {
                $devices = call_user_func_array('array_merge', $devices);
            }

            $data = array(
                'type' => 'service',
                'id' => $service->getId()
            );

            $message = (new Message())
                ->setData($data)
                ->setPriority(Message::PRIORITY_HIGH)
                ->setTimeToLive(15);

            $this->get('fun_pro_engine.gcm')->send($devices, $message);

            return $this->view($service, Response::HTTP_CREATED);
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Get a service
     *
     * @ApiDoc(
     *      section="Service",
     *      resource=true,
     *      output={
     *          "class"="FunPro\ServiceBundle\Entity\Requested",
     *          "groups"={"Passenger", "Driver", "Agent", "Admin", "Public", "Point"},
     *      },
     *      statusCodes={
     *          200="When success",
     *          403= {
     *              "when you are not a driver",
     *          },
     *      }
     * )
     *
     * @ParamConverter(name="service", class="FunPro\ServiceBundle\Entity\Requested")
     * @Security("is_authenticated()")
     *
     * @Rest\Get(name="get_service", path="/service/{id}", options={"method_prefix"=false})
     * @Rest\Get(name="get_passenger_service", path="/passenger/service/{id}", options={"method_prefix"=false})
     *
     * @param $id
     * @return \FOS\RestBundle\View\View
     */
    public function getAction(Request $request, $id)
    {
        $service = $request->attributes->get('service');
        $user = $this->getUser();

        if (!$user instanceof Driver and $service->getPassenger() != $user) {
            throw $this->createAccessDeniedException();
        }

        $context = (new Context())
            ->addGroups(array('Passenger', 'Driver', 'Public', 'Point', 'PassengerMobile', 'DriverMobile'));
        return $this->view($service, Response::HTTP_OK)
            ->setSerializationContext($context);
    }

    /**
     * Accept service by driver
     *
     * @ApiDoc(
     *      section="Service",
     *      resource=true,
     *      views={"driver"},
     *      output={
     *          "class"="FunPro\ServiceBundle\Entity\Requested",
     *          "groups"={"Public", "Driver"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          204="When success",
     *          403= {
     *              "when you are not driver",
     *          },
     *          409="When another driver accept this service",
     *      }
     * )
     *
     * @Security("is_authenticated()")
     *
     * @param $id
     * @return \FOS\RestBundle\View\View
     */
    public function acceptAction($id)
    {
        /** @var Driver $driver */
        $driver = $this->getUser();

        if (!$driver instanceof Driver) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $car = $em->getRepository('FunProDriverBundle:Car')->findOneBy(array(
            'driver' => $driver,
            'current' => true,
        ));

        $em->getConnection()->beginTransaction();
        try {
            $service = $em->find('FunProServiceBundle:Requested', $id,  \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);
            if ($service->getCar()) {
                throw new Exception($this->get('translator')->trans('this.service.done'), Response::HTTP_CONFLICT);
            }

            $service->setCar($car);
            $this->get('event_dispatcher')->dispatch(CarEvents::CAR_ACCEPT_SERVICE, new CarEvent($car));
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $e) {
            $em->getConnection()->rollBack();
            $em->close();
            $error = array(
                'code' => 0,
                'message' => $this->get('translator')->trans('this.service.done'),
            );
            return $this->view($error, Response::HTTP_CONFLICT);
        }

        if ($service->getPassenger()) {
            $data = array(
                'type' => 'service.accept',
                'id' => $service->getId()
            );

            $message = (new Message())
                ->setData($data)
                ->setPriority(Message::PRIORITY_HIGH)
                ->setTimeToLive(15);
            $this->get('fun_pro_engine.gcm')->send($service->getPassenger()->getDevices()->toArray(), $message);
        }

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
     * @ParamConverter("service", class="FunProServiceBundle:Requested")
     * @Security("has_role('ROLE_DRIVER') and service.getCar().getDriver() == user")
     *
     * @Rest\RequestParam(name="status", nullable=false, requirements="ready|finish", strict=true)
     * @Rest\RequestParam(name="distance", nullable=true, requirements="\d+", strict=true)
     * @Rest\RequestParam(name="price", nullable=true, requirements="\d+", strict=true)
     */
    public function patchStatusAction(Request $request, $id)
    {
        /** @var Requested $service */
        $service = $request->attributes->get('service');
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $manager = $this->getDoctrine()->getManager();
        $translator = $this->get('translator');

        if ($fetcher->get('status') == 'ready') {
            if ($service->getStartTime()) {
                return $this->view(null, Response::HTTP_CONFLICT);
            }
            $service->setStartTime(new \DateTime());
            $this->get('event_dispatcher')->dispatch(CarEvents::CAR_READY_SERVICE, new CarEvent($service->getCar()));
            $this->get('event_dispatcher')->dispatch(CarEvents::CAR_START_SERVICE, new CarEvent($service->getCar()));
            $manager->flush();

            $data = array(
                'message' => $translator->trans('car.is.in.your.place'),
                'title' => $translator->trans('dear.passenger'),
            );

            $message = (new Message())
                ->setBody($translator->trans('car.is.in.your.place'))
                ->setTitle($translator->trans('dear.passenger'))
                ->setData($data)
                ->setPriority(Message::PRIORITY_HIGH)
                ->setTimeToLive(30);

            $this->get('fun_pro_engine.gcm')->send($service->getPassenger()->getDevices()->toArray(), $message);

            return $this->view(null, Response::HTTP_NO_CONTENT);
        } else {
            if (is_null($service->getStartTime())) {
                return $this->view(null, Response::HTTP_BAD_REQUEST);
            } elseif ($service->getEndTime()) {
                return $this->view(null, Response::HTTP_CONFLICT);
            } else {
                if (is_null($fetcher->get('distance')) or is_null($fetcher->get('price'))) {
                    return $this->view(null, Response::HTTP_BAD_REQUEST);
                }

                $service->setDistance($fetcher->get('distance'));
                $service->setPrice($fetcher->get('price'));
                $service->setEndTime(new \DateTime());

                $this->get('event_dispatcher')->dispatch(CarEvents::CAR_END_SERVICE, new CarEvent($service->getCar()));
                $manager->flush();

                $message = (new Message())
                    ->setData(array('type' => 'service.finish', 'id' => $service->getId()))
                    ->setPriority(Message::PRIORITY_HIGH)
                    ->setTimeToLive(30);

                $this->get('fun_pro_engine.gcm')->send($service->getPassenger()->getDevices()->toArray(), $message);

                return $this->view(null, Response::HTTP_NO_CONTENT);
            }
        }

        return $this->view(null, Response::HTTP_BAD_REQUEST);
    }
}