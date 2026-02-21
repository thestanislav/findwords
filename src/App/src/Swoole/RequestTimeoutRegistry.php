<?php

declare(strict_types=1);

namespace App\Swoole;

use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Timer;

/**
 * Stores request timeout timer state by request object id to avoid
 * dynamic properties on Swoole\Http\Request (deprecated in PHP 8.2+).
 */
final class RequestTimeoutRegistry
{
    /** @var array<int, array{timerId: int, cancelled: bool}> */
    private static array $byRequest = [];

    public static function register(SwooleHttpRequest $request, int $timerId): void
    {
        self::$byRequest[spl_object_id($request)] = [
            'timerId'   => $timerId,
            'cancelled' => false,
        ];
    }

    public static function isCancelled(SwooleHttpRequest $request): bool
    {
        $id = spl_object_id($request);
        return isset(self::$byRequest[$id]) && self::$byRequest[$id]['cancelled'];
    }

    public static function cancelAndClear(SwooleHttpRequest $request): void
    {
        $id = spl_object_id($request);
        if (!isset(self::$byRequest[$id])) {
            return;
        }
        self::$byRequest[$id]['cancelled'] = true;
        Timer::clear(self::$byRequest[$id]['timerId']);
        unset(self::$byRequest[$id]);
    }

    public static function setCancelled(SwooleHttpRequest $request): void
    {
        $id = spl_object_id($request);
        if (isset(self::$byRequest[$id])) {
            self::$byRequest[$id]['cancelled'] = true;
        }
    }
}
