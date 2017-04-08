<?php

namespace FunPro\ServiceBundle\Controller;

use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\ORM\PessimisticLockException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\DriverBundle\Exception\CarStatusException;
use FunPro\DriverBundle\Exception\DriverNotFoundException;
use FunPro\GeoBundle\Utility\Util;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\ServiceBundle\Entity\FloatingCost;
use FunPro\ServiceBundle\Entity\PropagationList;
use FunPro\ServiceBundle\Entity\Service;
use FunPro\ServiceBundle\Event\GetCarPointServiceEvent;
use FunPro\ServiceBundle\Event\ServiceEvent;
use FunPro\ServiceBundle\Exception\ServiceStatusException;
use FunPro\ServiceBundle\Form\ServiceType;
use FunPro\ServiceBundle\ServiceEvents;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Service\Base\Location\AddressLocation;
use Ivory\GoogleMap\Service\Base\Location\CoordinateLocation;
use Ivory\GoogleMap\Service\Base\TravelMode;
use Ivory\GoogleMap\Service\Base\UnitSystem;
use Ivory\GoogleMap\Service\Direction\Request\DirectionRequest;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\VarDumper\VarDumper;

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
     */
    public function postAction(Request $request)
    {
        $logger = $this->get('logger');
        $translator = $this->get('translator');
        $manager = $this->getDoctrine()->getManager();
        $baseCost = $this->getDoctrine()->getRepository('FunProFinancialBundle:BaseCost')
            ->getLast();

        $service = new Service();
        $service->setBaseCost($baseCost);

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

                    #FIXME: If driver have multi car, do this work probably?
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
//            $manager->flush();

            $logger->addInfo('Dispatch service requested event');
            $event = new ServiceEvent($service);
            try {
                $this->get('event_dispatcher')->dispatch(ServiceEvents::SERVICE_REQUESTED, $event);
            } catch (DriverNotFoundException $e) {
                /** @Ignore */
                $message = $translator->trans($e->getMessage());
                $error = array(
                    'code' => 2,
                    'message' => $message,
                );
                return $this->view($error, Response::HTTP_BAD_REQUEST);
            }

            $manager->flush();

            return $this->view($service, Response::HTTP_CREATED)
                ->setSerializationContext($context);
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }

    public function getForm(Service $service)
    {
        $requestFormat = $this->get('request_stack')->getCurrentRequest()->getRequestFormat('html');
        $options['csrf_protection'] = $requestFormat === 'html' ?: false;
        $options['method'] = 'POST';
        $options['action'] = $this->generateUrl('fun_pro_service_api_post_service');
        $options['validation_groups'] = array('Create', 'Point');
        $options['allow_extra_fields'] = true;

        $form = $this->createForm(new ServiceType(), $service, $options);
        return $form;
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
     *          404="Service is not exists.",
     *          403= {
     *              "When service is not requested by this passenger",
     *          },
     *      }
     * )
     *
     * @ParamConverter(name="service", class="FunProServiceBundle:Service")
     * @Security("has_role('ROLE_PASSENGER') and service.getPassenger() == user")
     *
     * @Rest\QueryParam(name="reason", requirements="\d+", nullable=false, strict=true)
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
     *              "Car status must be wakeful or in_service(code: 2)",
     *              "Service status must be requested(code: 3)",
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
     * @Rest\RequestParam(name="latitude", allowBlank=false, nullable=false, requirements="\d+\.\d+", strict=true)
     * @Rest\RequestParam(name="longitude", allowBlank=false, nullable=false, requirements="\d+\.\d+", strict=true)
     *
     * @param $id
     *
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
            try {
                $this->get('event_dispatcher')->dispatch(ServiceEvents::SERVICE_ACCEPTED, $event);
                $manager->flush();
            } catch (CarStatusException $e) {
                $error = array(
                    'code' => 2,
                    'message' => $e->getMessage(),
                );
                return $this->view($error, Response::HTTP_BAD_REQUEST);
            } catch (ServiceStatusException $e) {
                $error = array(
                    'code' => 3,
                    'message' => $e->getMessage(),
                );
                return $this->view($error, Response::HTTP_BAD_REQUEST);
            }
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
     *              "Driver is away from start point(code: 1)",
     *              "Car status must be prepare or ready(code: 2)",
     *              "Service status is not accepted(code: 3)",
     *          },
     *          403= {
     *              "when you are not a driver or driver of this service",
     *          },
     *      }
     * )
     *
     * @ParamConverter("service", class="FunProServiceBundle:Service")
     * @Security("has_role('ROLE_DRIVER') and service.getCar().getDriver() == user")
     *
     * @Rest\RequestParam(name="latitude", allowBlank=false, nullable=false, requirements="\d+\.\d+", strict=true)
     * @Rest\RequestParam(name="longitude", allowBlank=false, nullable=false, requirements="\d+\.\d+", strict=true)
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

        if (($distance * 1000) > $this->getParameter('service.driver.allowed_radius_for_ready')) {
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
        try {
            $this->get('event_dispatcher')->dispatch(ServiceEvents::SERVICE_READY, $event);
            $manager->flush();
        } catch (CarStatusException $e) {
            $error = array(
                'code' => 2,
                'message' => $e->getMessage(),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        } catch (ServiceStatusException $e) {
            $error = array(
                'code' => 3,
                'message' => $e->getMessage(),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

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
     *              "When car status is not ready(code: 1)",
     *              "When service status is not ready(code: 2)",
     *          },
     *          403= {
     *              "when you are not a driver or driver of this service",
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

        $event = new ServiceEvent($service);
        try {
            $this->get('event_dispatcher')->dispatch(ServiceEvents::SERVICE_START, $event);
            $manager->flush();
        } catch (CarStatusException $e) {
            $error = array(
                'code' => 1,
                'message' => $e->getMessage(),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        } catch (ServiceStatusException $e) {
            $error = array(
                'code' => 2,
                'message' => $e->getMessage(),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
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
     *              "Invalid format for floating cost or more that ten item is send(code: 2)",
     *              "When car status is not in_service or in_and_accept or in_and_prepare(code: 3)",
     *              "When service is not started(code: 4)",
     *          },
     *          403= {
     *              "when you are not a driver or driver of this service",
     *          },
     *      }
     * )
     *
     * @ParamConverter("service", class="FunProServiceBundle:Service")
     * @Security("has_role('ROLE_DRIVER') and service.getCar().getDriver() == user")
     *
     * @Rest\RequestParam(name="floatingCost", nullable=true, strict=true)
     */
    public function patchFinishAction(Request $request, $id)
    {
        /** @var Service $service */
        $service = $request->attributes->get('service');
        $logger = $this->get('logger');
        $translator = $this->get('translator');
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $manager = $this->getDoctrine()->getManager();

        $floatingCosts = json_decode($fetcher->get('floatingCost'), true);
        if ($floatingCosts) {
            $validator = $this->get('validator');
            $errors = $validator->validate(
                $floatingCosts,
                array(
                    new Assert\All(
                        new Assert\Collection(array('fields' => array(
                            'amount' => new Assert\Required(array(new Assert\NotBlank(), new Assert\Type('numeric'))),
                            'description' => new Assert\Required(array(new Assert\NotBlank(), new Assert\Length(array('max' => 50)))))
                        ))),
                    new Assert\Count(array('max' => 10))
                )
            );

            if (count($errors)) {
                $logger->addError('invalid format for floating costs');
                $error = array(
                    'code' => 2,
                    'message' => $translator->trans('invalid.format.for.floatin.cost'),
                );
                return $this->view($error, Response::HTTP_BAD_REQUEST);
            }

            foreach ($floatingCosts as $floatCost) {
                $manager->persist(new FloatingCost($service, intval($floatCost['amount']), $floatCost['description']));
            }
        }

        $event = new ServiceEvent($service);
        try {
            $this->get('event_dispatcher')->dispatch(ServiceEvents::SERVICE_FINISH, $event);
            $manager->flush();
        } catch (CarStatusException $e) {
            $error = array(
                'code' => 3,
                'message' => $e->getMessage(),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        } catch (ServiceStatusException $e) {
            $error = array(
                'code' => 4,
                'message' => $e->getMessage(),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get last service of user
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
     *          204="When any service is not exists",
     *          403= {
     *              "when you are not login",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_PASSENGER') or has_role('ROLE_DRIVER')")
     */
    public function getLastAction()
    {
        $repository = $this->getDoctrine()->getRepository('FunProServiceBundle:Service');
        $user = $this->getUser();
        $context = new Context();
        $context->addGroups(array('Public', 'Point', 'Plaque', 'PassengerMobile', 'DriverMobile', 'Car', 'Cost'));

        if ($user instanceof Driver) {
            $service = $repository->getLastServiceOfDriver($user);
            $context->addGroups(array('Driver'));
        } elseif ($user instanceof Passenger) {
            $context->addGroups(array('Passenger', 'PropagationList', 'DriverInfo'));
            $service = $repository->getLastServiceOfPassenger($user);
        } else {
            $this->get('logger')->addError('user type is not supported');
            return $this->view(null, Response::HTTP_NOT_FOUND);
        }

        $statusCode = $service ? Response::HTTP_OK : Response::HTTP_NO_CONTENT;
        return $this->view($service, $statusCode)
            ->setSerializationContext($context);
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
     * @Rest\Get(requirements={"id"="\d+"})
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
        $context->addGroups(array('Public', 'Point', 'Plaque', 'PassengerMobile', 'DriverMobile', 'Car', 'Cost'));

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
            $context->addGroups(array('Passenger', 'PropagationList', 'DriverInfo'));
        } elseif ($service->getAgent() and $service->getAgent()->getAdmin() === $user) {
            $context->addGroup('Agent');
        } else {
            throw $this->createAccessDeniedException();
        }

        $context->setMaxDepth(3);
        return $this->view($service, Response::HTTP_OK)
            ->setSerializationContext($context);
    }

    /**
     * Calculate service price
     *
     * @ApiDoc(
     *      section="Service",
     *      resource=true,
     *      statusCodes={
     *          200="When success",
     *      }
     * )
     *
     * @Rest\QueryParam(name="origin_lat", requirements="\d+\.\d+", strict=true, allowBlank=false, nullable=false)
     * @Rest\QueryParam(name="origin_lng", requirements="\d+\.\d+", strict=true, allowBlank=false, nullable=false)     *
     * @Rest\QueryParam(name="des_lat", requirements="\d+\.\d+", strict=true, allowBlank=false, nullable=false)
     * @Rest\QueryParam(name="des_lng", requirements="\d+\.\d+", strict=true, allowBlank=false, nullable=false)
     */
    public function getCalculatePriceAction()
    {
        $fetcher = $this->get('fos_rest.request.param_fetcher');

        $request = new DirectionRequest(
            new CoordinateLocation(new Coordinate($fetcher->get('origin_lat'), $fetcher->get('origin_lng'))),
            new CoordinateLocation(new Coordinate($fetcher->get('des_lat'), $fetcher->get('des_lng')))
        );

        $request->setUnitSystem(UnitSystem::METRIC);
        $request->setTravelMode(TravelMode::DRIVING);
        $request->setProvideRouteAlternatives(true);

        $response = $this->container->get('ivory.google_map.direction')->route($request);

        $result = array();
        if (count($response->getRoutes()) > 0) {
            $baseCost = $this->getDoctrine()->getRepository('FunProFinancialBundle:BaseCost')->getLast();

            $routes = $response->getRoutes();
            $legs = $routes[0]->getLegs();
            $distance = $legs[0]->getDistance()->getValue();
            $price = $baseCost->getEntranceFee() + ($baseCost->getCostPerMeter() * $distance);
            $price -= ($price * $baseCost->getDiscountPercent()) / 100;

            $result['distance'] = $distance;
            $result['duration'] = $legs[0]->getDuration()->getValue();
            $result['price'] = $price;

            return $this->view($result, Response::HTTP_OK);
        } else {
            return $this->view(null, Response::HTTP_NO_CONTENT);
        }
    }
}
