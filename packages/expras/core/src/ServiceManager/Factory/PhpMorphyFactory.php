<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 2/5/2018
 * Time: 10:47
 */

namespace ExprAs\Core\ServiceManager\Factory;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PhpMorphyFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('Configuration');
        $config = $config['php_morphy'];
        return new \phpMorphy($config['dictDir'], $config['defaultLang']);
    }
}
