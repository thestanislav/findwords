<?php

namespace ExprAs\Nutgram\Handler;

use Doctrine\ORM\EntityManager;
use ExprAs\Nutgram\Entity\TelegramLogEntity;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for NutgramDoctrineHandler
 */
class NutgramDoctrineHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): NutgramDoctrineHandler
    {
        $entityManager = $container->get(EntityManager::class);
        
        return new NutgramDoctrineHandler(
            entityManager: $entityManager,
            hydrator: null,
            entityName: $options['entityName'] ?? TelegramLogEntity::class,
            level: $options['level'] ?? \Monolog\Level::Debug,
            bubble: $options['bubble'] ?? true
        );
    }
}

