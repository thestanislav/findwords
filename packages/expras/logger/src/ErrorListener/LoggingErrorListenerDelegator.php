<?php

namespace ExprAs\Logger\ErrorListener;

use Laminas\Stratigility\Middleware\ErrorHandler;
use Psr\Container\ContainerInterface;

class LoggingErrorListenerDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): ErrorHandler
    {
        $errorHandler = $callback();
        $errorHandler->attachListener(
            new LoggingErrorListener($container->get('expras_logger'))
        );
        return $errorHandler;
    }
}
