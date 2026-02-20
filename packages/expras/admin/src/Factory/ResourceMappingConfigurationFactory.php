<?php

namespace ExprAs\Admin\Factory;

use ExprAs\Admin\ResourceMapping\Configuration;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Stdlib\SplPriorityQueue;
use Psr\Container\ContainerInterface;

class ResourceMappingConfigurationFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config');

        return new Configuration(array_values($config['exprass_admin']['resource_mappings']));
    }
}
