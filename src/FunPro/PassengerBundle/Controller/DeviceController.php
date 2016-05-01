<?php

namespace FunPro\PassengerBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\EngineBundle\Entity\Device;
use FunPro\EngineBundle\Form\DeviceType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\VarDumper;

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
        $form = $this->createForm(DeviceType::class, $device, array(
            'action' => '',
            'method' => 'POST',
            'csrf_protection' => false,
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

        if ($form->get('deviceIdentifier')->getErrors()->count()) {
            return $this->view(null, Response::HTTP_CONFLICT);
        }

        if ($form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($device);
            $manager->flush();

            return $this->view($device, Response::HTTP_CREATED);
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }
} 