<?php

namespace FunPro\UserBundle\Event;

use FunPro\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class RegisterEvent
 *
 * @package FunPro\UserBundle\Event
 */
class RegisterEvent extends Event
{
    /**
     * @var User
     */
    private $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
