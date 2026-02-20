<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PageCacheHeaders implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (str_starts_with($request->getUri()->getPath(), '/.admin') || strcmp($request->getMethod(), 'post') === 0) {
            return $response
                ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
                ->withHeader('CDN-Cache-Control', 'no-store, no-cache, must-revalidate')
                ->withHeader('Pragma', 'no-store, no-cache, must-revalidate')
                ->withHeader('Expires', gmdate ("D, d M Y H:i:s", time() - (30 * 24 * 60 * 60)));
        }
        return $response
            ->withHeader('Cache-Control', 'public')
            ->withHeader('Pragma', 'public')
            ->withHeader('Expires', gmdate ("D, d M Y H:i:s", time() + (30 * 24 * 60 * 60)));
    }
}