<?php

namespace FunPro\ServiceBundle\Event;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\ServiceBundle\Entity\ServiceLog;
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

    public function onServiceAccept(GetCarPointServiceEvent $event)
    {
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
