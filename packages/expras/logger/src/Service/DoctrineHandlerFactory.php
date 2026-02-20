<?php

namespace ExprAs\Logger\Service;

use ExprAs\Logger\LogHandler\DoctrineHandler;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Laminas\Hydrator\DoctrineObject;

class DoctrineHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): DoctrineHandler
    {
        $entityManager = $container->get(EntityManager::class);
        $hydrator = new DoctrineObject($entityManager);
        
        return new DoctrineHandler(
            entityManager: $entityManager,
            hydrator: $hydrator,
            entityName: $options['entityName'] ?? null,
            level: $options['level'] ?? \Monolog\Level::Debug,
            bubble: $options['bubble'] ?? true
        );
    }
}
