<?php

namespace FunPro\ServiceBundle\Event;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\DriverBundle\Exception\DriverNotFoundException;
use FunPro\EngineBundle\GCM\GCM;
use FunPro\FinancialBundle\Entity\BaseCost;
use FunPro\ServiceBundle\Entity\PropagationList;
use FunPro\ServiceBundle\Entity\Service;
use FunPro\ServiceBundle\Repository\ServiceRepository;
use FunPro\ServiceBundle\ServiceEvents;
use FunPro\UserBundle\Entity\Message;
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
            ServiceEvents::SERVICE_CANCELED => array('onServiceCanceled', 5),
            ServiceEvents::SERVICE_ACCEPTED => array('onServiceAccept', 5),
            ServiceEvents::SERVICE_REJECTED => array('onServiceReject', 5),
            ServiceEvents::SERVICE_READY => array('onServiceReady', 5),
            ServiceEvents::SERVICE_START => array('onServiceStart', 5),
            ServiceEvents::SERVICE_FINISH => array('onServiceFinish', 5),
        );
    }

    /**
     * Send notification to given drivers when service is requested
     *
     * @param ServiceEvent $event
     *
     * @throws DriverNotFoundException
     */
    public function onServiceRequest(ServiceEvent $event)
    {
        $logger = $this->logger;
        $service = $event->getService();
        $doctrine = $this->doctrine;

        $favoriteDiscountCode = $doctrine->getRepository('FunProFinancialBundle:FavoriteDiscountCodes')->findOneBy(array(
            'passenger' => $this->getUser(),
            'active' => true,
            'used' => false,
        ));
        
        if ($favoriteDiscountCode  and $doctrine->getRepository('FunProFinancialBundle:DiscountCode')
                ->canUseDiscount($service->getPassenger(), $favoriteDiscountCode ->getDiscountCode(), $service->getStartPoint(), $service->getEndPoint())
        ) {
            $discountCode = $favoriteDiscountCode;
        } else {
            $discountCode = null;
        }

        $price = Service::roundPrice(ServiceRepository::calculatePrice($service->getBaseCost(), $service->getDistance(), false));
        $discountedPrice = Service::roundPrice(ServiceRepository::calculatePrice($service->getBaseCost(), $service->getDistance(), true, $discountCode));

        $data = array(
            'type' => 'service',
            'id' => $service->getId(),
            'propagationType' => $event->getService()->getPropagationType(),
            'pickup_latitude' => $service->getStartPoint()->getLatitude(),
            'pickup_longitude' => $service->getStartPoint()->getLongitude(),
            'pickup_name' => $service->getStartAddress(),
            'dropoff_latitude' => $service->getEndPoint() ? $service->getEndPoint()->getLatitude() : '',
            'dropoff_longitude' => $service->getEndPoint() ? $service->getEndPoint()->getLongitude() : '',
            'dropoff_name' => $service->getEndAddress(),
            'requested_at' => $this->serializer->serialize($service->getCreatedAt(), 'json'),
            'description' => !empty($service->getDescription()) ? substr($service->getDescription(), 0, 2000) : '',
            'send_in' => strtotime('now'),
            'distance' => round($service->getDistance() / 1000, 1),
            'price' => Service::roundPrice($price),
            'total_cost' => Service::roundPrice($discountedPrice),
        );

        if ($service->getPassenger()) {
            $data['user_type'] = 'user';
            $data['user_id'] = $service->getPassenger()->getId();
            $data['user_full_name'] = $service->getPassenger()->getName();
            $data['user_avatar'] = $service->getPassenger()->getAvatarPath();
            $data['user_phone'] = $service->getPassenger()->getMobile();
        } else {
            $data['user_type'] = 'place';
            $data['user_id'] = $service->getAgent()->getId();
            $data['user_full_name'] = $service->getAgent()->getName();
            $data['user_phone'] = json_encode($service->getAgent()->getContacts());
        }

        if ($event->getService()->getPropagationType() !== Service::PROPAGATION_TYPE_ALL) {
//            #TODO: sending notification only to first driver
//            #TODO: monitoring and send notification to remain list members by cron job
//            /** @var PropagationList $firstPropagation */
//            $firstPropagation = $service->getPropagationList()->first();
//            $drivers = array($firstPropagation->getDriver());

            $drivers = array();
            /** @var PropagationList $propagationList */
            foreach ($service->getPropagationList() as $propagationList) {
                $drivers[] = $propagationList->getDriver();
            }
        } else {
            //TODO: Use Spatial Mysql Distance function for Mysql > 5.6.1
            $drivers = $this->doctrine->getRepository('FunProDriverBundle:Driver')
                ->getAllFreeDriverAroundPoint($service->getStartPoint(), $this->parameterBag->get('service.visible_radius'));
        }

        if (!is_array($drivers) or empty($drivers)) {
            $logger->addNotice('any driver is not available');
            throw new DriverNotFoundException('driver.is.not.found', 400);
        }

        $logger->addInfo(
            'sending notification to drivers, ids:',
            array_map(array($this, 'getEntitiesIds'), $drivers)
        );

        $devices = array_map(
            function (Driver $driver) {
                return $driver->getDevices()->toArray();
            },
            $drivers
        );
        if ($devices) {
            $devices = call_user_func_array('array_merge', $devices);

            if (!is_array($devices) or empty($devices)) {
                throw new DriverNotFoundException('driver.device.is.not.found', 400);
            }

            $logger->addInfo(
                'sending notification to devices, ids:',
                array_map(array($this, 'getEntitiesIds'), $devices)
            );

            $message = (new Message())
                ->setType(Message::MESSAGE_TYPE_SERVICE_REQUESTED)
                ->setService($service)
                ->setData($data)
                ->setPriority(Message::PRIORITY_HIGH)
                ->setTimeToLive($this->parameterBag->get('gcm.ttl.service_request'));

            $this->gcm->queue($devices, $message);
        } else {
            $logger->addNotice('Any active device is not found');
            throw new DriverNotFoundException('device.is.not.found', 400);
        }
    }

    public function onServiceCanceled(ServiceEvent $event)
    {
        $service = $event->getService();

        if (!$service->getCar()) {
            $this->logger->addInfo('Service haven\'t car, no send notification', array('service' => $service->getId()));
            return;
        }

        $driver = $service->getCar()->getDriver();

        $data = array(
            'type' => 'service.canceled',
            'id' => $service->getId(),
            'mobile' => $service->getPassenger()->getMobile(),
            'send_in' => strtotime('now'),
        );

        $message = (new Message())
            ->setType(Message::MESSAGE_TYPE_SERVICE_CANCELED)
            ->setService($service)
            ->setData($data)
            ->setPriority(Message::PRIORITY_HIGH)
            ->setTimeToLive($this->parameterBag->get('gcm.ttl.service_cancel'));
        $this->gcm->queue($driver->getDevices()->toArray(), $message);
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
                'plaque' => (string)$service->getCar()->getPlaque(),
                'mobile' => $service->getCar()->getDriver()->getMobile(),
                'pickup_latitude' => $service->getStartPoint()->getLatitude(),
                'pickup_longitude' => $service->getStartPoint()->getLongitude(),
                'dropoff_latitude' => $service->getEndPoint() ? $service->getEndPoint()->getLatitude() : '',
                'dropoff_longitude' => $service->getEndPoint() ? $service->getEndPoint()->getLongitude() : '',
                'send_in' => strtotime('now'),
            );

            $message = (new Message())
                ->setType(Message::MESSAGE_TYPE_SERVICE_ACCEPTED)
                ->setService($service)
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
            'id' => $service->getId(),
            'send_in' => strtotime('now'),
        );

        $message = (new Message())
            ->setType(Message::MESSAGE_TYPE_SERVICE_READY)
            ->setService($service)
            ->setData($data)
            ->setPriority(Message::PRIORITY_HIGH)
            ->setTimeToLive($this->parameterBag->get('gcm.ttl.service_ready'));

        $this->gcm->queue($service->getPassenger()->getDevices()->toArray(), $message);
    }

    public function onServiceStart(ServiceEvent $event)
    {
        $service = $event->getService();

        $data = array(
            'type' => 'service.start',
            'id' => $service->getId(),
            'send_in' => strtotime('now'),
        );

        $message = (new Message())
            ->setType(Message::MESSAGE_TYPE_SERVICE_STARTED)
            ->setService($service)
            ->setData($data)
            ->setPriority(Message::PRIORITY_HIGH)
            ->setTimeToLive($this->parameterBag->get('gcm.ttl.service_start'));

        $this->gcm->queue($service->getPassenger()->getDevices()->toArray(), $message);
    }

    /**
     * @param ServiceEvent $event
     */
    public function onServiceFinish(ServiceEvent $event)
    {
        $service = $event->getService();
        $price = $service->getPrice();
        $discountedPrice = $service->getDiscountedPrice();

        $context = SerializationContext::create()
            ->setGroups(array('Cost'));
        $data = array(
            'type' => 'service.finish',
            'id' => $service->getId(),
            'cost' => $this->serializer->serialize($service->getFloatingCosts()->toArray(), 'json', $context),
            'distance' => $service->getDistance() / 1000,
            'send_in' => strtotime('now'),
            'price' => Service::roundPrice($price),
            'total_cost' => Service::roundPrice($discountedPrice),
        );

        $message = (new Message())
            ->setType(Message::MESSAGE_TYPE_SERVICE_FINISHED)
            ->setService($service)
            ->setData($data)
            ->setPriority(Message::PRIORITY_HIGH)
            ->setTimeToLive($this->parameterBag->get('gcm.ttl.service_finish'));

        $this->gcm->queue($service->getPassenger()->getDevices()->toArray(), $message);
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
}
