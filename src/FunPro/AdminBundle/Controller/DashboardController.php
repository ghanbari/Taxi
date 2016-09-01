<?php

namespace FunPro\AdminBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class DashboardController
 *
 * @package FunPro\AdminBundle\Controller
 *
 * @Rest\NamePrefix("fun_pro_admin_")
 * @Rest\RouteResource(resource="dashboard")
 */
class DashboardController extends FOSRestController
{
    /**
     */
    public function indexAction()
    {
        return $this->routeRedirectView('fun_pro_admin_map_monitor');
    }
}
