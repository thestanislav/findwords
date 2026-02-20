<?php

declare(strict_types=1);

namespace ExprAs\Core\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function in_array;

class JsonQueryDetectMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jsonQuery = rawurldecode($request->getUri()->getQuery());
        if (strlen($jsonQuery) === 0) {
            return $handler->handle($request);
        }
        $queryParams = json_decode($jsonQuery, true);
        if (!is_array($queryParams) || json_last_error() !== JSON_ERROR_NONE) {
            return $handler->handle($request);
        }

        return $handler->handle($request->withQueryParams($queryParams));
    }
}
