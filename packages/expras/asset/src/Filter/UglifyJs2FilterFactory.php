<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/22/2017
 * Time: 22:06
 */

namespace ExprAs\Asset\Filter;

use Assetic\Filter\UglifyJs2Filter;
use Psr\Container\ContainerInterface;

class UglifyJs2FilterFactory
{
    public function __invoke(ContainerInterface $container)
    {

        foreach ([
                     '/usr/local/bin/uglifyjs',
                     '/usr/local/sbin/uglifyjs',
                     '/usr/bin/uglifyjs',
                     '/usr/sbin/uglifyjs',
                 ] as $_path) {
            if (is_file($_path)) {
                $filter = new UglifyJs2Filter($_path);
                $filter->setCompress(true);
                $filter->setMangle(true);
                return $filter;
            }
        }

        return null;
    }
}
