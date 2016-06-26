<?php

namespace FunPro\UserBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
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
 * @Rest\NamePrefix("fun_pro_api_")
 * @Rest\RouteResource("device", pluralize=false)
 */
class DeviceController extends FOSRestController
{
    public function createCreateForm(Device $device)
    {
        $requestFormat = $this->get('request_stack')->getCurrentRequest()->getRequestFormat('html');

        $form = $this->createForm(DeviceType::class, $device, array(
            'action' => $this->generateUrl('fun_pro_api_post_device'),
            'method' => 'POST',
            'csrf_protection' => $requestFormat == 'html' ?: false,
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
     *          200="When Your device is exists",
     *          201="When success",
     *          400={
     *              "When form validation failed.",
     *              "When Your device is exists, but token is not valid",
     *              "You can not have multi device"
     *          },
     *          403={
     *              "when csrf token is invalid",
     *              "When you are not device owner"
     *          }
     *      }
     * )
     *
     * @Security("is_authenticated() and has_role('ROLE_USER')")
     */
    public function postAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        $device = new Device();
        $form = $this->createCreateForm($device);
        $form->handleRequest($request);

        /** @var Device $persistentDevice */
        $persistentDevice = $this->getDoctrine()->getRepository('FunProUserBundle:Device')->findOneBy(array(
            'deviceIdentifier' => $device->getDeviceIdentifier(),
            'appName' => $device->getAppName(),
        ));

        if ($persistentDevice) {
            if ($device->getDeviceToken() == $persistentDevice->getDeviceToken()) {
                $context = (new Context())
                    ->addGroup('Owner')
                    ->setMaxDepth(1);
                return $this->view($persistentDevice, Response::HTTP_OK)
                    ->setSerializationContext($context);
            } else if ($persistentDevice->getOwner() != $user) {
                $error = array(
                    'code' => 1,
                    'message' => $this->get('translator')->trans('you.are.not.owner.of.this.device'),
                );
                $this->get('logger')->log('error', '', $error);
                return $this->view($error, Response::HTTP_FORBIDDEN);
            } else {
                $error = array(
                    'code' => 1,
                    'message' => $this->get('translator')->trans('you.must.update.token'),
                );
                $this->get('logger')->log('error', '', $error);
                return $this->view($error, Response::HTTP_BAD_REQUEST);
            }
        }

        if (($user->getDevices()->count() > 0) and !$user->isMultiDeviceAllowed()) {
            $error = array(
                'code' => 2,
                'message' => $this->get('translator')->trans('you.can.not.add.another.device'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        if ($form->isValid()) {
            $device->setApiKey($user->getApiKey());
            $user->setApiKey(null);
            $device->setOwner($user);

            $manager->persist($device);
            $manager->flush();

            $context = (new Context())
                ->addGroup('Owner')
                ->setMaxDepth(1);
            return $this->view($device, Response::HTTP_CREATED)
                ->setSerializationContext($context);
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }

    /**
     * update a device token
     *
     * @ApiDoc(
     *      section="Device",
     *      resource=true,
     *      statusCodes={
     *          204="When success",
     *          404="When device is not exists",
     *          403= {
     *              "when you are not owner of device",
     *          },
     *      }
     * )
     *

     *
     * @Rest\RequestParam(name="token", nullable=false, strict=true)
     * @Rest\RequestParam(name="deviceIdentifier", nullable=false, strict=true)
     * @Rest\RequestParam(name="appName", nullable=false, strict=true)
     * @Rest\Put(name="put_device_token", path="/device/token", options={"method_prefix"=false})
     * @Rest\Put(name="put_passenger_device_token", path="/passenger/device/token", options={"method_prefix"=false})
     */
    public function putTokenAction()
    {
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $token = $fetcher->get('token');
        $device = $this->getDoctrine()->getRepository('FunProUserBundle:Device')->findOneBy(array(
            'deviceIdentifier' => $fetcher->get('deviceIdentifier'),
            'appName' => $fetcher->get('appName'),
        ));

        if (is_null($device)) {
            throw $this->createNotFoundException('device is not exists');
        }

        if ($device->getOwner() and $device->getOwner() != $this->getUser()) {
            throw $this->createAccessDeniedException('you.are.not.owner.of.device');
        }

        $device->setDeviceToken($token);
        $device->setStatus(Device::STATUS_ACTIVE);
        $this->getDoctrine()->getManager()->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

} 