<?php

namespace ExprAs\User\MezzioAuthentication;

use ExprAs\User\Entity\User;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AbstractAdapter implements AuthenticationInterface
{
    /**
     * Authenticate the PSR-7 request and return a valid user
     * or null if not authenticated
     */
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        return $request->getAttribute(User::class);
    }

    /**
     * Generate the unauthorized response
     */
    abstract public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface;

    public function complete(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}
