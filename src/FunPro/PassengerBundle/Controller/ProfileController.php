<?php

namespace FunPro\PassengerBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\PassengerBundle\Form\Type\ProfileType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProfileController
 *
 * @package FunPro\PassengerBundle\Controller
 *
 * @Rest\RouteResource(resource="profile", pluralize=false)
 * @Rest\NamePrefix("fun_pro_passenger_api_")
 */
class ProfileController extends FOSRestController
{
    /**
     * Update user profile.
     *
     * @ApiDoc(
     *      section="Profile",
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
     *      statusCodes={
     *          204="When success",
     *          400={
     *              "When form validation failed.",
     *              "email is duplicate (code: 2)"
     *          },
     *          403= {
     *              "when csrf token is invalid",
     *              "when you are not login",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_PASSENGER')")
     *
     * @param Request $request
     *
     * @return \FOS\RestBundle\View\View
     */
    public function putAction(Request $request)
    {
        if ($born = $request->request->get('born')) {
            $request->request->set('born', \DateTime::createFromFormat('U', $born)->format('Y-m-d'));
        }

        $user = $this->getUser();
        $form = $this->createEditForm($user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('fos_user.user_manager')->updateUser($user);

            return $this->view(null, Response::HTTP_NO_CONTENT);
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Passenger $passenger
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createEditForm(Passenger $passenger)
    {
        $requestFormat = $this->get('request_stack')->getCurrentRequest()->getRequestFormat('html');
        $form = $this->createForm(new ProfileType(), $passenger, array(
            'action' => $this->generateUrl('fun_pro_passenger_api_put_passenger_profile'),
            'method' => 'PUT',
            'validation_groups' => array('Profile'),
            'csrf_protection' => $requestFormat === 'html' ?: false,
        ));

        return $form;
    }

    /**
     * Get current user profile.
     *
     * @ApiDoc(
     *      section="Profile",
     *      resource=true,
     *      views={"passenger"},
     *      output={
     *          "class"="FunPro\PassengerBundle\Entity\Passenger",
     *          "groups"={"Public", "Profile"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          200="When success",
     *          403= {
     *              "when you are not login currently",
     *          },
     *      }
     * )
     *
     * @Security("is_authenticated() and has_role('ROLE_PASSENGER')")
     *
     * @return View
     */
    public function getAction()
    {
        $user = $this->getUser();

        $context = new Context();
        $context->addGroups(array('Public', 'Profile'))
            ->setMaxDepth(2);
        return $this->view($user, Response::HTTP_OK)
            ->setSerializationContext($context);
    }
}
