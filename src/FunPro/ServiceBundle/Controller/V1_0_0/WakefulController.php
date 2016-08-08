<?php

namespace FunPro\ServiceBundle\Controller\V1_0_0;

use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\Constraints as Assert;

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
