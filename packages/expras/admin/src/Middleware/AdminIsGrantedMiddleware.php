<?php

namespace ExprAs\Admin\Middleware;

use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AdminIsGrantedMiddleware implements MiddlewareInterface
{
    use ServiceContainerAwareTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}
