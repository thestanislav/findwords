<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 06.04.2014
 * Time: 14:33
 */

namespace ExprAs\Asset\Service;

use Interop\Container\ContainerInterface;
use Laminas\Stdlib\ArrayUtils;

class RouteAssetInjectorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $routes = [];
        $config = $container->get('config');

        if (!empty($config['asset_manager']['routes'])) {
            $routes = $config['asset_manager']['routes'];
        }

        if (!empty($config['as_asset']['routes'])) {
            $routes = ArrayUtils::merge($routes, $config['as_asset']['routes']);
        }

        return new RouteAssetInjector($routes);
    }

}
