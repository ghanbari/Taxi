<?php

namespace FunPro\FinancialBundle;

/**
 * Class FinancialEvents
 *
 * @package FunPro\FinancialBundle
 */
class FinancialEvents
{
    /**
     * This event occur when user payed service with credit or cash.
     *
     * The Event listener receive an instance of FunPro\FinancialBundle\Event\PaymentEvent class
     */
    const PAYMENT_EVENT = 'financial.payment';

    /**
     * This event occur when one invited passenger complete register.
     *
     * The Event listener receive an instance of FunPro\UserBundle\Event\RegisterEvent class
     */
    const REWARD_REFERRER = 'financial.reward.referer';
}
