<?php

namespace FunPro\ServiceBundle\Controller\V1_0_0;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class WakefulController
 *
 * @package FunPro\ServiceBundle\Controller
 *
 * @Rest\RouteResource(resource="wakeful", pluralize=false)
 * @Rest\NamePrefix("fun_pro_api_")
 */
class WakefulController extends \FunPro\ServiceBundle\Controller\WakefulController
{
}
