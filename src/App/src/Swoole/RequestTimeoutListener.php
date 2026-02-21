<?php

declare(strict_types=1);

namespace App\Swoole;

use Mezzio\Swoole\Event\RequestEvent;
use Swoole\Timer;

/**
 * Listener for RequestEvent that enforces a max execution time per request.
 * Schedules a timer; if the request is not completed in time, sends 408 Request Timeout.
 * The timer is cleared when RequestHandlerRequestListener finishes (via delegator).
 *
 * @see https://docs.mezzio.dev/mezzio-swoole/v4/events/
 */
final class RequestTimeoutListener
{
    /** Default timeout in milliseconds (30s, recommended for OpenSwoole) */
    public const DEFAULT_TIMEOUT_MS = 30000;

    public function __construct(
        private readonly int $timeoutMs = self::DEFAULT_TIMEOUT_MS
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if ($this->timeoutMs <= 0) {
            return;
        }

        $request  = $event->getRequest();
        $response = $event->getResponse();

        $timerId = Timer::after($this->timeoutMs, function () use ($request, $response): void {
            if (RequestTimeoutRegistry::isCancelled($request)) {
                return;
            }
            RequestTimeoutRegistry::setCancelled($request);
            $response->status(408);
            $response->end("Request timeout\n");
        });

        RequestTimeoutRegistry::register($request, is_int($timerId) ? $timerId : (int) $timerId);
    }
}
