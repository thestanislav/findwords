<?php

use Monolog\Level;
use ExprAs\Logger\Entity\ErrorLogEntity;

/**
 * Logger Configuration
 * 
 * Defines loggers for the ExprAs framework.
 * Each logger can have multiple handlers and processors.
 */
return [
    'log' => [
        /**
         * Default ExprAs Logger
         * 
         * Handles framework errors and exceptions via Mezzio ErrorHandler.
         * Logs to file by default, can be configured to use Doctrine.
         */
        'expras_logger' => [
            'name' => 'expras_logger',
            'handlers' => [
                // Stream handler - logs to file
                'stream' => [
                    'name' => 'stream',
                    'options' => [
                        'stream' => 'data/logs/expras-error.log',
                        'level' => Level::Warning,
                    ],
                ],
                
                // Error-specific Doctrine handler - uncomment to log to database
               'error_doctrine' => [
                     'name' => 'custom',
                     'options' => [
                         'service' => \ExprAs\Logger\LogHandler\ErrorDoctrineHandler::class,
                     ],
                ],
            ],
            'processors' => [
                // Request data processor - adds request context
                'requestData' => [
                    'name' => 'requestData',
                    'priority' => -1,
                ],
            ],
            'metadata' => [
                'entity' => ErrorLogEntity::class,
                'description' => 'ExprAs Framework Error Logger',
                'module' => 'ExprAs\\Logger',
            ],
        ],
    ],
];
