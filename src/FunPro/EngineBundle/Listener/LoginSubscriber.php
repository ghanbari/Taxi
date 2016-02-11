<?php

namespace FunPro\EngineBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * {@inheritDoc}
 */
class LoginSubscriber implements EventSubscriberInterface
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var integer
     */
    private $maxFailureCount;

    /**
     * @param Registry $doctrine
     * @param $maxFailureCount
     */
    public function __construct(Registry $doctrine, $maxFailureCount)
    {
        $this->doctrine = $doctrine;
        $this->maxFailureCount = $maxFailureCount;
    }

    public static function getSubscribedEvents()
    {
        return array(
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onFailure',
            SecurityEvents::INTERACTIVE_LOGIN => 'onSuccess',
        );
    }

    public function onFailure(AuthenticationFailureEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $username = $token->getUsername();
        /** @var User $user */
        $user = $this->doctrine->getRepository('NikEngineBundle:User')->findOneByUsername($username);

        if (!$user) {
            return;
        }

        $user->setWrongPasswordCount($user->getWrongPasswordCount() + 1);

        if ($user->getWrongPasswordCount() > $this->maxFailureCount) {
            #TODO: send a sms or email for unlock
            $user->setLocked(true);
        }

        $this->doctrine->getManager()->flush();
    }

    public function onSuccess(InteractiveLoginEvent $event)
    {
        /** @var User $user */
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof User and $user->getWrongPasswordCount() > 0) {
            $user->setLocked(false);
            $user->setWrongPasswordCount(0);
            $this->doctrine->getManager()->flush();
        }
    }
} 