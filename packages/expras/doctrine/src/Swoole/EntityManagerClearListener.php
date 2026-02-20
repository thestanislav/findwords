<?php

declare(strict_types=1);

namespace ExprAs\Doctrine\Swoole;

use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Swoole\Event\RequestEvent;

/**
 * Clears the Doctrine EntityManager at the start of each Swoole request.
 * Runs on RequestEvent (before the middleware pipeline) so each request
 * gets a clean identity map and unit of work.
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
    }
}
