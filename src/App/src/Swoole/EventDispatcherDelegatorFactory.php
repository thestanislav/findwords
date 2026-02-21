<?php

declare(strict_types=1);

namespace App\Swoole;

use Mezzio\Swoole\Event\EventDispatcherInterface;
use Mezzio\Swoole\Event\RequestEvent;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;

/**
 * Delegator for the Swoole EventDispatcher that clears the request timeout
 * timer after dispatch(RequestEvent) completes, so the timer is always cleared
 * when the request handler finishes.
 */
final class EventDispatcherDelegatorFactory
{
    public function __invoke(ContainerInterface $container, string $requestedName, callable $callback): EventDispatcherInterface
    {
        $inner = $callback();

        return new class ($inner) implements EventDispatcherInterface, PsrEventDispatcherInterface {
            public function __construct(
                private readonly PsrEventDispatcherInterface $inner
            ) {
            }

            public function dispatch(object $event): object
            {
                try {
                    return $this->inner->dispatch($event);
                } finally {
                    if ($event instanceof RequestEvent) {
                        RequestTimeoutRegistry::cancelAndClear($event->getRequest());
                    }
                }
            }
        };
    }
}
