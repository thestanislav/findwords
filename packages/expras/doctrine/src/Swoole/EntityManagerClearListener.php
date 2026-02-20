<?php

declare(strict_types=1);

namespace ExprAs\Doctrine\Swoole;

use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Swoole\Event\RequestEvent;

/**
 * Clears the Doctrine EntityManager at the start of each Swoole request.
 * Runs on RequestEvent (before the middleware pipeline) so each request
 * gets a clean identity map and unit of work.
 * 
 * Also closes the database connection to prevent "unbuffered queries" errors
 * when Swoole coroutines are enabled, ensuring each request gets a fresh connection.
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
        $this->closeConnection();
    }

    /**
     * Closes the database connection to ensure a clean state for each request.
     * This prevents "unbuffered queries" errors when multiple Swoole coroutines
     * might otherwise share the same connection.
     */
    private function closeConnection(): void
    {
        $connection = $this->entityManager->getConnection();
        
        if ($connection->isConnected()) {
            $connection->close();
        }
    }
}
