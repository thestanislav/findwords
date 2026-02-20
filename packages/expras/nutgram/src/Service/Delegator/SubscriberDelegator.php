<?php

namespace ExprAs\Nutgram\Service\Delegator;

use SergiX44\Nutgram\Nutgram;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use ExprAs\Nutgram\SubscriberInterface;

class SubscriberDelegator
{
    public function __invoke(ContainerInterface $container, $name, callable $callback, ?array $options = null): Nutgram
    {
        $bot = $callback($container, $name, $options);

        // Only proceed if the bot is actually a Nutgram instance
        if (!$bot instanceof Nutgram) {
            return $bot;
        }

        $config = $container->get('config')['nutgram'] ?? [];
        $handlers = $config['handlers'] ?? [];

        // Get handler map from bot if available
        $handlerMap = $bot->get('expras.nutgram.handlerMap') ?? [];

        // Iterate over handlers and call subscribeToEvents on those implementing SubscriberInterface
        foreach ($handlers as $handler) {
            if (is_string($handler) && $container->has($handler)) {
                $handlerInstance = $container->get($handler);
            } elseif (is_string($handler) && class_exists($handler)) {
                $handlerInstance = new $handler();
            } else {
                $handlerInstance = $handler;
            }
        }

        // Also check handler map for subscribers
        foreach ($handlerMap as $handler) {
            if ($handler instanceof SubscriberInterface) {
                $handler->subscribeToEvents($bot);
            }
        }

        return $bot;
    }
}
