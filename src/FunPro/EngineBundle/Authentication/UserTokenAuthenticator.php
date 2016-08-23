<?php

namespace FunPro\EngineBundle\Authentication;

use FunPro\UserBundle\Entity\User;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class UserTokenAuthenticator
 *
 * @package FunPro\EngineBundle\Authentication
 */
class UserTokenAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var TokenStorage
     */
    private $storage;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param EntityManager         $manager
     * @param TokenStorage $storage
     * @param Logger                $logger
     */
    public function __construct(EntityManager $manager, TokenStorage $storage, Logger $logger)
    {
        $this->manager = $manager;
        $this->storage = $storage;
        $this->logger = $logger;
    }

    /**
     * Called on every request. Return whatever credentials you want,
     * or null to stop authentication.
     */
    public function getCredentials(Request $request)
    {
        if ($this->storage->getToken() and $this->storage->getToken()->isAuthenticated()
            and !$this->storage->getToken() instanceof AnonymousToken
        ) {
            $this->logger->addDebug('user is authenticated, cancel guard');
            return;
        }

        if (!$token = $request->headers->get('X-AUTH-TOKEN')) {
            return;
        }

        return array(
            'token' => $token,
        );
    }

    /**
     * @param mixed                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return \FunPro\UserBundle\Entity\User|null|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $apiKey = $credentials['token'];

        $this->user = $this->manager->getRepository('FunProUserBundle:User')->findOneByApiKey($apiKey);

        return $this->user;
    }

    /**
     * @param mixed         $credentials
     * @param UserInterface $user
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $providerKey
     *
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $this->user->setLastLogin(new \DateTime());
        $this->manager->flush();

        return null;
    }

    /**
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return null|JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return;
    }

    /**
     * Called when authentication is needed, but it's not sent
     *
     * @param Request                 $request
     * @param AuthenticationException $authException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            'message' => 'Authentication Required'
        );

        return new JsonResponse($data, 401);
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
