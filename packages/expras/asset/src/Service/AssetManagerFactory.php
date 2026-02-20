<?php

namespace ExprAs\Asset\Service;

use AssetManager\Resolver\AggregateResolver;
use AssetManager\Service\AssetCacheManager;
use AssetManager\Service\AssetFilterManager;
use AssetManager\Service\AssetManagerServiceFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Factory class for AssetManagerService
 *
 * @category AssetManager
 * @package  AssetManager
 */
class AssetManagerFactory extends AssetManagerServiceFactory
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config             = $container->get('config');
        $assetManagerConfig = [];

        if (!empty($config['asset_manager'])) {
            $assetManagerConfig = $config['asset_manager'];
        }

        $assetManager = new AssetManager(
            $container->get(AggregateResolver::class),
            $assetManagerConfig
        );

        $assetManager->setAssetFilterManager(
            $container->get(AssetFilterManager::class)
        );

        $assetManager->setAssetCacheManager(
            $container->get(AssetCacheManager::class)
        );

        return $assetManager;
    }

    /**
     * {@inheritDoc}
     *
     * @return AssetManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, AssetManager::class);
    }
}
