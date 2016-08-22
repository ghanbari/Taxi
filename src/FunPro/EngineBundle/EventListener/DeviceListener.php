<?php

namespace FunPro\EngineBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\UserBundle\Entity\Device;
use FunPro\UserBundle\Exception\DeviceNotFoundException;
use FunPro\UserBundle\Exception\MultiDeviceException;
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

        $user = $token->getUser();

        if (!$request->headers->has('X-AUTH-TOKEN')) {
            return;
        }

        $this->logger->addInfo('check for device information');

        /** @var Device $device */
        $device = $this->registry->getRepository('FunProUserBundle:Device')->findOneBy(array(
            'owner' => $user,
            'apiKey' => $request->headers->get('X-AUTH-TOKEN'),
        ));

        $request->attributes->set('currentDevice', $device);

        if (($user->getDevices()->count() > 1) and !$user->isMultiDeviceAllowed()) {
            $this->logger->addError(
                'user can not have multi device',
                array(
                    'count' => $user->getDevices()->count(),
                    'userId' => $user->getId(),
                )
            );
            throw new MultiDeviceException;
        }
    }
}
