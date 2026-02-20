<?php

namespace ExprAs\Admin\Factory;

use ExprAs\Admin\HydratorConfigurator\JsonApiFieldExcludeConfigurator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class JsonApiFieldExcludeConfiguratorFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {

        $mappings = $container->get('config')['exprass_admin']['resource_mappings'];
        $fields = [];
        foreach ($mappings as $_map) {

            if (!isset($_map['spec']) || !isset($_map['spec']['fieldSpec'])) {
                continue;
            }

            $fields[$_map['entity']] = array_map(fn ($fieldSpec) => $fieldSpec['name'] ?? $fieldSpec['source'], $_map['spec']['fieldSpec']);
        }

        return new JsonApiFieldExcludeConfigurator($fields);
    }
}
