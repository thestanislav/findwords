<?php

declare(strict_types=1);

namespace ExprAs\Doctrine\Container;

use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use ExprAs\Doctrine\Service\EntityManager;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Delegator that wraps the Doctrine EntityManager with a coroutine-aware wrapper.
 * Each Swoole coroutine will receive its own EntityManager instance, ensuring
 * isolation and preventing "unbuffered queries" errors from shared connections.
 */
final class DoctrineEntityManagerDelegator implements DelegatorFactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        ?array $options = null
    ): EntityManager {
        $emCreatorFn = static function () use ($container): DoctrineEntityManager {
            // build() creates a new connection per coroutine to avoid "unbuffered queries" errors
            $connection = $container->build('doctrine.connection.orm_default');
            // get() is fine for config as it's immutable/read-only
            $config = $container->get('doctrine.configuration.orm_default');

            return new DoctrineEntityManager($connection, $config);
        };

        return new EntityManager($emCreatorFn);
    }
}
