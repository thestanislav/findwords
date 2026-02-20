<?php

namespace ExprAs\Logger\LogHandler;

use Doctrine\ORM\EntityManager;
use ExprAs\Logger\Entity\ErrorLogEntity;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for ErrorDoctrineHandler
 */
class ErrorDoctrineHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ErrorDoctrineHandler
    {
        $entityManager = $container->get(EntityManager::class);
        
        return new ErrorDoctrineHandler(
            entityManager: $entityManager,
            hydrator: null,
            entityName: $options['entityName'] ?? ErrorLogEntity::class,
            level: $options['level'] ?? \Monolog\Level::Debug,
            bubble: $options['bubble'] ?? true
        );
    }
}

