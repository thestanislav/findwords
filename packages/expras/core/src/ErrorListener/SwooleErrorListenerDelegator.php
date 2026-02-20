<?php

declare(strict_types=1);

namespace ExprAs\Core\ErrorListener;

use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Swoole\Log\AccessLogInterface;
use Psr\Container\ContainerInterface;

class SwooleErrorListenerDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): ErrorHandler
    {
        $errorHandler = $callback();
        if ($container->has(AccessLogInterface::class)) {
            $errorHandler->attachListener(
                new SwooleErrorListener($container->get(AccessLogInterface::class))
            );
        }
        return $errorHandler;
    }
}
