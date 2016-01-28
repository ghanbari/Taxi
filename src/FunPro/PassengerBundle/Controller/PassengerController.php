<?php

namespace FunPro\PassengerBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\PassengerBundle\Form\Type\RegisterType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Context\Context;

/**
 * Class PassengerController
 *
 * @package FunPro\PassengerBundle\Controller
 *
 * @Rest\RouteResource("passenger", pluralize=false)
 * @Rest\NamePrefix("fun_pro_api_")
 */
class PassengerController extends FOSRestController
{
    /**
     * create a form
     *
     * @param Passenger $passenger
     * @param string $method
     * @return \Symfony\Component\Form\Form
     */
    private function getForm(Passenger $passenger, $method='POST')
    {
        $options['method'] = $method;
        $options['action'] = $method == 'POST'
            ? $this->generateUrl('fun_pro_api_post_passenger')
            : $this->generateUrl('fun_pro_api_put_passenger', array('id' => $passenger->getId()));

        $options['validation_groups'] = $method == 'POST' ? 'Register' : 'Profile';

        $requestFormat = $this->get('request_stack')->getCurrentRequest()->getRequestFormat('html');
        $options['csrf_protection'] = $requestFormat == 'html' ?: false;

        $form = $this->createForm(new RegisterType(), $passenger, $options);

        return $form;
    }

    /**
     * Show a form for create of passenger
     *
     * @Security("!is_authenticated()")
     *
     * @Rest\View()
     *
     * @return \Symfony\Component\Form\Form
     */
    public function newAction()
    {
        $passenger = new Passenger();
        $form = $this->getForm($passenger);
        return $form;
    }

    /**
     * Create a passenger
     *
     * @ApiDoc(
     *      section="Passenger",
     *      resource=true,
     *      views={"passenger"},
     *      input={
     *          "class"="FunPro\PassengerBundle\Form\Type\RegisterType",
     *          "data"={
     *              "class"="FunPro\PassengerBundle\Entity\Passenger",
     *              "groups"={"Register"},
     *              "parsers"={
     *                  "Nelmio\ApiDocBundle\Parser\ValidationParser",
     *                  "Nelmio\ApiDocBundle\Parser\JmsMetadataParser",
     *              },
     *          },
     *      },
     *      output={
     *          "class"="FunPro\PassengerBundle\Entity\Passenger",
     *          "groups"={"Public"},
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
     * @Rest\View()
     *
     * @TODO: must send a verification to user mobile
     * @TODO: if mobile number is valid, update mobileCanonical
     *
     * @param Request $request
     * @return \FOS\RestBundle\View\View|\Symfony\Component\Form\Form
     */
    public function postAction(Request $request)
    {
        $passenger = new Passenger();
        $form = $this->getForm($passenger);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('fos_user.user_manager')->updateUser($passenger);

            $context = new Context();
            $context->addGroups(array("Public", "PassengerMobile"))
                ->setMaxDepth(true);
            $view = $this->view($passenger, Response::HTTP_CREATED)
                ->setSerializationContext($context);
            return $view;
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }

    public function editAction($id)
    {

    }

    /**
     * Update a user account.
     *
     * @ApiDoc(
     *      section="Passenger",
     *      resource=true,
     *      views={"passenger"},
     *      input={
     *          "class"="FunPro\PassengerBundle\Form\Type\ProfileType",
     *          "data"={
     *              "class"="FunPro\PassengerBundle\Entity\Passenger",
     *              "groups"={"Profile"},
     *              "parsers"={
     *                  "Nelmio\ApiDocBundle\Parser\ValidationParser",
     *                  "Nelmio\ApiDocBundle\Parser\JmsMetadataParser",
     *              },
     *          },
     *      },
     *      output={
     *          "class"="FunPro\PassengerBundle\Entity\Passenger",
     *          "groups"={"Public"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          204="When success",
     *          400="When form validation failed.",
     *          403= {
     *              "when csrf token is invalid",
     *              "when you are not login",
     *          },
     *      }
     * )
     *
     * @Security("IS_AUTHENTICATED_FULLY")
     *
     * @param Request $request
     * @return \FOS\RestBundle\View\View|\Symfony\Component\Form\Form
     */
    public function putAction(Request $request)
    {
        # if user change email, he must verify email
        # if user change mobile, he must verify mobile
        $passenger = $this->getUser();
        $form = $this->getForm($passenger, 'PUT');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('fos_user.user_manager')->updateUser($passenger);

            return $this->view($passenger, Response::HTTP_CREATED);
        }

        return $form;
    }

    public function removeAction($id)
    {

    }

    /**
     * @param $id
     */
    public function deleteAction($id)
    {

    }
}