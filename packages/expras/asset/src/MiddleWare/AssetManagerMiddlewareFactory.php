<?php

namespace ExprAs\Asset\MiddleWare;

use Psr\Container\ContainerInterface;
use ExprAs\Asset\Service\AssetManager;

/**
 * @author James Jervis - https://github.com/jerv13
 */
class AssetManagerMiddlewareFactory
{
    /**
     * @param ContainerInterface $serviceContainer
     *
     * @return AssetManagerMiddleware
     */
    public function __invoke(
        $serviceContainer
    ) {
        $instance = $serviceContainer->get(
            AssetManager::class
        );
        return new AssetManagerMiddleware($instance);
    }
}
