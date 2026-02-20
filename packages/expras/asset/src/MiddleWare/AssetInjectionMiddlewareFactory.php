<?php

namespace ExprAs\Asset\MiddleWare;

use ExprAs\Asset\Service\RouteAssetInjector;
use Psr\Container\ContainerInterface;
use Laminas\View\HelperPluginManager;

class AssetInjectionMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new AssetInjectionMiddleware(
            $container->get(RouteAssetInjector::class),
            $container->get(HelperPluginManager::class)
        );
    }
}
