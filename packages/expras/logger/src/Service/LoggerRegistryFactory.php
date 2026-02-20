<?php

namespace ExprAs\Logger\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for LoggerRegistry
 * 
 * Creates a singleton instance of LoggerRegistry
 */
class LoggerRegistryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LoggerRegistry
    {
        return new LoggerRegistry();
    }
}

