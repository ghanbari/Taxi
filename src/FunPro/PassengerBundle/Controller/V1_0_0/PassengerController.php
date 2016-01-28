<?php

namespace FunPro\PassengerBundle\Controller\V1_0_0;

use FunPro\PassengerBundle\Controller\PassengerController as MasterPassenger;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class PassengerController
 *
 * @package FunPro\PassengerBundle\Controller
 *
 * @Rest\RouteResource("passenger", pluralize=false)
 * @Rest\NamePrefix("fun_pro_api_")
 */
class PassengerController extends MasterPassenger
{
}