<?php

namespace FunPro\PassengerBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\EngineBundle\Entity\Device;
use FunPro\EngineBundle\Form\DeviceType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DeviceController
 *
 * @package FunPro\PassengerBundle\Controller
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
            'action' => $this->generateUrl('fun_pro_api_post_passenger_device'),
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
     *      views={"passenger"},
     *      input={
     *          "class"="FunPro\EngineBundle\Form\DeviceType",
     *          "data"={
     *              "class"="FunPro\EngineBundle\Entity\Device",
     *              "groups"={"Owner"},
     *              "parsers"={
     *                  "Nelmio\ApiDocBundle\Parser\ValidationParser",
     *                  "Nelmio\ApiDocBundle\Parser\JmsMetadataParser",
     *              },
     *          },
     *      },
     *      output={
     *          "class"="FunPro\EngineBundle\Entity\Device",
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
     *
     * @Security("!is_authenticated()")
     *
     */
    public function postAction(Request $request)
    {
        $device = new Device();
        $form = $this->createCreateForm($device);
        $form->handleRequest($request);

        /** @var Device $persistentDevice */
        $persistentDevice = $this->getDoctrine()->getRepository('FunProEngineBundle:Device')
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
} 