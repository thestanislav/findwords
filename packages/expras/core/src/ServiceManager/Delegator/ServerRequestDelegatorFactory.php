<?php

declare(strict_types=1);

namespace ExprAs\Core\ServiceManager\Delegator;

use ExprAs\Core\Http\CurrentRequestHolder;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Delegator for ServerRequestInterface. When the real service is the Swoole
 * 1-arg factory, wraps it so that:
 * - Called with 1 arg (SwooleHttpRequest): builds PSR-7 request, stores in holder, returns it.
 * - Called with 0 args: returns the stored request (for the logger).
 * When the real service is 0-arg (standard Mezzio), returns it unchanged.
 */
final class ServerRequestDelegatorFactory implements DelegatorFactoryInterface
{
    public function __invoke(ContainerInterface $container, $name, callable $callback, ?array $options = null): callable
    {
        $real = $callback();

        if (! is_callable($real) || ! $this->callableRequiresExactlyOneArgument($real)) {
            return $real;
        }

        $holder = $container->get(CurrentRequestHolder::class);

        return static function (mixed $swooleRequest = null) use ($real, $holder): ?ServerRequestInterface {
            if (func_num_args() === 1) {
                $request = $real($swooleRequest);
                $holder->set($request);
                return $request;
            }
            return $holder->get();
        };
    }

    private function callableRequiresExactlyOneArgument(callable $callable): bool
    {
        if (is_array($callable)) {
            $ref = new \ReflectionMethod($callable[0], $callable[1]);
        } elseif (is_object($callable) && ! $callable instanceof \Closure) {
            $ref = new \ReflectionMethod($callable, '__invoke');
        } else {
            $ref = new \ReflectionFunction($callable);
        }
        $required = 0;
        foreach ($ref->getParameters() as $p) {
            if (! $p->isOptional()) {
                $required++;
            }
        }
        return $required === 1;
    }
}
