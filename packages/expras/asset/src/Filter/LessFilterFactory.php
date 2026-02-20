<?php

namespace ExprAs\Asset\Filter;

use Interop\Container\ContainerInterface;

class LessFilterFactory
{
    public function __invoke(ContainerInterface $container)
    {

        foreach ([
                     '/usr/local/bin/node',
                     '/usr/bin/node',
                     '/usr/bin/nodejs',
                     '/usr/sbin/node',
                 ] as $_node) {
            if (is_file($_node)) {
                $filter = new LessFilter(
                    $_node,
                    [realpath('./'), '/usr/local/lib/node_modules', '/usr/lib/node_modules', '/usr/local/lib/node_modules', '/usr/local/share/.config/yarn/global/node_modules']
                );
                return $filter;
            }
        }

        return null;
    }
}
