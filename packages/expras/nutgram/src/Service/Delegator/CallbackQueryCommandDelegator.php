<?php

namespace ExprAs\Nutgram\Service\Delegator;

use Psr\Container\ContainerInterface;
use SergiX44\Nutgram\Nutgram;

class CallbackQueryCommandDelegator
{
    public function __invoke(ContainerInterface $container, $requestedName, callable $callback)
    {
        $bot = $callback($container, $requestedName);

        if (!$bot instanceof Nutgram) {
            return $bot;
        }

         /** @var array<string, \SergiX44\Nutgram\Handlers\Type\Command> $commandMap */
        $commandMap = $bot->get('expras.nutgram.handlerMap');
        if (empty($commandMap)) {
            return $bot;
        }

        $bot->onCallbackQueryData('^/(?P<cmd>[A-Za-z0-9_]+)(?:\s+(?P<params>.*))?$', function (Nutgram $bot, ?string $cmd = null, ?string $params = null) use ($commandMap, $container) {
            if ($cmd === null) {
                return null;
            }

            $cmd = strtolower($cmd);
            if (!isset($commandMap[$cmd])) {
                return null;
            }

            $json = $params !== null ? json_decode($params, true) : null;
            $parameters = [
                '__rawParameters' => $json,
            ];
            if ($params !== null && json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $parameters = array_merge($parameters, $json);
            } elseif ($params !== null) {
                parse_str($params, $parts);
                $parameters = array_merge($parameters, $parts ?: []);
            }

            $command = $commandMap[$cmd];
            $command->setParameters(...$parameters);
            try {
                return $command->__invoke($bot);
            } catch (\Throwable $e) {
                // Log the error if logger is available
                if ($container->has('expras_error_logger')) {
                    $logger = $container->get('expras_error_logger');
                    $logger->err('Callback query command failed: ' . $e->getMessage(), [
                        'command' => $cmd,
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
                // Re-throw to let Nutgram handle it
                throw $e;
            }
        });

        return $bot;
    }
}
