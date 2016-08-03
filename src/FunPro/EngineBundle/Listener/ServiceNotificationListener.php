<?php

namespace FunPro\EngineBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\EngineBundle\GCM\GCM;
use FunPro\ServiceBundle\Entity\Service;
use FunPro\ServiceBundle\Event\GetCarPointServiceEvent;
use FunPro\ServiceBundle\Event\GetCarServiceEvent;
use FunPro\ServiceBundle\Event\ServiceEvent;
use FunPro\ServiceBundle\ServiceEvents;
use FunPro\UserBundle\Entity\Device;
use FunPro\UserBundle\Entity\Message;
use FunPro\UserBundle\Entity\User;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ServiceNotificationListener
 *
 * @package FunPro\EngineBundle\Listener
 */
class ServiceNotificationListener implements EventSubscriberInterface
{
    /**
     * @var GCM $gcm
     */
    private $gcm;

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
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @param GCM                   $gcm
     * @param Registry              $doctrine
     * @param Logger                $logger
     * @param Serializer            $serializer
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        GCM $gcm,
        Registry $doctrine,
        Logger $logger,
        Serializer $serializer,
        ParameterBagInterface $parameterBag
    ) {
        $this->gcm = $gcm;
        $this->doctrine = $doctrine;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->parameterBag = $parameterBag;
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
            ServiceEvents::SERVICE_REQUESTED => array('onServiceRequest', 5),
            ServiceEvents::SERVICE_ACCEPTED  => array('onServiceAccept', 5),
            ServiceEvents::SERVICE_REJECTED  => array('onServiceReject', 5),
            ServiceEvents::SERVICE_READY     => array('onServiceReady', 5),
            ServiceEvents::SERVICE_START     => array('onServiceStart', 5),
            ServiceEvents::SERVICE_FINISH    => array('onServiceFinish', 5),
        );
    }

    /**
     * @param $entity
     *
     * @return integer
     */
    protected function getEntitiesIds($entity)
    {
        return $entity->getId();
    }

    /**
     * @param User $user
     *
     * @return array
     */
    protected function getActiveDevices(User $user)
    {
        $devices = array();
        /** @var Device $device */
        foreach ($user->getDevices()->toArray() as $device) {
            if ($device->getStatus() === Device::STATUS_ACTIVE) {
                $devices[] = $device;
            }
        }

        return $devices;
    }

    /**
     * Send notification to specify drivers when service is requested
     *
     * @param ServiceEvent $event
     */
    public function onServiceRequest(ServiceEvent $event)
    {
        $service = $event->getService();
        $logger = $this->logger;

        if ($event->getService()->getPropagationType() !== Service::PROPAGATION_TYPE_ALL) {
            #TODO: sending notification only to first driver
            #TODO: monitoring and send notification to remain list members by cron job
            $drivers = $service->getPropagationList()->first()->getCar()->getDriver();
        } else {
            //TODO: Use Spatial Mysql Distance function for Mysql > 5.6.1
            $drivers = $this->doctrine->getRepository('FunProDriverBundle:Driver')
                ->getAllAround($service->getStartPoint(), $this->parameterBag->get('service.visible_radius'));
        }

        $logger->addInfo(
            'sending notification to drivers, ids:',
            array_map(array($this, 'getEntitiesIds'), $drivers)
        );

        $devices = array_map(array($this, 'getActiveDevices'), $drivers);
        if ($devices) {
            $devices = call_user_func_array('array_merge', $devices);
        }

        $logger->addInfo(
            'sending notification to devices, ids:',
            array_map(array($this, 'getEntitiesIds'), $devices)
        );

        $data = array(
            'type' => 'service',
            'id' => $service->getId()
        );

        $message = (new Message())
            ->setData($data)
            ->setPriority(Message::PRIORITY_HIGH)
            ->setTimeToLive($this->parameterBag->get('gcm.ttl.service_request'));

        $this->gcm->queue($devices, $message);
    }

    public function onServiceAccept(GetCarPointServiceEvent $event)
    {
        $service = $event->getService();

        if (!$service->getCar()) {
            $this->logger->addError('Service haven\'t car', array('service' => $service->getId()));
            throw new \LogicException('Service haven\'t car');
        }

        if ($service->getPassenger()) {
            $data = array(
                'type' => 'service.accept',
                'id' => $service->getId(),
                'name' => $service->getCar()->getDriver()->getName(),
                'car_type' => $service->getCar()->getType(),
                'plaque' => (string) $service->getCar()->getPlaque(),
                'mobile' => $service->getCar()->getDriver()->getMobile(),
            );

            $message = (new Message())
                ->setData($data)
                ->setPriority(Message::PRIORITY_HIGH)
                ->setTimeToLive($this->parameterBag->get('gcm.ttl.service_accept'));
            $this->gcm->queue($service->getPassenger()->getDevices()->toArray(), $message);
        }
    }

    public function onServiceReject(GetCarServiceEvent $event)
    {
    }

    public function onServiceReady(GetCarPointServiceEvent $event)
    {
        $service = $event->getService();

        $data = array(
            'type' => 'service.ready',
        );

        $message = (new Message())
            ->setData($data)
            ->setPriority(Message::PRIORITY_HIGH)
            ->setTimeToLive($this->parameterBag->get('gcm.ttl.service_ready'));

        $this->gcm->queue($service->getPassenger()->getDevices()->toArray(), $message);
    }

    public function onServiceStart(GetCarPointServiceEvent $event)
    {
        $service = $event->getService();

        $data = array(
            'type' => 'service.start',
        );

        $message = (new Message())
            ->setData($data)
            ->setPriority(Message::PRIORITY_HIGH)
            ->setTimeToLive($this->parameterBag->get('gcm.ttl.service_start'));

        $this->gcm->queue($service->getPassenger()->getDevices()->toArray(), $message);
    }

    public function onServiceFinish(GetCarPointServiceEvent $event)
    {
        $service = $event->getService();

        $context = SerializationContext::create()
            ->setGroups(array('Cost'));
        $data = array(
            'type' => 'service.finish',
            'id' => $service->getId(),
            'price' => $service->getPrice(),
            'cost' => $this->serializer->serialize($service->getFloatingCosts()->toArray(), 'json', $context),
            'distance' => $service->getDistance(),
        );

        $message = (new Message())
            ->setData($data)
            ->setPriority(Message::PRIORITY_HIGH)
            ->setTimeToLive($this->parameterBag->get('gcm.ttl.service_finish'));

        $this->gcm->queue($service->getPassenger()->getDevices()->toArray(), $message);
    }
}
