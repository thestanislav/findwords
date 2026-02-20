<?php

declare(strict_types=1);

namespace ExprAs\Core\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function in_array;

class XMLHttpRequestDetectMiddleware implements MiddlewareInterface
{
    public static $attributeName = 'isXMLHttpRequest';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array('XMLHttpRequest', $request->getHeader('X-Requested-With'), true)) {
            return $handler->handle($request->withAttribute(static::$attributeName, true));
        }

        return $handler->handle($request);
    }
}
