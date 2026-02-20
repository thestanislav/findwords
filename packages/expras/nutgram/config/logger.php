<?php

use Monolog\Level;
use ExprAs\Nutgram\Entity\TelegramLogEntity;
use ExprAs\Nutgram\Service\TelegramContextProcessor;

/**
 * Nutgram/Telegram Logger Configuration
 * 
 * Dedicated logger for Telegram bot operations.
 * Logs webhook events, bot commands, errors, and user interactions.
 */
return [
    'log' => [
        /**
         * Nutgram Bot Logger
         * 
         * Logs all Telegram bot operations to database.
         * Captures update ID, chat ID, user ID, handler info, etc.
         */
        'nutgram.logger' => [
            'name' => 'nutgram',
            'handlers' => [
                // Log errors and warnings to database using Nutgram-specific handler
                'nutgram_doctrine' => [
                    'name' => 'custom',
                    'options' => [
                        'service' => \ExprAs\Nutgram\Handler\NutgramDoctrineHandler::class,
                        'level'  => Level::Error,
                    ],
                ],
                // Also log all levels to rotating file for debugging
                /*'debug_file' => [
                    'name' => 'rotating_file',
                    'options' => [
                        'path' => 'data/logs/telegram-bot.log',
                        'maxFiles' => 7,  // Keep 7 days
                        'level' => Level::Debug,
                    ],
                ],*/
            ],
            'processors' => [
                // Add Telegram-specific context (updateId, chatId, userId, handler, etc.)
                // Uses lazy loading to avoid circular dependency issues
                'telegramContext' => [
                    'name' => 'custom',
                    'options' => [
                        'service' => TelegramContextProcessor::class,
                    ],
                ],
            ],
            'metadata' => [
                'entity' => TelegramLogEntity::class,
                'description' => 'Telegram Bot Logger (Nutgram)',
                'module' => 'ExprAs\\Nutgram',
            ],
        ],
    ],
];
