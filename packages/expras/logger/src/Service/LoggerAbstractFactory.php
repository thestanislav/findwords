<?php

namespace ExprAs\Logger\Service;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Logger Abstract Factory
 * 
 * Enables automatic creation of loggers from configuration without
 * explicit factory registration.
 * 
 * Example:
 * - Config: 'log' => ['admin.api.logger' => [...]]
 * - Usage: $container->get('admin.api.logger')
 * 
 * The factory will automatically create the logger if configuration exists.
 */
class LoggerAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Can the factory create the requested logger?
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        // Can create if logger name ends with '.logger' and config exists
        if (str_ends_with($requestedName, '.logger')) {
            $config = $container->get('config')['log'] ?? [];
            return isset($config[$requestedName]);
        }

        // Can also create if there's direct config match
        $config = $container->get('config')['log'] ?? [];
        return isset($config[$requestedName]);
    }

    /**
     * Create the logger
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return LoggerInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LoggerInterface
    {
        // Delegate to MonologLoggerFactory with the requested name
        $factory = $container->get(MonologLoggerFactory::class);
        return $factory($container, $requestedName, $options);
    }
}

