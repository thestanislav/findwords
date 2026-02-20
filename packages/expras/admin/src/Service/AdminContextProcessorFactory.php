<?php

namespace ExprAs\Admin\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Factory for AdminContextProcessor
 */
class AdminContextProcessorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AdminContextProcessor
    {
        // Create a closure that returns the current request
        $requestFactory = function () use ($container) {
            if ($container->has(ServerRequestInterface::class)) {
                return $container->get(ServerRequestInterface::class);
            }
            return null;
        };

        return new AdminContextProcessor($requestFactory);
    }
}

