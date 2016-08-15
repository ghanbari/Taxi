<?php

namespace FunPro\ServiceBundle\Event;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Criteria;
use FunPro\DriverBundle\CarEvents;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Event\GetMoveCarEvent;
use FunPro\FinancialBundle\Entity\RegionBasePrice;
use FunPro\FinancialBundle\Entity\Transaction;
use FunPro\FinancialBundle\Event\PaymentEvent;
use FunPro\FinancialBundle\Exception\InvalidTransactionException;
use FunPro\FinancialBundle\FinancialEvents;
use FunPro\ServiceBundle\Entity\ServiceLog;
use FunPro\ServiceBundle\Exception\ServiceStatusException;
use FunPro\ServiceBundle\ServiceEvents;
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

    public function __construct(Registry $doctrine, Logger $logger, Serializer $serializer)
    {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
        $this->serializer = $serializer;
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
            ServiceEvents::SERVICE_REQUESTED => array('onServiceRequest', 10),
            ServiceEvents::SERVICE_CANCELED => array('onServiceCanceled', 10),
            ServiceEvents::SERVICE_ACCEPTED  => array('onServiceAccept', 10),
            ServiceEvents::SERVICE_REJECTED  => array('onServiceReject', 10),
            ServiceEvents::SERVICE_READY     => array('onServiceReady', 10),
            ServiceEvents::SERVICE_START     => array('onServiceStart', 10),
            ServiceEvents::SERVICE_FINISH    => array(
                array('onServiceFinish', 10),
                array('autoPay', 5),
            ),
            CarEvents::CAR_MOVE              => array('onService', 10),
            FinancialEvents::PAYMENT_EVENT   => array(
                array('calculateRealPrice', 80),
                array('onServicePayed', 10),
            ),
        );
    }

    /**
     * @param ServiceEvent $event
     */
    public function onServiceRequest(ServiceEvent $event)
    {
        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_REQUESTED);
        $this->doctrine->getManager()->persist($log);

        $this->logger->addInfo('New service is requested', array('service' => $event->getService()->getId()));
    }

    /**
     * @param ServiceEvent $event
     */
    public function onServiceCanceled(ServiceEvent $event)
    {
        $service = $event->getService();

        $criteria = Criteria::create();
        $criteria->orderBy(array('atTime' => Criteria::DESC))->getFirstResult();
        $serviceLog = $service->getLogs()->matching($criteria);

        $allowedStatus = array(
            ServiceLog::STATUS_REQUESTED,
            ServiceLog::STATUS_ACCEPTED,
            ServiceLog::STATUS_READY,
        );

        if (!$serviceLog->first() or !in_array($serviceLog->first()->getStatus(), $allowedStatus)) {
            $logContext = SerializationContext::create()
                ->setGroups(array('Public', 'ServiceLogs'));
            $this->logger->addNotice(
                'passenger can cancel service only when status is requested, accepted or ready',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be requested');
        }

        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_CANCELED);
        $this->doctrine->getManager()->persist($log);

        $this->logger->addInfo('Service was canceled', array('service' => $service->getId()));
    }

    /**
     * @param GetCarPointServiceEvent $event
     */
    public function onServiceAccept(GetCarPointServiceEvent $event)
    {
        $service = $event->getService();

        $criteria = Criteria::create();
        $criteria->orderBy(array('atTime' => Criteria::DESC))->getFirstResult();
        $serviceLog = $service->getLogs()->matching($criteria);

        if (!$serviceLog->first() or $serviceLog->first()->getStatus() !== ServiceLog::STATUS_REQUESTED) {
            $logContext = SerializationContext::create()
                ->setGroups(array('Public', 'ServiceLogs'));
            $this->logger->addError(
                'Service status must be requested till it can change into accepted',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be requested');
        }

        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_ACCEPTED);
        $this->doctrine->getManager()->persist($log);

        $this->logger->addInfo('the driver accept service', array('service' => $service->getId()));
    }

    /**
     * @param GetCarServiceEvent $event
     */
    public function onServiceReject(GetCarServiceEvent $event)
    {
        $service = $event->getService();

        $criteria = Criteria::create();
        $criteria->orderBy(array('atTime' => Criteria::DESC))->getFirstResult();
        $serviceLog = $service->getLogs()->matching($criteria);

        if (!$serviceLog->first() or $serviceLog->first()->getStatus() !== ServiceLog::STATUS_REQUESTED) {
            $logContext = SerializationContext::create()
                ->setGroups(array('Public', 'ServiceLogs'));
            $this->logger->addError(
                'Service status must be requested till it can change into rejected',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be requested');
        }

        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_REJECTED);
        $this->doctrine->getManager()->persist($log);

        $this->logger->addInfo('the driver reject service', array('service' => $service->getId()));
    }

    /**
     * @param GetCarPointServiceEvent $event
     */
    public function onServiceReady(GetCarPointServiceEvent $event)
    {
        $service = $event->getService();

        $criteria = Criteria::create();
        $criteria->orderBy(array('atTime' => Criteria::DESC))->getFirstResult();
        $serviceLog = $service->getLogs()->matching($criteria);

        if (!$serviceLog->first()
            or ($serviceLog->first()->getStatus() !== ServiceLog::STATUS_ACCEPTED
                and $serviceLog->first()->getStatus() !== ServiceLog::STATUS_READY)
        ) {
            $logContext = SerializationContext::create()
                ->setGroups(array('Public', 'ServiceLogs'));
            $this->logger->addError(
                'Service status must be accepted till it can change into ready',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be accepted');
        }

        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_READY);
        $this->doctrine->getManager()->persist($log);

        $this->logger->addInfo('Service is ready', array('service' => $service->getId()));
    }

    /**
     * @param ServiceEvent $event
     */
    public function onServiceStart(ServiceEvent $event)
    {
        $service = $event->getService();

        $criteria = Criteria::create();
        $criteria->orderBy(array('atTime' => Criteria::DESC))->getFirstResult();
        $serviceLog = $service->getLogs()->matching($criteria);

        if (!$serviceLog->first() or $serviceLog->first()->getStatus() !== ServiceLog::STATUS_READY) {
            $logContext = SerializationContext::create()
                ->setGroups(array('Public', 'ServiceLogs'));
            $this->logger->addError(
                'Service status must be ready till it can change into started',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be ready');
        }

        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_START);
        $this->doctrine->getManager()->persist($log);

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
                $route = clone $service->getRoute();
                if ($route->count() === 0) {
                    $route->attach($service->getStartPoint());
                }
                $route->attach($event->getCurrentLocation());
                $service->setRoute($route);
            }
        }
    }

    /**
     * @param ServiceEvent $event
     */
    public function onServiceFinish(ServiceEvent $event)
    {
        $service = $event->getService();

        $criteria = Criteria::create();
        $criteria->orderBy(array('atTime' => Criteria::DESC))->getFirstResult();
        $serviceLog = $service->getLogs()->matching($criteria);

        if (!$serviceLog->first() or $serviceLog->first()->getStatus() !== ServiceLog::STATUS_START) {
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

        $service->updateDistance();

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

        $cost = $serviceRepo->getTotalCost($service);

        $currency = $manager->getRepository('FunProFinancialBundle:Currency')->findOneByCode('IRR');
        $service->setCurrency($currency);

        $logger->addInfo('Payment method is cash');

        $transaction = new Transaction(
            $service->getPassenger(),
            $service->getCurrency(),
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
