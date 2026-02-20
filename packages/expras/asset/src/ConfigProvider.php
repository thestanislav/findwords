<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 00:00
 */

namespace ExprAs\Asset;

use Assetic\Filter\UglifyJs2Filter;
use AssetManager\Expressive\Module;
use ExprAs\Asset\Filter\LessFilter;
use ExprAs\Asset\Filter\LessFilterFactory;
use ExprAs\Asset\Filter\UglifyJs2FilterFactory;
use ExprAs\Asset\MiddleWare\AssetInjectionMiddleware;
use ExprAs\Asset\MiddleWare\AssetInjectionMiddlewareFactory;
use ExprAs\Asset\MiddleWare\AssetManagerMiddleware;
use ExprAs\Asset\MiddleWare\AssetManagerMiddlewareFactory;
use ExprAs\Asset\Resolver\PatternResolver;
use ExprAs\Asset\Resolver\PatternResolverFactory;
use ExprAs\Asset\Service\RouteAssetInjector;
use ExprAs\Asset\Service\RouteAssetInjectorFactory;
use ExprAs\Core\ConfigAggregator\InvokableProvider;
use ExprAs\Core\ModuleConfigProvider\AbstractProvider;
use Laminas\Cache\Service\StorageCacheFactory;
use Laminas\Cache\StorageFactory;
use ExprAs\Asset\Service\AssetManager;
use ExprAs\Asset\Service\AssetManagerFactory;

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
                StorageFactory::class => StorageCacheFactory::class,
                AssetManager::class => AssetManagerFactory::class,
                RouteAssetInjector::class => RouteAssetInjectorFactory::class,
                AssetInjectionMiddleware::class => AssetInjectionMiddlewareFactory::class,
                PatternResolver::class => PatternResolverFactory::class,
                LessFilter::class => LessFilterFactory::class,
                UglifyJs2Filter::class => UglifyJs2FilterFactory::class,
                AssetManagerMiddleware::class                => AssetManagerMiddlewareFactory::class,
            ],
            'invokables' => ['AsAsset\ListenerAggregate' => 'AsAsset\ListenerAggregate', Filter\SassFilter::class => Filter\SassFilter::class],
            'aliases' => [
                'Laminas\Cache\Storage' => StorageFactory::class,
                \AssetManager\Service\AssetManager::class => AssetManager::class,
                'SassFilter' => Filter\SassFilter::class,
                'SassCompiler' => Filter\SassFilter::class,
            ]
        ];
    }

    #[\Override]
    public function getConfig()
    {
        if (!class_exists('AssetManager\Module')) {
            return [];
        }
        $module = new \AssetManager\Module();
        $config = $module->getConfig();
        $config['dependencies'] = $config['service_manager'];

        return $config;

    }

}
