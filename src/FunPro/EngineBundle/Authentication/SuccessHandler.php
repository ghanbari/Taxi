<?php

namespace FunPro\EngineBundle\Authentication;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\UserBundle\Entity\Device;
use FunPro\UserBundle\Exception\DeviceNotFoundException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
        if ($token->getUser() instanceof Driver or $token->getUser() instanceof Passenger) {
            $this->logger->addInfo('check for device information');
            if (!$request->headers->has('X-DEVICE-ID')) {
                $this->logger->addInfo('X-DEVICE-ID header is not exists');
                throw new BadRequestHttpException('You must send device\'s id as X-DEVICE-ID header');
            }

            $deviceId = $request->headers->get('X-DEVICE-ID');
            /** @var Device $device */
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

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(
                        array(
                            'code' => 100,
                            'message' => 'You are not owner of device'
                        ),
                        JsonResponse::HTTP_BAD_REQUEST
                    );
                } else {
                    throw new AccessDeniedException('You are not owner of device');
                }
            } else {
                $device->setLastLoginAt(new \DateTime());
                $this->registry->getManager()->flush();
            }
        }

        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse(array('code' => Response::HTTP_OK, 'username' => $token->getUsername()));
        } else {
            $response = parent::onAuthenticationSuccess($request, $token);
        }

        return $response;
    }
}