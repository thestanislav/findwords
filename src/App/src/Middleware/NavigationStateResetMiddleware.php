<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Navigation\NavigationStateResetUpdater;
use Laminas\ServiceManager\ServiceManager;
use Mezzio\Swoole\SwooleStream;
use Mimmi20\Mezzio\Navigation\Navigation;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to reset navigation state for Swoole/coroutine environments.
 *
 * Must run AFTER RouteMiddleware (in post_pipe_routing_middleware) so that
 * RouteResult is available in request attributes.
 *
 * This middleware:
 * - Builds fresh navigation pages and replaces existing singleton's pages
 * - Updates navigation pages with current RouteResult for isActive()/getHref()
 * - Clears breadcrumb container
 * - Rewinds navigation iterator
 */
final class NavigationStateResetMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ServiceManager $container,
        private readonly NavigationStateResetUpdater $stateResetUpdater,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!($request->getBody() instanceof SwooleStream)) {
            return $handler->handle($request);
        }

        /** @var Navigation $existingNavigation */
        $existingNavigation = $this->container->get('DictionaryNavigation');

        /** @var Navigation $freshNavigation */
        $freshNavigation = $this->container->build('DictionaryNavigation');

        $existingNavigation->setPages($freshNavigation->getPages());

        $this->stateResetUpdater->resetFromRequest($existingNavigation, $request);
        $existingNavigation->rewind();

        return $handler->handle($request);
    }
}
