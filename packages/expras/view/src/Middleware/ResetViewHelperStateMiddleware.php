<?php

declare(strict_types=1);

namespace ExprAs\View\Middleware;

use Laminas\View\HelperPluginManager;
use Mezzio\Swoole\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to reset view helper placeholder state for Swoole/coroutine environments.
 *
 * Clears placeholder containers (HeadMeta, HeadTitle, etc.) at the start of each request
 * to ensure isolated view state in long-running processes.
 *
 * Should run in pre_pipe_routing_middleware (before RouteMiddleware).
 */
final class ResetViewHelperStateMiddleware implements MiddlewareInterface
{
    private const PLACEHOLDER_HELPERS = [
        'headMeta',
        'headTitle',
        'headLink',
        'headStyle',
        'headScript',
        'inlineScript',
    ];

    public function __construct(
        private readonly HelperPluginManager $helperPluginManager,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!($request->getBody() instanceof SwooleStream)) {
            return $handler->handle($request);
        }

        $this->resetPlaceholders();

        return $handler->handle($request);
    }

    private function resetPlaceholders(): void
    {
        foreach (self::PLACEHOLDER_HELPERS as $helperName) {
            if ($this->helperPluginManager->has($helperName)) {
                $helper = $this->helperPluginManager->get($helperName);
                if (method_exists($helper, 'deleteContainer')) {
                    $helper->deleteContainer();
                }
            }
        }
    }
}
