<?php

namespace FunPro\EngineBundle\Authentication;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

/**
 * {@inheritDoc}
 */
class SuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token ) {
        if($request->isXmlHttpRequest()) {
            $response = new JsonResponse(array('code' => Response::HTTP_OK, 'username' => $token->getUsername()));
        } else {
            $response = parent::onAuthenticationSuccess($request, $token);
        }

        return $response;
    }
}