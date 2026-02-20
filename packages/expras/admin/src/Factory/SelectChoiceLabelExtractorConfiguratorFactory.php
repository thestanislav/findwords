<?php

namespace ExprAs\Admin\Factory;

use ExprAs\Admin\HydratorConfigurator\SelectChoiceLabelExtractorConfigurator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SelectChoiceLabelExtractorConfiguratorFactory implements FactoryInterface
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

            $_fields = array_reduce(
                $_map['spec']['fieldSpec'], function ($currey, $item) {
                    if (in_array($item['type'], ['select']) && is_array($item['choices'])) {
                        $currey[$item['name']] = array_reduce(
                            $item['choices'], function ($c, $v) {
                                $c[$v['id']] = $v['name'];
                                return $c;
                            }, []
                        );
                    }
                    return $currey;
                }, []
            );
            if ($_fields) {
                $fields[$_map['entity']] = $_fields;
            }
        }

        return new SelectChoiceLabelExtractorConfigurator($fields);
    }

}
