<?php

namespace FunPro\UserBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FunPro\UserBundle\Entity\Device;
use FunPro\UserBundle\Entity\User;
use FunPro\UserBundle\Form\DeviceType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DeviceController
 *
 * @package FunPro\UserBundle\Controller
 *
 * @Rest\NamePrefix("fun_pro_user_api_")
 * @Rest\RouteResource("device", pluralize=false)
 */
class DeviceController extends FOSRestController
{
    public function createCreateForm(Device $device)
    {
        $requestFormat = $this->get('request_stack')->getCurrentRequest()->getRequestFormat('html');
        $form = $this->createForm(DeviceType::class, $device, array(
            'action' => $this->generateUrl('fun_pro_user_api_post_device'),
            'method' => 'POST',
            'csrf_protection' => $requestFormat === 'html' ?: false,
        ));

        return $form;
    }

    public function newAction()
    {
    }

    /**
     * Add a device to user
     *
     * @ApiDoc(
     *      section="Device",
     *      resource=true,
     *      input={
     *          "class"="FunPro\UserBundle\Form\DeviceType",
     *          "data"={
     *              "class"="FunPro\UserBundle\Entity\Device",
     *              "groups"={"Owner"},
     *              "parsers"={
     *                  "Nelmio\ApiDocBundle\Parser\ValidationParser",
     *                  "Nelmio\ApiDocBundle\Parser\JmsMetadataParser",
     *              },
     *          },
     *      },
     *      output={
     *          "class"="FunPro\UserBundle\Entity\Device",
     *          "groups"={"Owner"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          201="When success",
     *          200="When your device is exists and update it",
     *          400={
     *              "When form validation failed.",
     *          },
     *          403={
     *              "when csrf token is invalid",
     *              "When you are not login"
     *          }
     *      }
     * )
     *
     * @Security("is_authenticated() and has_role('ROLE_USER')")
     */
    public function postAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $logger = $this->get('logger');

        /** @var User $user */
        $user = $this->getUser();

        $device = new Device();
        $form = $this->createCreateForm($device);
        $form->handleRequest($request);

        /** @var Device $persistentDevice */
        $persistentDevice = $manager->getRepository('FunProUserBundle:Device')->findOneBy(array(
            'owner' => $this->getUser(),
            'deviceIdentifier' => $device->getDeviceIdentifier(),
            'status' => Device::STATUS_ACTIVE,
            'deviceModel' => $device->getDeviceModel(),
            'deviceVersion' => $device->getDeviceVersion(),
            'appVersion' => $device->getAppVersion(),
            'appName' => $device->getAppName(),
        ));

        if ($persistentDevice) {
            $form = $this->createCreateForm($persistentDevice);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $manager->flush();

                $logger->addInfo('device was updated');

                $context = (new Context())
                    ->addGroup('Owner')
                    ->setMaxDepth(1);
                return $this->view($persistentDevice, Response::HTTP_OK)
                    ->setSerializationContext($context);
            }
        }

        if (($user->getDevices()->count() > 0) and !$user->isMultiDeviceAllowed()) {
            $logger->addDebug("you have {$user->getDevices()->count()} device");
            $logger->addInfo('remove old devices');
            $manager->getRepository('FunProUserBundle:Device')->removeUserDevices($this->getUser());
        }

        if ($form->isValid()) {
            $manager->getFilters()->getFilter('softdeleteable')->disableForEntity('FunProUserBundle:Device');
            do {
                $apiKey = bin2hex(random_bytes(100));
                $isDuplicate = $manager->getRepository('FunProUserBundle:Device')
                    ->findOneByApiKey($apiKey);
            } while ($isDuplicate);

            $device->setApiKey($apiKey);
            $user->setApiKey(null);
            $device->setOwner($user);

            $manager->persist($device);
            $manager->flush();

            $logger->addInfo('device was persisted');

            $context = (new Context())
                ->addGroup('Owner')
                ->setMaxDepth(1);
            return $this->view($device, Response::HTTP_CREATED)
                ->setSerializationContext($context);
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Update a device token
     *
     * @ApiDoc(
     *      section="Device",
     *      resource=true,
     *      statusCodes={
     *          204="When success",
     *          400="X-AUTH-TOKEN header is not exists(code: 1)",
     *          404="When device is not exists",
     *          403= {
     *              "when you are not login",
     *          },
     *      }
     * )
     *
     * @Security("is_authenticated() and has_role('ROLE_USER')")
     *
     * @Rest\RequestParam(name="token", nullable=false, strict=true)
     *
     */
    public function putTokenAction(Request $request)
    {
        $logger = $this->get('logger');
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $token = $fetcher->get('token');
        $translator = $this->get('translator');

        if (!$request->headers->has('X-AUTH-TOKEN')) {
            $logger->addError('X-AUTH-TOKEN header is not exists', array('userId' => $this->getUser()->getId()));
            $error = array(
                'code' => 1,
                'message' => $translator->trans('X-AUTH-TOKEN.header.is.not.exists'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $device = $this->getDoctrine()->getRepository('FunProUserBundle:Device')
            ->findOneByApiKey($request->headers->get('X-AUTH-TOKEN'));

        if (is_null($device)) {
            $key = substr($request->headers->get('X-AUTH-TOKEN'), 0, 20);
            $logger->addDebug('device is not exists', array('apiKey' => $key));
            throw $this->createNotFoundException('device is not exists');
        }

        $device->setDeviceToken($token);
        $device->setStatus(Device::STATUS_ACTIVE);
        $this->getDoctrine()->getManager()->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get user's devices
     *
     * @ApiDoc(
     *      section="Device",
     *      resource=true,
     *      output={
     *          "class"="FunPro\UserBundle\Entity\Device",
     *          "groups"={"Owner"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          200="When success",
     *          204="When device is not exists",
     *          403= {
     *              "when you are not login",
     *          },
     *      }
     * )
     *
     * @Security("is_authenticated()")
     */
    public function cgetAction()
    {
        $user = $this->getUser();
        $devices = $user->getDevices()->toArray();

        if (empty($devices)) {
            $this->get('logger')->addDebug('device is not registered');
            return $this->view(null, Response::HTTP_NO_CONTENT);
        }

        $context = (new Context())
            ->addGroup('Owner');
        return $this->view($devices, Response::HTTP_OK)
            ->setSerializationContext($context);
    }

    /**
     * Find user's device by device identifier
     *
     * @ApiDoc(
     *      section="Device",
     *      resource=true,
     *      output={
     *          "class"="FunPro\UserBundle\Entity\Device",
     *          "groups"={"Owner"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          200="When success",
     *          404="When device is not exists",
     *          403= {
     *              "when you are not login",
     *          },
     *      }
     * )
     *
     * @ParamConverter(name="device", class="FunProUserBundle:Device")
     * @Security("is_authenticated() and device.getOwner() === user")
     */
    public function getAction(Request $request, $id)
    {
        $device = $request->attributes->get('device');

        $context = (new Context())
            ->addGroup('Owner');
        return $this->view($device, Response::HTTP_OK)
            ->setSerializationContext($context);
    }
}
