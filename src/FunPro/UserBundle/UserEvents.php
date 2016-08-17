<?php

namespace FunPro\UserBundle;

/**
 * Class UserEvents
 *
 * @package FunPro\UserBundle
 */
class UserEvents
{
    /**
     * The REGISTRATION_CONFIRMED event occurs after confirming the account.
     *
     * This event allows you to access the response which will be sent.
     * The event listener method receives a FunPro\UserBundle\Event\RegisterEvent instance.
     */
    const REGISTRATION_CONFIRMED = 'fun_pro_user.registration.confirmed';
}
