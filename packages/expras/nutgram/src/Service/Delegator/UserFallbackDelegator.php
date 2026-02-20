<?php

namespace ExprAs\Nutgram\Service\Delegator;

use SergiX44\Nutgram\Nutgram;
use Psr\Container\ContainerInterface;
use ExprAs\Nutgram\Entity\User;

class UserFallbackDelegator
{
    public function __invoke(ContainerInterface $container, $name, callable $callback, ?array $options = null): Nutgram
    {
        $bot = $callback($container, $name, $options);

        // Only proceed if the bot is actually a Nutgram instance
        if (!$bot instanceof Nutgram) {
            return $bot;
        }

        // Add a fallback handler for text messages that checks user waiting state
        $bot->fallback(function (Nutgram $bot) use ($container) {
            try {
                $this->handleUserFallback($bot, $container);
            } catch (\Throwable $throwable) {
                if ($container->has('expras_error_logger')) {
                    $logger = $container->get('expras_error_logger');
                    $logger->error(sprintf('Error handling user fallback: %s', $throwable->getMessage()));
                }
            }
        });

        return $bot;
    }

    private function handleUserFallback(Nutgram $bot, ContainerInterface $container): void
    {
        if (!$bot->message()) {
            return;
        }

        // Get current user from context
        $user = $this->getCurrentUser($bot, $container);
        if (!$user || !$user->isWaitingForInput()) {
            return; // No waiting state, let other handlers process
        }

        $handlerInfo = $user->getWaitingForHandler();
        $context = $user->getWaitingContext();

        // Parse handler info
        if (str_contains((string) $handlerInfo, '::')) {
            [$handlerClass, $method] = explode('::', (string) $handlerInfo);
        } else {
            $handlerClass = $handlerInfo;
            $method = 'handleTextInput';
        }

        // Get handler instance from container
        if ($container->has($handlerClass)) {
            $handler = $container->get($handlerClass);
        } else {
            $handlerMap = $bot->get('expras.nutgram.handlerMap');
            $handlerFound = false;
            foreach ($handlerMap as $key => $handler) {
                if ($key === $handlerClass) {
                    $handlerFound = true;
                    $handler = $handlerMap[$key];
                    break;
                } elseif ($handler::class === $handlerClass) {
                    $handlerFound = true;
                    $handler = $handlerMap[$key];
                    break;
                }
            }
            if (!$handlerFound) {
                $bot->sendMessage('An error occurred while processing your input. Please try again from /start');
                $this->clearWaitingState($user, $container);
                return;
            }
        }


        // Call the handler method
        if (method_exists($handler, $method)) {
            try {
                $handler->$method($bot, $user, $context);
            } catch (\Throwable $e) {
                $bot->sendMessage('An error occurred while processing your input. Please try again from /start');
                $this->clearWaitingState($user, $container);

                // Log the error
                if ($container->has('expras_logger')) {
                    $logger = $container->get('expras_logger');
                    $logger->error('Error in user fallback handler', [
                        'handler' => $handlerClass,
                        'method' => $method,
                        'user_id' => $user->getId(),
                        'error' => $e->getMessage(),
                        'line' => $e->getLine(),
                        'file' => $e->getFile(),
                        'trace' => $e->getTraceAsString(),
                        'context' => $context,
                    ]);
                }
            }
        } else {
            $bot->sendMessage('Handler method not found. Please try again.');
            $this->clearWaitingState($user, $container);
        }
    }

    private function getCurrentUser(Nutgram $bot, ContainerInterface $container): ?User
    {
        // Try to get user from bot context first
        if (method_exists($bot, 'get') && $bot->get('expras.nutgram.user')) {
            return $bot->get('expras.nutgram.user');
        }

        // Try to get user from message
        if ($bot->message() && $bot->message()->from) {
            $userId = $bot->message()->from->id;
            $userEntityClass = $container->get('config')['nutgram']['userEntity'];
            // Get user repository and find user
            if ($container->has('doctrine.entitymanager.orm_default')) {
                $em = $container->get('doctrine.entitymanager.orm_default');
                $userRepository = $em->getRepository($userEntityClass);
                return $userRepository->find($userId);
            }
        }

        return null;
    }

    private function clearWaitingState(User $user, ContainerInterface $container): void
    {
        $user->clearWaitingState();

        // Save to database if entity manager is available
        if ($container->has('doctrine.entitymanager.orm_default')) {
            $em = $container->get('doctrine.entitymanager.orm_default');
            $em->persist($user);
            $em->flush();
        }
    }
}
