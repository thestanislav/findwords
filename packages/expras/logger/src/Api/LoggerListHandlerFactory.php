<?php

namespace ExprAs\Logger\Api;

use ExprAs\Logger\Service\LoggerRegistry;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for LoggerListHandler
 */
class LoggerListHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LoggerListHandler
    {
        return new LoggerListHandler(
            $container->get(LoggerRegistry::class)
        );
    }
}

