<?php

declare(strict_types=1);

namespace ExprAs\Doctrine\Service;

use Closure;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Cache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Swoole\Coroutine as Co;

/**
 * Coroutine-aware EntityManager wrapper for Swoole.
 * Each coroutine receives its own EntityManager instance stored in Co::getContext().
 * The EntityManager is automatically closed when the coroutine ends via Co::defer().
 * 
 * When running outside of a coroutine (e.g., CLI commands), a default shared EntityManager is used.
 */
final class EntityManager implements EntityManagerInterface
{
    private ?EntityManagerInterface $defaultEm = null;

    public function __construct(private Closure $emCreatorFn)
    {
    }

    public function getWrappedEm(): EntityManagerInterface
    {
        $context = $this->getContext();
        
        if ($context === null) {
            return $this->getDefaultEm();
        }

        $key = self::class;
        if (! isset($context[$key]) || ! $context[$key] instanceof EntityManagerInterface) {
            $em = ($this->emCreatorFn)();
            $context[$key] = $em;

            // Capture $em directly - avoids unreliable context re-lookup inside defer.
            // The DBAL/PDO chain has no cycles, so close() immediately frees the MySQL
            // connection. The EM↔UnitOfWork cycle is GC'd later but that doesn't matter
            // since the MySQL connection is already released.
            // NOTE: gc_collect_cycles() must NOT be called here - in Swoole it would
            // destroy objects from other active coroutines in the same worker.
            Co::defer(static function () use ($em) {
                try {
                    $connection = $em->getConnection();
                    if ($connection->isConnected()) {
                        $connection->close();
                    }
                } catch (\Throwable) {
                }
                try {
                    if ($em->isOpen()) {
                        $em->close();
                    }
                } catch (\Throwable) {
                }
            });
        }
        return $context[$key];
    }

    private function getDefaultEm(): EntityManagerInterface
    {
        if ($this->defaultEm === null || ! $this->defaultEm->isOpen()) {
            $this->defaultEm = ($this->emCreatorFn)();
        }
        return $this->defaultEm;
    }

    /**
     * Close the default (non-coroutine) EntityManager and its connection if it exists.
     * Called by WorkerStopListener to clean up when worker stops.
     */
    public function closeDefaultConnection(): void
    {
        if ($this->defaultEm === null) {
            return;
        }

        try {
            $connection = $this->defaultEm->getConnection();
            if ($connection->isConnected()) {
                $connection->close();
            }
        } finally {
            if ($this->defaultEm->isOpen()) {
                $this->defaultEm->close();
            }
            $this->defaultEm = null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadata($className): ClassMetadata
    {
        return $this->getWrappedEm()->getClassMetadata($className);
    }

    public function getUnitOfWork(): UnitOfWork
    {
        return $this->getWrappedEm()->getUnitOfWork();
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository($className): EntityRepository
    {
        return $this->getWrappedEm()->getRepository($className);
    }

    public function getConnection(): Connection
    {
        return $this->getWrappedEm()->getConnection();
    }

    public function getCache(): ?Cache
    {
        return $this->getWrappedEm()->getCache();
    }

    public function getExpressionBuilder(): Expr
    {
        return $this->getWrappedEm()->getExpressionBuilder();
    }

    public function beginTransaction(): void
    {
        $this->getWrappedEm()->beginTransaction();
    }

    /**
     * @deprecated Use wrapInTransaction() instead
     */
    public function transactional($func)
    {
        return $this->getWrappedEm()->transactional($func);
    }

    public function wrapInTransaction(callable $func): mixed
    {
        return $this->getWrappedEm()->wrapInTransaction($func);
    }

    public function commit(): void
    {
        $this->getWrappedEm()->commit();
    }

    public function rollback(): void
    {
        $this->getWrappedEm()->rollback();
    }

    public function createQuery($dql = ''): Query
    {
        return $this->getWrappedEm()->createQuery($dql);
    }

    /**
     * @deprecated
     */
    public function createNamedQuery($name): Query
    {
        return $this->getWrappedEm()->createNamedQuery($name);
    }

    public function createNativeQuery($sql, ResultSetMapping $rsm): NativeQuery
    {
        return $this->getWrappedEm()->createNativeQuery($sql, $rsm);
    }

    /**
     * @deprecated
     */
    public function createNamedNativeQuery($name): NativeQuery
    {
        return $this->getWrappedEm()->createNamedNativeQuery($name);
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->getWrappedEm()->createQueryBuilder();
    }

    /**
     * {@inheritDoc}
     */
    public function getReference($entityName, $id): ?object
    {
        return $this->getWrappedEm()->getReference($entityName, $id);
    }

    /**
     * @deprecated
     */
    public function getPartialReference($entityName, $identifier): ?object
    {
        return $this->getWrappedEm()->getPartialReference($entityName, $identifier);
    }

    public function close(): void
    {
        $this->getWrappedEm()->close();
    }

    /**
     * @deprecated
     */
    public function copy($entity, $deep = false)
    {
        return $this->getWrappedEm()->copy($entity, $deep);
    }

    /**
     * {@inheritDoc}
     */
    public function lock($entity, $lockMode, $lockVersion = null): void
    {
        $this->getWrappedEm()->lock($entity, $lockMode, $lockVersion);
    }

    public function getEventManager(): EventManager
    {
        return $this->getWrappedEm()->getEventManager();
    }

    public function getConfiguration(): Configuration
    {
        return $this->getWrappedEm()->getConfiguration();
    }

    public function isOpen(): bool
    {
        return $this->getWrappedEm()->isOpen();
    }

    /**
     * @deprecated
     */
    public function getHydrator($hydrationMode): AbstractHydrator
    {
        return $this->getWrappedEm()->getHydrator($hydrationMode);
    }

    public function newHydrator($hydrationMode): AbstractHydrator
    {
        return $this->getWrappedEm()->newHydrator($hydrationMode);
    }

    public function getProxyFactory(): ProxyFactory
    {
        return $this->getWrappedEm()->getProxyFactory();
    }

    public function getFilters(): FilterCollection
    {
        return $this->getWrappedEm()->getFilters();
    }

    public function isFiltersStateClean(): bool
    {
        return $this->getWrappedEm()->isFiltersStateClean();
    }

    public function hasFilters(): bool
    {
        return $this->getWrappedEm()->hasFilters();
    }

    /**
     * {@inheritDoc}
     */
    public function find($className, $id, $lockMode = null, $lockVersion = null): ?object
    {
        return $this->getWrappedEm()->find($className, $id, $lockMode, $lockVersion);
    }

    public function persist($object): void
    {
        $this->getWrappedEm()->persist($object);
    }

    public function remove($object): void
    {
        $this->getWrappedEm()->remove($object);
    }

    public function clear(): void
    {
        $this->getWrappedEm()->clear();
    }

    public function detach($object): void
    {
        $this->getWrappedEm()->detach($object);
    }

    public function refresh($object, $lockMode = null): void
    {
        $this->getWrappedEm()->refresh($object, $lockMode);
    }

    public function flush(): void
    {
        $this->getWrappedEm()->flush();
    }

    public function getMetadataFactory(): ClassMetadataFactory
    {
        return $this->getWrappedEm()->getMetadataFactory();
    }

    public function initializeObject($obj): void
    {
        $this->getWrappedEm()->initializeObject($obj);
    }

    public function contains($object): bool
    {
        return $this->getWrappedEm()->contains($object);
    }

    /**
     * Reopen the EntityManager for current context.
     * If the EM is open, clears it. If closed, removes it from context so a new one will be created.
     */
    public function reopen(): void
    {
        $context = $this->getContext();
        $em = $this->getWrappedEm();
        
        if ($em->isOpen()) {
            $em->clear();
        } elseif ($context !== null) {
            unset($context[self::class]);
        } else {
            $this->defaultEm = null;
        }
    }

    private function getContext(): ?Co\Context
    {
        $cid = Co::getCid();
        if ($cid < 1) {
            return null;
        }
        
        $context = Co::getContext($cid);
        if (! $context instanceof Co\Context) {
            return null;
        }
        return $context;
    }
}
