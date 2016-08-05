<?php

namespace FunPro\ServiceBundle\Event;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Criteria;
use FunPro\ServiceBundle\Entity\ServiceLog;
use FunPro\ServiceBundle\Exception\ServiceStatusException;
use FunPro\ServiceBundle\ServiceEvents;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Bridge\Monolog\Logger;
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
            ServiceEvents::SERVICE_FINISH    => array('onServiceFinish', 10),
        );
    }

    public function onServiceRequest(ServiceEvent $event)
    {
        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_REQUESTED);
        $this->doctrine->getManager()->persist($log);

        $logContext = SerializationContext::create()
            ->setGroups(array('Public', 'Point', 'Admin'));
        $this->logger->addInfo(
            'New service was requested',
            array($this->serializer->serialize($event->getService(), 'json', $logContext))
        );
    }

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

        $logContext = SerializationContext::create()
            ->setGroups(array('Public', 'Point', 'Admin'));
        $this->logger->addInfo(
            'Service was canceled',
            array($this->serializer->serialize($event->getService(), 'json', $logContext))
        );
    }

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

        $logContext = SerializationContext::create()
            ->setGroups(array('Public', 'Admin', 'Point', 'Car'));
        $this->logger->addInfo(
            'The driver accept service',
            array($this->serializer->serialize($event->getService(), 'json', $logContext))
        );
    }

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
                'Service status must be requested till it can change into accepted',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be requested');
        }

        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_REJECTED);
        $this->doctrine->getManager()->persist($log);

        $logContext = SerializationContext::create()
            ->setGroups(array('Public', 'Admin', 'Point', 'Car'));
        $this->logger->addInfo(
            'The driver reject service',
            array(
                $this->serializer->serialize($event->getService(), 'json', $logContext),
                $this->serializer->serialize($event->getCar(), 'json', $logContext),
            )
        );
    }

    public function onServiceReady(GetCarPointServiceEvent $event)
    {
        $service = $event->getService();

        $criteria = Criteria::create();
        $criteria->orderBy(array('atTime' => Criteria::DESC))->getFirstResult();
        $serviceLog = $service->getLogs()->matching($criteria);

        if (!$serviceLog->first() or $serviceLog->first()->getStatus() !== ServiceLog::STATUS_ACCEPTED) {
            $logContext = SerializationContext::create()
                ->setGroups(array('Public', 'ServiceLogs'));
            $this->logger->addError(
                'Service status must be accepted till it can change into accepted',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be accepted');
        }

        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_READY);
        $this->doctrine->getManager()->persist($log);

        $logContext = SerializationContext::create()
            ->setGroups(array('Public', 'Admin', 'Point', 'Car'));
        $this->logger->addInfo(
            'service is ready',
            array($this->serializer->serialize($event->getService(), 'json', $logContext))
        );
    }

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
                'Service status must be ready till it can change into accepted',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be ready');
        }

        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_START);
        $this->doctrine->getManager()->persist($log);

        $logContext = SerializationContext::create()
            ->setGroups(array('Public', 'Admin', 'Point', 'Car'));
        $this->logger->addInfo(
            'service is started',
            array($this->serializer->serialize($event->getService(), 'json', $logContext))
        );
    }

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
                'Service status must be started till it can change into accepted',
                array($this->serializer->serialize($event->getService(), 'json', $logContext))
            );
            throw new ServiceStatusException('status must be started');
        }

        $log = new ServiceLog($event->getService(), ServiceLog::STATUS_FINISH);
        $this->doctrine->getManager()->persist($log);

        $logContext = SerializationContext::create()
            ->setGroups(array('Public', 'Admin', 'Point', 'Car'));
        $this->logger->addInfo(
            'service is finished',
            array($this->serializer->serialize($event->getService(), 'json', $logContext))
        );
    }
}
