<?php

namespace ExprAs\User\OwnerAware;

use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class OwnerListenerInjectorMiddleware implements MiddlewareInterface
{
    use ServiceContainerAwareTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        if (($user = $request->getAttribute(UserInterface::class))) {
            $this->getContainer()->get(OwnerListener::class)->setUser($user);
        }

        return $handler->handle($request);
    }
}
