<?php

namespace App\Middleware;

use PageCache\AbstractPageCacheMiddleware;
use Laminas\Diactoros\Response\ArraySerializer as ResponseSerializer;
use Laminas\Stdlib\Parameters;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PageCacheMiddleware extends AbstractPageCacheMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->shouldCache($request)) {
            return $handler->handle($request);
        }

        $idGenerator = $this->getIdGenerator();
        $cache       = $this->getStorageAdapter();

        $queryParams = new Parameters($request->getQueryParams());
        $clearCache = $queryParams->offsetExists('__cc__');
        $clearCache && $queryParams->offsetUnset('__cc__');
        $request = $request->withUri($request->getUri()->withQuery(http_build_query($queryParams->toArray())))
            ->withQueryParams($queryParams->toArray());


        $cacheId    = $idGenerator->generate($request);
        $serialized = $cache->getItem($cacheId, $success, $casToken);


        if (!$success || !$serialized || $clearCache) {
            $cacheStatus = self::STATUS_MISS;
            $response    = $handler->handle($request);
            if ($response->getStatusCode() === 200) {
                $serialized  = ResponseSerializer::toArray($response);
                $cache->setItem($cacheId, $serialized);
            }
        } else {
            $cacheStatus = self::STATUS_HIT;
            $response    = ResponseSerializer::fromArray($serialized);
        }

        $response = $response->withHeader('X-Page-Cache', $cacheStatus);

        return $response;
    }
}