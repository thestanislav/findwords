<?php

declare(strict_types=1);

namespace ExprAs\Doctrine\Swoole;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Swoole\Event\RequestEvent;

/**
 * Clears the Doctrine EntityManager at the start of each Swoole request.
 * Runs on RequestEvent (before the middleware pipeline) so each request
 * gets a clean identity map and unit of work.
 * 
 * Also ensures the database connection is valid, closing stale connections
 * so they will be automatically reconnected on the next query.
 */
final class EntityManagerClearListener
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $this->entityManager->clear();
        $this->ensureConnectionValid();
    }

    /**
     * Ensures the database connection is valid.
     * If the connection is stale/lost, close it so Doctrine will reconnect.
     */
    private function ensureConnectionValid(): void
    {
        $connection = $this->entityManager->getConnection();
        
        if (!$connection->isConnected()) {
            return;
        }

        try {
            $connection->executeQuery($connection->getDatabasePlatform()->getDummySelectSQL());
        } catch (Exception) {
            $connection->close();
        }
    }
}
