<?php

namespace FunPro\EngineBundle\Authentication;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\UserBundle\Entity\Device;
use FunPro\UserBundle\Entity\User;
use FunPro\UserBundle\Exception\DeviceNotFoundException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * {@inheritDoc}
 */
class SuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var TokenStorage
     */
    private $storage;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param HttpUtils    $httpUtils
     * @param Registry     $registry
     * @param TokenStorage $storage
     * @param Logger       $logger
     * @param array        $options
     */
    public function __construct(
        HttpUtils $httpUtils,
        Registry $registry,
        TokenStorage $storage,
        Logger $logger,
        array $options = array()
    ) {
        parent::__construct($httpUtils, $options);
        $this->logger = $logger;
        $this->storage = $storage;
        $this->registry = $registry;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
//        if ($user instanceof Driver) {
//            $this->logger->addInfo('check for device information');
//
//            if ($user->getDevices()->count() === 0) {
//                $this->logger->addInfo('skip check device, because user have not any device');
//                $response = $this->getResponse($request, $token);
//                $response->headers->add(array('X-Device-Status' => 'NotFound'));
//                return $response;
//            }
//
//            if (!$request->headers->has('X-AUTH-TOKEN')) {
//                $this->logger->addInfo('X-AUTH-TOKEN header is not exists, remove all old devices');
//                $this->registry->getRepository('FunProUserBundle:Device')->removeUserDevices($user);
//                $this->registry->getManager()->flush();
//                $response = $this->getResponse($request, $token);
//                $response->headers->add(array('X-Device-Status' => 'Reset'));
//                return $response;
//            }
//
//            $apiKey = $request->headers->get('X-AUTH-TOKEN');
//            /** @var Device $device */
//            $device = $this->registry->getRepository('FunProUserBundle:Device')->findOneBy(array(
//                'owner' => $user,
//                'apiKey' => $apiKey,
//            ));
//
//            if (!$device) {
//                $this->logger->addError(
//                    'device is not exists',
//                    array(
//                        'apiKey' => substr($apiKey, 0, 20),
//                        'userId' => $user->getId(),
//                    )
//                );
//                throw new DeviceNotFoundException('device is not exists');
//            } else {
//                $device->setLastLoginAt(new \DateTime());
//                $this->registry->getManager()->flush();
//                $response = $this->getResponse($request, $token);
//                $response->headers->add(array(
//                    'X-Device-Status' => $device->getStatus() === Device::STATUS_ACTIVE ? 'Ok' : 'InActive',
//                ));
//                return $response;
//            }
//        }

        return $this->getResponse($request, $token);
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function getResponse(Request $request, TokenInterface $token)
    {
        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse(array('code' => Response::HTTP_OK, 'username' => $token->getUsername()));
        } else {
            $response = parent::onAuthenticationSuccess($request, $token);
        }

        return $response;
    }
}
