<?php

namespace FunPro\EngineBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\UserBundle\Exception\DeviceNotFoundException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class DeviceListener
 *
 * @package FunPro\EngineBundle\EventListener
 */
class DeviceListener implements EventSubscriberInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var TokenStorage
     */
    private $storage;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Registry     $registry
     * @param TokenStorage $storage
     */
    public function __construct(Registry $registry, TokenStorage $storage, Logger $logger)
    {
        $this->registry = $registry;
        $this->storage = $storage;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array('onKernelRequest', 7),
            ),
        );
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $token = $this->storage->getToken();

        if ($token and $token->isAuthenticated()
            and ($token->getUser() instanceof Driver or $token->getUser() instanceof Passenger)
        ) {
            $this->logger->addInfo('check for device information');
            if (!$request->headers->has('X-DEVICE-ID')) {
                $this->logger->addInfo('X-DEVICE-ID header is not exists');
                throw new BadRequestHttpException('You must send device\'s id as X-DEVICE-ID header');
            }

            $deviceId = $request->headers->get('X-DEVICE-ID');
            $device = $this->registry->getRepository('FunProUserBundle:Device')
                ->findOneByDeviceIdentifier($deviceId);

            if (!$device) {
                $this->logger->addError('device is not exists', array('deviceId' => $deviceId));
                throw new DeviceNotFoundException('device is not exists');
            }

            if ($device->getOwner() !== $token->getUser()) {
                $this->logger->addError(
                    'you are not owner of device',
                    array(
                        'deviceId' => $deviceId,
                        'userId' => $token->getUser()->getId(),
                    )
                );
                throw new AccessDeniedException('You are not owner of device');
            }
        }
    }
}
