<?php

declare(strict_types=1);

namespace ExprAs\Doctrine\Service;

use Closure;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Utility factory for creating EntityManager wrapper instances.
 * 
 * Primary creation is handled by DoctrineEntityManagerDelegator.
 * This factory is provided for manual/programmatic creation scenarios.
 */
final class EntityManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): EntityManager
    {
        return new EntityManager($container->get(EntityManager::class));
    }
}