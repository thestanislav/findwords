<?php

namespace ExprAs\Logger;

use ExprAs\Core\ConfigAggregator\InvokableProvider;
use ExprAs\Core\ModuleConfigProvider\AbstractProvider;
use ExprAs\Logger\Processor\RequestDataProcessor;
use ExprAs\Logger\Processor\ProcessorBuilder;
use ExprAs\Logger\Processor\ProcessorBuilderFactory;
use ExprAs\Logger\Service\DoctrineHandlerFactory;
use ExprAs\Logger\Service\MonologLoggerFactory;
use ExprAs\Logger\Service\RequestDataProcessorFactory;
use ExprAs\Logger\Service\LoggerRegistry;
use ExprAs\Logger\Service\LoggerRegistryFactory;
use ExprAs\Logger\Service\LoggerAbstractFactory;
use ExprAs\Logger\Provider\LoggerProviderInitializer;
use ExprAs\Logger\LogHandler\DoctrineHandler;
use ExprAs\Logger\LogHandler\ErrorDoctrineHandler;
use ExprAs\Logger\LogHandler\ErrorDoctrineHandlerFactory;
use ExprAs\Logger\Api\ErrorLogAdminHandler;
use ExprAs\Logger\LogHandler\HandlerBuilder;
use ExprAs\Logger\LogHandler\HandlerBuilderFactory;
use ExprAs\Logger\Api\LoggerListHandler;
use ExprAs\Logger\Api\LoggerListHandlerFactory;
use Psr\Log\LoggerInterface;
use Laminas\Stratigility\Middleware\ErrorHandler;
use ExprAs\Logger\Service\LoggingErrorListenerDelegator;

/**
 * Logger Module Configuration Provider
 * 
 * Provides logging infrastructure for the ExprAs framework and modules.
 * Supports multiple loggers, custom entities, and various handler types.
 */
class ConfigProvider extends AbstractProvider
{
    /**
     * Returns the container dependencies
     *
     * @return array
     */
    #[\Override]
    public function getDependencies()
    {
        return [
            'factories' => [
                // Core logger services
                MonologLoggerFactory::class => \Laminas\ServiceManager\Factory\InvokableFactory::class,
                
                // Logger infrastructure
                LoggerRegistry::class => LoggerRegistryFactory::class,
                
                // Handler infrastructure
                DoctrineHandler::class => DoctrineHandlerFactory::class,
                ErrorDoctrineHandler::class => ErrorDoctrineHandlerFactory::class,
                HandlerBuilder::class => HandlerBuilderFactory::class,
                
                // Processor infrastructure
                RequestDataProcessor::class => RequestDataProcessorFactory::class,
                ProcessorBuilder::class => ProcessorBuilderFactory::class,
                
                // Admin interface
                LoggerListHandler::class => LoggerListHandlerFactory::class,
            ],

            'invokables' => [
                ErrorLogAdminHandler::class
            ],

            'abstract_factories' => [
                LoggerAbstractFactory::class,
            ],
            
            'initializers' => [
                LoggerProviderInitializer::class,
            ],
            
            'aliases' => [
                'expras.logger' => LoggerInterface::class
            ],
            
            'delegators' => [
                ErrorHandler::class => [
                    LoggingErrorListenerDelegator::class,
                ],
            ],

            'shared' => [
                ErrorLogAdminHandler::class => false,
            ],
        ];
    }

    #[\Override]
    public function getDependantModules(): array
    {
        return [
            // No external logger dependencies needed for Monolog
        ];
    }
}
