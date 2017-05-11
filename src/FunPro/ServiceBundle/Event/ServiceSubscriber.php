<?php

namespace FunPro\ServiceBundle\Event;

use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\DriverBundle\CarEvents;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Event\GetMoveCarEvent;
use FunPro\FinancialBundle\Entity\RegionBasePrice;
use FunPro\FinancialBundle\Entity\Transaction;
use FunPro\FinancialBundle\Event\PaymentEvent;
use FunPro\FinancialBundle\FinancialEvents;
use FunPro\ServiceBundle\Entity\Service;
use FunPro\ServiceBundle\Entity\ServiceLog;
use FunPro\ServiceBundle\Exception\ServiceStatusException;
use FunPro\ServiceBundle\ServiceEvents;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Service\Base\Location\CoordinateLocation;
use Ivory\GoogleMap\Service\Base\TravelMode;
use Ivory\GoogleMap\Service\Base\UnitSystem;
use Ivory\GoogleMap\Service\Direction\DirectionService;
use Ivory\GoogleMap\Service\Direction\Request\DirectionRequest;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ServiceSubscriber
 *
 * @package FunPro\ServiceBundle\Event
 */
class ServiceSubscriber implements EventSubscriberInterface
{
    /**
     * @var Registry $doctrine
     */
    private $doctrine;

    /**
     * @var Logger $logger
     */
    private $logger;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var DirectionService
     */
    private $directionService;

    /**
     * @param Registry         $doctrine
     * @param Logger           $logger
     * @param Serializer       $serializer
     * @param DirectionService $directionService
     */
    public function __construct(Registry $doctrine, Logger $logger, Serializer $serializer, DirectionService $directionService)
    {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->directionService = $directionService;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            ServiceEvents::SERVICE_REQUESTED => array(
                array('onServiceRequest', 10),
            ),
            ServiceEvents::SERVICE_CANCELED => array(
                array('checkServiceStatusPreCancel', 150),
                array('onServiceCanceled', 10),
            ),
            ServiceEvents::SERVICE_ACCEPTED => array(
                array('checkServiceStatusPreAccept', 150),
                array('onServiceAccept', 10),
            ),
            ServiceEvents::SERVICE_REJECTED => array(
                array('checkServiceStatusPreReject', 150),
                array('onServiceReject', 10),
            ),
            ServiceEvents::SERVICE_READY => array(
                array('checkServiceStatusPreReady', 150),
                array('onServiceReady', 10),
            ),
            ServiceEvents::SERVICE_START => array(
                array('checkServiceStatusPreStart', 150),
                array('onServiceStart', 10),
            ),
            ServiceEvents::SERVICE_FINISH => array(
                array('checkServiceStatusPreFinish', 150),
                array('onServiceFinish', 10),
                array('autoPay', 5),
            ),
            CarEvents::CAR_MOVE => array(
                array('onService', 10),
            ),
            FinancialEvents::PAYMENT_EVENT => array(
//                array('calculateRealPrice', 80),
                array('onServicePayed', 10),
            ),
        );
    }

    /**
     * @param ServiceEvent $event
     */
    public function onServiceRequest(ServiceEvent $event)
    {
        $service = $event->getService();
        $service->setDistance($this->calculateDistance($service));
        try {
            $service->calculatePrice();
        } catch (\RuntimeException $e) {
            $this->logger->addError('distance can not be zero', array('distance' => $service->getDistance()));
        }

        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_REQUESTED);
        $this->doctrine->getManager()->persist($log);

        $this->logger->addInfo('New service is requested', array('service' => $event->getService()->getId()));
    }

    /**
     * @param ServiceEvent $event
     */
    public function checkServiceStatusPreCancel(ServiceEvent $event)
    {
        $service = $event->getService();

        $allowedStatus = array(
            ServiceLog::STATUS_REQUESTED,
            ServiceLog::STATUS_ACCEPTED,
            ServiceLog::STATUS_READY,
        );

        if (!in_array($service->getStatus(), $allowedStatus)) {
            $logContext = SerializationContext::create()
                ->setGroups(array('Public', 'ServiceLogs'));
            $this->logger->addNotice(
                'passenger can cancel service only when status is requested, accepted or ready',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be requested');
        }
    }

    /**
     * @param ServiceEvent $event
     */
    public function onServiceCanceled(ServiceEvent $event)
    {
        $service = $event->getService();

        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_CANCELED);
        $this->doctrine->getManager()->persist($log);

        $this->logger->addInfo('Service was canceled', array('service' => $service->getId()));
    }

    /**
     * @param GetCarPointServiceEvent $event
     */
    public function checkServiceStatusPreAccept(GetCarPointServiceEvent $event)
    {
        $service = $event->getService();

        if ($service->getStatus() !== ServiceLog::STATUS_REQUESTED) {
            $logContext = SerializationContext::create()
                ->setGroups(array('Public', 'ServiceLogs'));
            $this->logger->addError(
                'Service status must be requested till it can change into accepted',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be requested');
        }
    }

    /**
     * @param GetCarPointServiceEvent $event
     */
    public function onServiceAccept(GetCarPointServiceEvent $event)
    {
        $service = $event->getService();

        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_ACCEPTED);
        $this->doctrine->getManager()->persist($log);

        $this->logger->addInfo('the driver accept service', array('service' => $service->getId()));
    }

    /**
     * @param GetCarServiceEvent $event
     */
    public function checkServiceStatusPreReject(GetCarServiceEvent $event)
    {
        $service = $event->getService();
        #TODO: when multi driver select for service, when first driver accept, service status is changed
        if ($service->getStatus() !== ServiceLog::STATUS_REQUESTED) {
            $logContext = SerializationContext::create()
                ->setGroups(array('Public', 'ServiceLogs'));
            $this->logger->addError(
                'Service status must be requested till it can change into rejected',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be requested');
        }
    }

    /**
     * @param GetCarServiceEvent $event
     */
    public function onServiceReject(GetCarServiceEvent $event)
    {
        $service = $event->getService();

        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_REJECTED);
        $this->doctrine->getManager()->persist($log);

        $this->logger->addInfo('the driver reject service', array('service' => $service->getId()));
    }

    /**
     * @param GetCarPointServiceEvent $event
     */
    public function checkServiceStatusPreReady(GetCarPointServiceEvent $event)
    {
        $service = $event->getService();

        if ($service->getStatus() !== ServiceLog::STATUS_ACCEPTED
            and $service->getStatus() !== ServiceLog::STATUS_READY
        ) {
            $logContext = SerializationContext::create()
                ->setGroups(array('Public', 'ServiceLogs'));
            $this->logger->addError(
                'Service status must be accepted till it can change into ready',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be accepted');
        }
    }

    /**
     * @param GetCarPointServiceEvent $event
     */
    public function onServiceReady(GetCarPointServiceEvent $event)
    {
        $service = $event->getService();

        # Unique db key on status & service
        if ($service->getStatus() === ServiceLog::STATUS_ACCEPTED) {
            $log = new ServiceLog($event->getService(), ServiceLog::STATUS_READY);
            $this->doctrine->getManager()->persist($log);
        }

        $this->logger->addInfo('Service is ready', array('service' => $service->getId()));
    }

    /**
     * @param ServiceEvent $event
     */
    public function checkServiceStatusPreStart(ServiceEvent $event)
    {
        $service = $event->getService();

        if ($service->getStatus() !== ServiceLog::STATUS_READY and $service->getStatus() !== ServiceLog::STATUS_START) {
            $logContext = SerializationContext::create()
                ->setGroups(array('Public', 'ServiceLogs'));
            $this->logger->addError(
                'Service status must be ready till it can change into started',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be ready');
        }
    }

    /**
     * @param ServiceEvent $event
     */
    public function onServiceStart(ServiceEvent $event)
    {
        $service = $event->getService();

        if ($service->getStatus() === ServiceLog::STATUS_READY) {
            $log = new ServiceLog($event->getService(), ServiceLog::STATUS_START);
            $this->doctrine->getManager()->persist($log);
        }

        $this->logger->addInfo('Service is started', array('service' => $service->getId()));
    }

    /**
     * @param GetMoveCarEvent $event
     */
    public function onService(GetMoveCarEvent $event)
    {
        $car = $event->getCar();
        $status = $car->getStatus();

        if ($status === Car::STATUS_SERVICE_IN or $status === Car::STATUS_SERVICE_IN_AND_ACCEPT
            or $status === Car::STATUS_SERVICE_IN_AND_PREPARE
        ) {
            $service = $this->doctrine->getRepository('FunProServiceBundle:Service')
                ->getDoingServiceFilterByCar($car);

            if ($service) {
                if ($service->getRoute()) {
                    $route = $service->getRoute();
                    $route->addPoint($event->getCurrentLocation());
                    $service->setRoute(clone $route);
                } else {
                    $service->setRoute(new LineString(array($service->getStartPoint(), $event->getCurrentLocation())));
                }
            }
        }
    }

    /**
     * @param ServiceEvent $event
     */
    public function checkServiceStatusPreFinish(ServiceEvent $event)
    {
        $service = $event->getService();

        if ($service->getStatus() !== ServiceLog::STATUS_START) {
            $logContext = SerializationContext::create()
                ->setGroups(array('Public', 'ServiceLogs'));
            $this->logger->addError(
                'Service status must be started till it can change into finished',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be started');
        }

        #FIXME: if passenger will end service before he arrive to specified end point.
//        if ($service->getEndPoint()->getLatitude()) {
//            $route = clone $service->getRoute();
//            if ($route->count() === 0) {
//                $route->attach($service->getStartPoint());
//            }
//            $route->attach($service->getEndPoint());
//            $service->setRoute($route);
//        }
    }

    private function calculateDistance(Service $service)
    {
        $request = new DirectionRequest(
            new CoordinateLocation(new Coordinate($service->getStartPoint()->getLatitude(), $service->getStartPoint()->getLongitude())),
            new CoordinateLocation(new Coordinate($service->getEndPoint()->getLatitude(), $service->getEndPoint()->getLongitude()))
        );

        $request->setUnitSystem(UnitSystem::METRIC);
        $request->setTravelMode(TravelMode::DRIVING);
        $request->setProvideRouteAlternatives(true);

        $response = $this->directionService->route($request);
        $routes = $response->getRoutes();
        $legs = $routes[0]->getLegs();

        return $legs[0]->getDistance()->getValue();
    }

    /**
     * @param ServiceEvent $event
     */
    public function onServiceFinish(ServiceEvent $event)
    {
        $service = $event->getService();

        $service->calculateRealDistance();
        #TODO: calculate price for specified path in requesting time.
//        $distance = $this->calculateDistance($service);
//        if ($distance) {
//            $service->setDistance($distance);
//        } else {
//            $service->setDistance($service->getRealDistance());
//        }

        try {
//            $service->calculatePrice();
            $service->calculateRealPrice();
        } catch (\RuntimeException $e) {
            $this->logger->addError(
                'distance can not be zero',
                array(
                    'distance' => $service->getDistance(),
                    'real_distance' => $service->getRealDistance()
                )
            );
        }

        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_FINISH);
        $this->doctrine->getManager()->persist($log);

        $this->logger->addInfo('Service is finished', array('service' => $service->getId()));
    }

    /**
     * TODO: Remove this method and use Payment api
     *
     * @param ServiceEvent             $event
     * @param                          $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function autoPay(ServiceEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $logger = $this->logger;
        $manager = $this->doctrine->getEntityManager();

        $serviceRepo = $manager->getRepository('FunProServiceBundle:Service');
        $service = $event->getService();

        #TODO: we need float costs?
//        $cost = $serviceRepo->getTotalCost($service);
        $cost = Service::roundPrice($service->getDiscountedPrice());

//        $currency = $manager->getRepository('FunProFinancialBundle:Currency')->findOneByCode('IRR');
//        $service->setCurrency($currency);

        $logger->addInfo('Payment method is cash');

        $transaction = new Transaction(
            $service->getPassenger(),
//            $service->getCurrency(),
            $cost,
            Transaction::TYPE_PAY,
            false
        );

        $transaction->setService($service);
        $transaction->setStatus(Transaction::STATUS_SUCCESS);

        $manager->persist($transaction);
        $logger->addInfo('main transaction is persisted');

        $event = new PaymentEvent($transaction);
        $dispatcher->dispatch(FinancialEvents::PAYMENT_EVENT, $event);
    }

    /**
     * @param PaymentEvent $event
     */
    public function calculateRealPrice(PaymentEvent $event)
    {
        $transaction = $event->getTransaction();
        $service = $transaction->getService();

        $basePrice = $this->doctrine->getRepository('FunProFinancialBundle:RegionBasePrice')
            ->getPriceInRegion($service->getStartPoint(), $transaction->getCurrency());

        if ($basePrice) {
            $service->setRealPrice(floor($basePrice->getPrice() * $service->getDistance()));
        } else {
            $this->logger->addWarning(
                'any base price is not set for this region',
                array('location' => $service->getStartPoint())
            );
        }
    }

    /**
     * @param PaymentEvent $event
     */
    public function onServicePayed(PaymentEvent $event)
    {
        $service = $event->getTransaction()->getService();

        #TODO: When use Payment api, these line must be uncommented.
//        $criteria = Criteria::create();
//        $criteria->orderBy(array('atTime' => Criteria::DESC))->getFirstResult();
//        $serviceLog = $service->getLogs()->matching($criteria);
//
//        if (!$serviceLog->first() or $serviceLog->first()->getStatus() !== ServiceLog::STATUS_FINISH) {
//            $logContext = SerializationContext::create()
//                ->setGroups(array('Public', 'ServiceLogs'));
//            $this->logger->addError(
//                'Service status must be finished till he can pay',
//                array($this->serializer->serialize($service, 'json', $logContext))
//            );
//            throw new ServiceStatusException('status must be finished');
//        }

        $log = new ServiceLog($service, ServiceLog::STATUS_PAYED);
        $this->doctrine->getManager()->persist($log);

        $this->logger->addInfo('Service is payed', array('service' => $service->getId()));
    }
}
