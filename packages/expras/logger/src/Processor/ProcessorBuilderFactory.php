<?php

namespace ExprAs\Logger\Processor;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for ProcessorBuilder
 */
class ProcessorBuilderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ProcessorBuilder
    {
        return new ProcessorBuilder($container);
    }
}

