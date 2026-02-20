<?php

namespace ExprAs\Admin\Middleware;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for AdminApiLoggingMiddleware
 */
class AdminApiLoggingMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AdminApiLoggingMiddleware
    {
        return new AdminApiLoggingMiddleware(
            $container->get('admin.api.logger')
        );
    }
}

