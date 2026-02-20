<?php

namespace ExprAs\Asset\Resolver;

use Interop\Container\ContainerInterface;

class PatternResolverFactory
{
    /**
     * {@inheritDoc}
     *
     * @return PatternResolver
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $map = [];

        if (isset($config['asset_manager']['resolver_configs']['patterns'])) {
            $map = $config['asset_manager']['resolver_configs']['patterns'];
        }

        return new PatternResolver($map);
    }
}
