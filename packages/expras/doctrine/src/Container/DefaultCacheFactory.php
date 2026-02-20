<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 20:31
 */

namespace ExprAs\Doctrine\Container;

use DoctrineModule\Cache\LaminasStorageCache;
use Psr\Container\ContainerInterface;
use Laminas\Cache\StorageFactory;

class DefaultCacheFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new LaminasStorageCache($container->get(StorageFactory::class));
    }
}
