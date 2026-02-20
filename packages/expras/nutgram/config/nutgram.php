<?php

use ExprAs\Nutgram\Middleware\ChatEntityInjectorMiddleware;
use ExprAs\Nutgram\Middleware\UserEntityInjectorMiddleware;
use ExprAs\Nutgram\Entity\DefaultChat;
use ExprAs\Nutgram\Entity\DefaultUser;
use ExprAs\Nutgram\Middleware\UserMessageListenerMiddleware;

return [
    'nutgram' => [
        // The Telegram bot token, fetched from the environment
        'token' => getenv('TELEGRAM_TOKEN'),

        'secretToken' => getenv('NUTGRAM_SECRET_TOKEN') ?: md5(realpath(__DIR__)),

        // The middlewares to be executed on every request
        // Order is critical: UserEntityInjectorMiddleware must execute first
        // as other middlewares depend on the injected user entity
        'middlewares' => [
            [
                'middleware' => UserEntityInjectorMiddleware::class,
                'priority'   => PHP_INT_MAX, // Highest priority - executes first
            ],
            [
                'middleware' => ChatEntityInjectorMiddleware::class,
                'priority'   => PHP_INT_MAX - 1, // Second priority
            ],
            [
                'middleware' => UserMessageListenerMiddleware::class,
                'priority'   => PHP_INT_MAX - 2, // Third priority - depends on user entity
            ],
        ],

        // Default user entity
        'userEntity'  => DefaultUser::class,

        // Default chat entity
        'chatEntity'  => DefaultChat::class,

        // Handlers configuration - all handlers including commands and update listeners
        'handlers'    => [],
        'cacheConfig'       => [],
    ],
];
