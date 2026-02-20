<?php

declare(strict_types=1);

namespace ExprAs\View\Middleware;

use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;

final class ResetViewHelperStateMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): ResetViewHelperStateMiddleware
    {
        return new ResetViewHelperStateMiddleware(
            helperPluginManager: $container->get(HelperPluginManager::class),
        );
    }
}
