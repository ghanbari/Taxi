<?php

namespace FunPro\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ConfigController
 *
 * @package FunPro\UserBundle\Controller
 *
 * @Rest\RouteResource(resource="config", pluralize=false)
 * @Rest\NamePrefix("fun_pro_user_api_")
 */
class ConfigController extends FOSRestController
{
    /**
     * Get global config
     *
     * @ApiDoc(
     *      section="Config",
     *      resource=true,
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
     * @return \FOS\RestBundle\View\View
     */
    public function getGlobalAction()
    {
        $config = array(
            'version.current' => $this->getParameter('api.version.current'),
            'version.supported' => $this->getParameter('api.version.available'),
            'version.supported.till' => $this->getParameter('api.version.supported_till'),
            'gcm.ttl.service_request' => $this->getParameter('gcm.ttl.service_request'),
            'gcm.ttl.service_accept' => $this->getParameter('gcm.ttl.service_accept'),
            'gcm.ttl.service_ready' => $this->getParameter('gcm.ttl.service_ready'),
            'gcm.ttl.service_start' => $this->getParameter('gcm.ttl.service_start'),
            'gcm.ttl.service_finish' => $this->getParameter('gcm.ttl.service_finish'),
            'gcm.ttl.service_cancel' => $this->getParameter('gcm.ttl.service_cancel'),
            'login.max_failure_count' => $this->getParameter('login.max_failure_count'),
            'login.on_failure_lock_for' => $this->getParameter('login.on_failure_lock_for'),
            'register.max_token_request' => $this->getParameter('register.max_token_request'),
            'register.reset_token_request_after_second' => $this->getParameter('register.reset_token_request_after_second'),
            'service.visible_radius' => $this->getParameter('service.visible_radius'),
            'service.passenger.can_cancel_till' => $this->getParameter('service.passenger.can_cancel_till'),
            'service.propagation_list.max' => $this->getParameter('service.propagation_list.max'),
            'service.driver.allowed_radius_for_ready' => $this->getParameter('service.driver.allowed_radius_for_ready'),
        );

        return $this->view($config, Response::HTTP_OK);
    }
}
