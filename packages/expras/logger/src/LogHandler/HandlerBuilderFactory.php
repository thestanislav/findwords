<?php

namespace ExprAs\Logger\LogHandler;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for HandlerBuilder
 */
class HandlerBuilderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): HandlerBuilder
    {
        return new HandlerBuilder($container);
    }
}

