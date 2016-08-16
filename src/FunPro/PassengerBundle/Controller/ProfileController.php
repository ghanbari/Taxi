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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * @param Passenger $passenger
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createEditForm(Passenger $passenger)
    {
        $form = $this->createForm(new ProfileType(), $passenger, array(
            'action' => $this->generateUrl('fun_pro_passenger_api_put_passenger_profile'),
            'method' => 'PUT',
            'validation_groups' => array('Profile'),
        ));

        return $form;
    }

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
     *          400="When form validation failed.",
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
        $manager = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $form = $this->createEditForm($user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $manager->flush();

            return $this->view(null, Response::HTTP_NO_CONTENT);
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
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

    /**
     * Upload user avatar (field name: avatar)
     *
     * @ApiDoc(
     *      section="Profile",
     *      resource=true,
     *      views={"passenger"},
     *      statusCodes={
     *          200="When success",
     *          400={
     *              "when file is not valid(maxSize=5M, minWidth=200px, minHeight=200px)",
     *              "when server can not handle upload(code: 1)",
     *          },
     *          403= {
     *              "when you are not login",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_PASSENGER')")
     *
     * @Rest\FileParam(
     *      name="avatar",
     *      image=true,
     *      nullable=false,
     *      requirements={"maxSize"="5M", "minWidth"="200", "minHeight"="200"}
     * )
     */
    public function postAvatarAction()
    {
        $logger = $this->get('logger');

        /** @var UploadedFile $image */
        $image = $this->get('fos_rest.request.param_fetcher')->get('avatar');
        $name = sha1($this->getUser()->getUsername()) . '.' . $image->guessExtension();

        try {
            $image->move($this->getParameter('filesystem.avatar.path'), $name);
        } catch (FileException $e) {
            $logger->addError('avatar can not move');
            $error = array(
                'code' => 1,
                'message' => $this->get('translator')->trans('server.can.not.handle.upload'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $this->get('logger')->addInfo('move avatar');

        $user = $this->getUser();
        $user->setAvatar($name);
        $this->get('fos_user.user_manager')->updateUser($user);
        $this->get('logger')->addInfo('update user avatar');

        return $this->view(
            array('name' => $name, 'path' => $this->get('liip_imagine.cache.manager')->getBrowserPath("images/avatars/$name", 'avatar_mob_thumb')),
            Response::HTTP_CREATED
        );
    }
}
