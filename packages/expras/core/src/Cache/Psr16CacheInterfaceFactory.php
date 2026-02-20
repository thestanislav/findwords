<?php

namespace ExprAs\Core\Cache;

use Psr\SimpleCache\CacheInterface;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Mezzio\Container\SimpleFactoryInterface;
class Psr16CacheInterfaceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new SimpleCacheDecorator(
            $container->get('Laminas\Cache\Storage')
        );
    }
}