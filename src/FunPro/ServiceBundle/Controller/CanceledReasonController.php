<?php

namespace FunPro\ServiceBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FunPro\ServiceBundle\Entity\CanceledReason;
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
     * @Rest\QueryParam(name="group", default="passenger", requirements="driver|passenger", strict=true)
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction()
    {
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $group = strtolower($fetcher->get('group')) == 'passenger' ? CanceledReason::GROUP_PASSENGER : CanceledReason::GROUP_DRIVER;
        $canceledReason = $this->getDoctrine()->getRepository('FunProServiceBundle:CanceledReason')->findAllFilterByGroup($group);

        return $this->view($canceledReason, Response::HTTP_OK);
    }

    public function getAction($id)
    {
    }
}
