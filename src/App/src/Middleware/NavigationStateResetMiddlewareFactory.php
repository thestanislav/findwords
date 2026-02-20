<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Navigation\NavigationStateResetUpdater;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;

final class NavigationStateResetMiddlewareFactory
{
    public function __invoke(ServiceManager $container): NavigationStateResetMiddleware
    {
        return new NavigationStateResetMiddleware(
            container: $container,
            stateResetUpdater: new NavigationStateResetUpdater(
                $container->get(HelperPluginManager::class),
            ),
        );
    }
}
