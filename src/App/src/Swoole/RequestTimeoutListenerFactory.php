<?php

declare(strict_types=1);

namespace App\Swoole;

use Psr\Container\ContainerInterface;

final class RequestTimeoutListenerFactory
{
    public function __invoke(ContainerInterface $container): RequestTimeoutListener
    {
        $config = $container->get('config');
        $timeoutMs = (int) ($config['mezzio-swoole']['request_timeout_ms']
            ?? RequestTimeoutListener::DEFAULT_TIMEOUT_MS);

        return new RequestTimeoutListener($timeoutMs);
    }
}
