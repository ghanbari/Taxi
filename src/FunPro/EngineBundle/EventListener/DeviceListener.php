<?php

namespace FunPro\EngineBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\EngineBundle\Authentication\DeviceTokenAuthenticator;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\UserBundle\Entity\Device;
use FunPro\UserBundle\Entity\User;
use FunPro\UserBundle\Exception\DeviceNotFoundException;
use FunPro\UserBundle\Exception\MultiDeviceException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
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
            KernelEvents::RESPONSE => array(
                array('onKernelResponse', 7),
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

        if (!$request->attributes->has('currentDevice')) {
            $this->logger->addInfo('skip device checker');
            return;
        }

        $this->logger->addInfo('check for device information');

        if (($token->getUser()->getDevices()->count() > 1) and !$token->isMultiDeviceAllowed()) {
            $this->logger->addError(
                'user can not have multi device',
                array(
                    'count' => $token->getUser()->getDevices()->count(),
                    'userId' => $token->getUser()->getId(),
                )
            );
            throw new MultiDeviceException;
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($event->getRequestType() === HttpKernelInterface::SUB_REQUEST
            or !$event->getRequest()->attributes->has('currentDevice')
        ) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('X-Device-Status', $event->getRequest()->attributes->get('currentDevice')->getStatus());
    }
}
