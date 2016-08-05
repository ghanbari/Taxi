<?php

namespace FunPro\ServiceBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CanceledReasonController
 *
 * @package FunPro\ServiceBundle\Controller
 *
 * @Rest\RouteResource(resource="canceled/reason", pluralize=false)
 * @Rest\NamePrefix("fun_pro_service_api_")
 */
class CanceledReasonController extends FOSRestController
{
    /**
     * Get list of canceled reason.
     *
     * @ApiDoc(
     *      section="Service",
     *      resource=true,
     *      views={"passenger"},
     *      output={
     *          "class"="FunPro\ServiceBundle\Entity\CanceledReason",
     *          "groups"={"Public"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          200="When success",
     *      }
     * )
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction()
    {
        $canceledReason = $this->getDoctrine()->getRepository('FunProServiceBundle:CanceledReason')->findAll();

        return $this->view($canceledReason, Response::HTTP_OK);
    }

    public function getAction($id)
    {
    }
}
