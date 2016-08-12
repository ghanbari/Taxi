<?php

namespace FunPro\ServiceBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class ReportController
 *
 * @package FunPro\ServiceBundle\Controller
 *
 * @Rest\RouteResource("report", pluralize=false)
 * @Rest\NamePrefix("fun_pro_service_api_")
 */
class ReportController extends FOSRestController
{
    /**
     * Get Service filtered & pagination
     *
     * @ApiDoc(
     *      section="Service",
     *      resource=true,
     *      output={
     *          "class"="FunPro\ServiceBundle\Entity\Service",
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
     * @Security("has_role('ROLE_PASSENGER') or has_role('ROLE_DRIVER')")
     *
     */
    public function getServiceAction()
    {
        $user = $this->getUser();
    }
}
