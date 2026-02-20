<?php

declare(strict_types=1);

namespace ExprAs\Doctrine\Swoole;

use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EntityManagerClearListenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): EntityManagerClearListener
    {
        $em = $container->get(EntityManager::class);

        return new EntityManagerClearListener($em);
    }
}
