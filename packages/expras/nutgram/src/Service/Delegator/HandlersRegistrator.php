<?php

namespace ExprAs\Nutgram\Service\Delegator;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command as NutgramCommand;
use Psr\Container\ContainerInterface;

class HandlersRegistrator
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

        // Register handlers and build map
        $handlerMap = [];
        foreach ($handlers as $handler) {
            if (is_string($handler) && $container->has($handler)) {
                $handlerInstance = $container->get($handler);
            } elseif (is_string($handler) && class_exists($handler)) {
                $handlerInstance = new $handler();
            } else {
                $handlerInstance = $handler;
            }

            // If handler implements or extends Nutgram Command, register it
            if ($handlerInstance instanceof NutgramCommand) {
                $command = $bot->registerCommand($handlerInstance);
                $handlerMap[strtolower((string) $command->getName())] = $command;
            } else {
                $handlerMap[] = $handlerInstance;
            }
        }

        // Expose handler map for other delegators
        if (!empty($handlerMap)) {
            $bot->set('expras.nutgram.handlerMap', $handlerMap);
        }

        return $bot;
    }
}