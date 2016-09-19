<?php

namespace FunPro\EngineBundle\Authentication;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;

/**
 * {@inheritDoc}
 */
class FailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse(
                array('code' => Response::HTTP_BAD_REQUEST, 'message' => $exception->getMessage()),
                Response::HTTP_BAD_REQUEST
            );
        } else {
            $response = parent::onAuthenticationFailure($request, $exception);
        }

        return $response;
    }
}