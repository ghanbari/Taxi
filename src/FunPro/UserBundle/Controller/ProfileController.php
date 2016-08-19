<?php

namespace FunPro\UserBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProfileController
 *
 * @package FunPro\UserBundle\Controller
 *
 * @Rest\RouteResource(resource="profile", pluralize=false)
 * @Rest\NamePrefix("fun_pro_user_api_")
 */
class ProfileController extends FOSRestController
{
    /**
     * Upload user avatar (field name: avatar)
     *
     * @ApiDoc(
     *      section="Profile",
     *      resource=true,
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
     * @Security("has_role('ROLE_USER')")
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
            array('name' => $name, 'path' => $this->get('liip_imagine.cache.manager')->getBrowserPath("images/avatars/$name", 'mob_avatar_thumb')),
            Response::HTTP_CREATED
        );
    }
}
