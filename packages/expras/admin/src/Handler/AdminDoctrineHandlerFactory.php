<?php

namespace ExprAs\Admin\Handler;

use Doctrine\ORM\EntityManager;
use ExprAs\Admin\Entity\AdminRequestLogEntity;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for AdminDoctrineHandler
 */
class AdminDoctrineHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AdminDoctrineHandler
    {
        $entityManager = $container->get(EntityManager::class);
        
        return new AdminDoctrineHandler(
            entityManager: $entityManager,
            hydrator: null,
            entityName: $options['entityName'] ?? AdminRequestLogEntity::class,
            level: $options['level'] ?? \Monolog\Level::Info,
            bubble: $options['bubble'] ?? true
        );
    }
}

