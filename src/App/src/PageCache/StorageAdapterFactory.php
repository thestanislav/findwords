<?php

namespace App\PageCache;

use PageCache\PageCacheMiddleware;
use ExprAs\Core\Cache\Plugin\Gzip;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Cache\Service\StorageAdapterFactory as LaminasStorageAdapterFactory;

class StorageAdapterFactory implements FactoryInterface
{

    /**
     * Create an object
     *
     * @param  string             $requestedName
     * @param  null|array<mixed>  $options
     * @return object
     * @throws ServiceNotFoundException If unable to resolve the service.
     * @throws ServiceNotCreatedException If an exception is raised when creating a service.
     * @throws ContainerException If any other error occurs.
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null) {
        $config = $container->get('config')[PageCacheMiddleware::class]['storage_adapter'];
        /** @var LaminasStorageAdapterFactory $adapterService */
        $adapterService =  $container->get(LaminasStorageAdapterFactory::class);
        return $adapterService->createFromArrayConfiguration($config);
    }
}