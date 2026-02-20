<?php

namespace ExprAs\Logger\Processor;

use Psr\Container\ContainerInterface;

/**
 * Processor Builder
 * 
 * Factory for creating Monolog processors from configuration.
 * Implements Factory Method pattern for processor creation.
 */
class ProcessorBuilder
{
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * Build a processor from configuration
     *
     * @param string $name Processor type name (requestData, introspection, custom)
     * @param array $config Processor configuration
     * @return callable|null
     */
    public function build(string $name, array $config): ?callable
    {
        return match ($name) {
            'requestData' => $this->buildRequestDataProcessor($config),
            'introspection' => $this->buildIntrospectionProcessor($config),
            'custom' => $this->buildCustomProcessor($config),
            default => null,
        };
    }

    /**
     * Build request data processor
     */
    private function buildRequestDataProcessor(array $config): callable
    {
        return $this->container->get(RequestDataProcessor::class);
    }

    /**
     * Build introspection processor
     *
     * Adds file, line, class, and function information to log records.
     * Useful for debugging where log messages are generated from.
     */
    private function buildIntrospectionProcessor(array $config): callable
    {
        $options = $config['options'] ?? [];

        return new \Monolog\Processor\IntrospectionProcessor(
            level: $options['level'] ?? \Monolog\Level::Debug,
            skipClassesPartials: $options['skipClassesPartials'] ?? [
                'Monolog\\',
                'ExprAs\\Logger\\',
                'Laminas\\',
                'Mezzio\\',
                'Doctrine\\'
            ],
            skipStackFramesCount: $options['skipStackFramesCount'] ?? 0
        );
    }

    /**
     * Build a custom processor from service name
     * 
     * Uses lazy loading to avoid circular dependency issues.
     * The processor is not retrieved from container until it's actually invoked.
     */
    private function buildCustomProcessor(array $config): ?callable
    {
        $serviceName = $config['options']['service'] ?? null;
        if (!$serviceName) {
            return null;
        }

        // Return a lazy-loading wrapper to avoid circular dependencies
        return function ($record) use ($serviceName) {
            $processor = $this->container->get($serviceName);
            
            if (!is_callable($processor)) {
                return $record;
            }
            
            return $processor($record);
        };
    }
}

