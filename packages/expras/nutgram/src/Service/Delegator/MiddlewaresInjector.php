<?php

namespace ExprAs\Nutgram\Service\Delegator;

use Psr\Container\ContainerInterface;
use Laminas\Stdlib\SplPriorityQueue;
use SergiX44\Nutgram\Nutgram;

class MiddlewaresInjector
{
    public function __invoke(ContainerInterface $container, $name, callable $callback, ?array $options = null): Nutgram
    {
        $bot = $callback($container, $name, $options);

        // Only proceed if the bot is actually a Nutgram instance
        if (!$bot instanceof Nutgram) {
            return $bot;
        }

        $config = $container->get('config') ?? [];
        $nutgramConfig = $config['nutgram'] ?? [];

        // $serial is used to ensure that items of the same priority are enqueued
        // in the order in which they are inserted.
        $serial = PHP_INT_MAX;
        $middlewares = $nutgramConfig['middlewares'] ?? [];
        $middlewareIndex = 0;
        $queue = array_reduce(
            $middlewares,
            function ($queue, $item) use (&$serial, &$middlewareIndex) {

                if (is_string($item)) {
                    $item = ['middleware' => $item];
                }
                $priority = isset($item['priority']) && is_int($item['priority'])
                    ? $item['priority']
                    : $middlewareIndex;
                $queue->insert($item, [$priority, $serial]);
                $serial -= 1;
                $middlewareIndex++;
                return $queue;
            },
            new SplPriorityQueue()
        );

        foreach ($queue as $spec) {
            if (is_string($spec['middleware']) && $container->has($spec['middleware'])) {
                $bot->middlewares($container->get($spec['middleware']));
            }else {
                $bot->middlewares($spec['middleware']);
            }
        }

        return $bot;
    }
}