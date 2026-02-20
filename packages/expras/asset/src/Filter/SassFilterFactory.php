<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 1/28/2018
 * Time: 17:17
 */

namespace ExprAs\Asset\Service;

use ExprAs\Asset\Filter\SassFilter;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SassFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new SassFilter($container->get('config')['sass_filter']['try_executables']);
    }
}
