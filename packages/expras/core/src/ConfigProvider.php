<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 00:00
 */

namespace ExprAs\Core;

use ExprAs\Core\ConfigAggregator\InvokableProvider;
use ExprAs\Core\Console\CacheFlush;
use ExprAs\Core\Console\ShowConfig;
use ExprAs\Core\Http\CurrentRequestHolder;
use ExprAs\Core\PhpSettings\ConfigSetterDelegator;
use ExprAs\Core\PhpSettings\SettingManager;
use ExprAs\Core\ServiceManager\Delegator\EnvironmentVariablesInjector;
use ExprAs\Core\ServiceManager\Delegator\LaminasCliConfigProvider;
use ExprAs\Core\ServiceManager\Delegator\PipelineAndRoutesDelegator;
use ExprAs\Core\ServiceManager\Delegator\ServerRequestDelegatorFactory;
use ExprAs\Core\ServiceManager\Delegator\SapiStreamEmitterInjector;
use ExprAs\Core\ServiceManager\Factory\BodyParamsStrategyFactory;
use ExprAs\Core\ServiceManager\Factory\EventManagerFactory;
use ExprAs\Core\ServiceManager\ServiceContainerInitializer;
use Laminas\Cache\Service\StorageCacheFactory;
use Laminas\Cache\StorageFactory;
use Laminas\EventManager\EventManager;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Mezzio\Application;
use Mezzio\Container\ApplicationConfigInjectionDelegator;
use ExprAs\Core\Cache\Psr16CacheInterfaceFactory;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;
use Laminas\Stratigility\Middleware\ErrorHandler;
use ExprAs\Core\ErrorListener\SwooleErrorListenerDelegator;
use ExprAs\Core\ErrorListener\SwooleErrorListener;
class ConfigProvider extends ModuleConfigProvider\AbstractProvider
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
                StorageFactory::class => StorageCacheFactory::class,
                EventManager::class => EventManagerFactory::class,
                BodyParamsMiddleware::class => BodyParamsStrategyFactory::class,
                CacheInterface::class => Psr16CacheInterfaceFactory::class,
            ],
            'aliases' => [
                'Laminas\Cache\Storage' => StorageFactory::class,
                
                'configuration' => 'config',
                'Config' => 'config',
                'Configuration' => 'config',
                'EventManager' => EventManager::class
            ],
            'initializers' => [
                ServiceContainerInitializer::class
            ],
            'invokables' => [
                SwooleErrorListener::class,
                SettingManager::class,
                CacheFlush::class,
                ShowConfig::class,
                CurrentRequestHolder::class,
                ServerRequestDelegatorFactory::class,
            ],
            'delegators' => [
                Application::class => [
                    EnvironmentVariablesInjector::class,
                    ConfigSetterDelegator::class,
                    ApplicationConfigInjectionDelegator::class,
                    PipelineAndRoutesDelegator::class,
                    LaminasCliConfigProvider::class
                ],
                ServerRequestInterface::class => [
                    ServerRequestDelegatorFactory::class,
                ],
                \Symfony\Component\Console\Application::class => [
                    ConfigSetterDelegator::class,
                ],
                EmitterInterface::class => [
                    SapiStreamEmitterInjector::class
                ],
                ErrorHandler::class => [
                    SwooleErrorListenerDelegator::class
                ]
            ],
        ];
    }

    #[\Override]
    public function getDependantModules()
    {
        return [
            new InvokableProvider(\Mezzio\ConfigProvider::class),

        ];
    }
}
