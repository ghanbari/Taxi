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
     *          200="When device is exists",
     *          201="When success",
     *          400="When form validation failed.",
     *          403= {
     *              "when csrf token is invalid",
     *              "when you are a user and you are login in currently",
     *          },
     *      }
     * )
     */
    public function postAction(Request $request)
    {
        $device = new Device();
        /** @var User $user */
        $user = $this->getUser();


        if (!is_null($user)) {
            if (($user->getDevices()->count() == 0) or $user->isMultiDeviceAllowed()) {
                $device->setOwner($this->getUser());
            }
        }

        $form = $this->createCreateForm($device);
        $form->handleRequest($request);

        /** @var Device $persistentDevice */
        $persistentDevice = $this->getDoctrine()->getRepository('FunProUserBundle:Device')
            ->findOneByDeviceIdentifier($device->getDeviceIdentifier());

        if ($persistentDevice) {
            $context = (new Context())
                ->addGroup('Owner')
                ->setMaxDepth(1);
            if ($device->getDeviceToken() == $persistentDevice->getDeviceToken()
                or ($device->getDeviceModel() == $persistentDevice->getDeviceModel()
                    and $device->getDeviceName() == $persistentDevice->getDeviceName())
            ) {
                return $this->view($persistentDevice, Response::HTTP_OK)
                    ->setSerializationContext($context);
            }

            $error = array(
                'code' => 0,
                'message' => $this->get('translator')->trans('this.device.is.exists'),
            );
            return $this->view($error, Response::HTTP_CONFLICT);
        }

        if ($form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
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
     * @Security("is_authenticated()")
     *
     * @Rest\RequestParam(name="token", nullable=false, strict=true)
     * @Rest\RequestParam(name="deviceIdentifier", nullable=false, strict=true)
     */
    public function putTokenAction()
    {
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $token = $fetcher->get('token');
        $device = $this->getDoctrine()->getRepository('FunProUserBundle:Device')
            ->findOneByDeviceIdentifier($fetcher->get('deviceIdentifier'));

        if (is_null($device)) {
            throw $this->createNotFoundException('device is not exists');
        }

        if ($device->getOwner() != $this->getUser()) {
            throw $this->createAccessDeniedException('you.are.not.owner.of.device');
        }

        $device->setDeviceToken($token);
        $this->getDoctrine()->getManager()->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

} 