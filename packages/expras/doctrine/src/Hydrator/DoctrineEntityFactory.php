<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 07.12.12
 * Time: 11:36
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\Doctrine\Hydrator;

use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DoctrineEntityFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new DoctrineEntity($container->get(EntityManager::class));
    }
}
