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
     *          403= {
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
            'app.download.url' => $this->getParameter('app.download.url'),
            'gcm.ttl.service_request' => $this->getParameter('gcm.ttl.service_request'),
            'gcm.ttl.service_accept' => $this->getParameter('gcm.ttl.service_accept'),
            'gcm.ttl.service_ready' => $this->getParameter('gcm.ttl.service_ready'),
            'gcm.ttl.service_start' => $this->getParameter('gcm.ttl.service_start'),
            'gcm.ttl.service_finish' => $this->getParameter('gcm.ttl.service_finish'),
            'gcm.ttl.service_cancel' => $this->getParameter('gcm.ttl.service_cancel'),
            'gcm.enabled' => $this->getParameter('gcm.enabled'),
            'login.max_failure_count' => $this->getParameter('login.max_failure_count'),
            'login.on_failure_lock_for' => $this->getParameter('login.on_failure_lock_for'),
            'register.max_token_request' => $this->getParameter('register.max_token_request'),
            'register.reset_token_request_after_second' => $this->getParameter('register.reset_token_request_after_second'),
            'device.gps.retry' => $this->getParameter('device.gps.retry'),
            'device.notification.retry' => $this->getParameter('device.notification.retry'),
            'service.visible_radius' => $this->getParameter('service.visible_radius'),
            'service.passenger.can_cancel_till' => $this->getParameter('service.passenger.can_cancel_till'),
            'service.propagation_list.max' => $this->getParameter('service.propagation_list.max'),
            'service.driver.allowed_radius_for_ready' => $this->getParameter('service.driver.allowed_radius_for_ready'),
            'sms.operators_numbers' => $this->getSmsOperatorNumbers(),
            'financial.reward.referer' => $this->getParameter('financial.reward.referer'),
            'financial.reward.referer.default_currency' => $this->getParameter('financial.reward.referer.default_currency'),
            'financial.reward.payment.cash' => $this->getParameter('financial.reward.payment.cash'),
            'financial.reward.payment.credit' => $this->getParameter('financial.reward.payment.credit'),
            'financial.commission.payment.cash' => $this->getParameter('financial.commission.payment.cash'),
            'financial.commission.payment.credit' => $this->getParameter('financial.commission.payment.credit'),
        );

        return $this->view($config, Response::HTTP_OK);
    }

    private function getSmsOperatorNumbers()
    {
        $numbers = array();
        if ($this->container->hasParameter('sms_ir.from') and $this->getParameter('sms_ir.from') !== '') {
            $numbers[] = $this->getParameter('sms_ir.from');
        }

        if ($this->container->hasParameter('nik_sms.from') and $this->getParameter('nik_sms.from') !== '') {
            $numbers[] = $this->getParameter('nik_sms.from');
        }

        return $numbers;
    }
}
