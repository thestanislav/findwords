<?php

namespace ExprAs\Logger\Service;

use Monolog\Logger;
use ExprAs\Logger\LogHandler\HandlerBuilder;
use ExprAs\Logger\Processor\ProcessorBuilder;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Monolog Logger Factory
 * 
 * Creates PSR-3 compliant Monolog loggers from configuration.
 * Uses HandlerBuilder and ProcessorBuilder for component creation.
 * Auto-registers created loggers with LoggerRegistry.
 */
class MonologLoggerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Logger
    {
        // Determine logger name
        $loggerName = $this->resolveLoggerName($requestedName, $options);
        
        // Get configuration
        $config = $container->get('config')['log'][$loggerName] ?? [];
        
        // Create logger instance
        $logger = new Logger($config['name'] ?? $loggerName);

        // Get builders
        $handlerBuilder = $container->get(HandlerBuilder::class);
        $processorBuilder = $container->get(ProcessorBuilder::class);

        // Add handlers
        $handlers = $options['handlers'] ?? $config['handlers'] ?? [];
        foreach ($handlers as $handlerName => $handlerConfig) {
            // Skip disabled handlers (set to null)
            if ($handlerConfig === null) {
                continue;
            }

            $handler = $handlerBuilder->build(
                $handlerConfig['name'] ?? $handlerName,
                $handlerConfig
            );
            if ($handler) {
                $logger->pushHandler($handler);
            }
        }

        // Add processors
        $processors = $options['processors'] ?? $config['processors'] ?? [];
        foreach ($processors as $processorName => $processorConfig) {
            $processor = $processorBuilder->build(
                $processorConfig['name'] ?? $processorName,
                $processorConfig
            );
            if ($processor) {
                $logger->pushProcessor($processor);
            }
        }

        // Register logger with metadata
        $registry = $container->get(LoggerRegistry::class);
        $registry->register($loggerName, $logger, $config['metadata'] ?? []);

        return $logger;
    }

    /**
     * Resolve logger name from requested service name or options
     */
    private function resolveLoggerName(string $requestedName, ?array $options): string
    {
        // If options provide name, use it
        if (isset($options['name'])) {
            return $options['name'];
        }

        // If requested name is LoggerInterface, use default
        if ($requestedName === \Psr\Log\LoggerInterface::class) {
            return 'expras_logger';
        }

        // Otherwise use requested name as logger name
        return $requestedName;
    }
}
