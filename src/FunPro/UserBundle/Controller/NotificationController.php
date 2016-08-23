<?php

namespace FunPro\UserBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NotificationController
 *
 * @package FunPro\UserBundle\Controller
 *
 * @Rest\RouteResource(resource="notification", pluralize=false)
 * @Rest\NamePrefix("fun_pro_user_api_")
 */
class NotificationController extends FOSRestController
{
    /**
     * Get last notifications
     *
     * @ApiDoc(
     *      section="Device",
     *      resource=true,
     *      output={
     *          "class"="FunPro\UserBundle\Entity\Message",
     *          "groups"={"Public"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          200="When success",
     *          204="When message is not exists",
     *          403= {
     *              "when you are not passenger or driver",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_PASSENGER') or has_role('ROLE_DRIVER')")
     */
    public function cgetAction()
    {
        $messages = $this->getDoctrine()->getRepository('FunProUserBundle:Message')
            ->getAllNonDownloaded($this->getUser());
        $this->getDoctrine()->getManager()->flush();

        $statusCode = empty($messages) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;
        $context = (new Context())->addGroups(array('Public', 'Owner', 'Device'));
        return $this->view($messages, $statusCode)->setSerializationContext($context);
    }

    public function getAction($id)
    {
    }
}
