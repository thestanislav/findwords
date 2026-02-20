<?php

use Monolog\Level;
use ExprAs\Admin\Entity\AdminRequestLogEntity;
use ExprAs\Admin\Service\AdminContextProcessor;

/**
 * Admin Logger Configuration
 * 
 * Dedicated logger for admin REST API actions.
 * Logs user actions, resource operations, and API requests.
 */
return [
    'log' => [
        /**
         * Admin API Logger
         * 
         * Logs all admin API requests with user, resource, action details.
         * Useful for audit trails and security monitoring.
         */
        'admin.api.logger' => [
            'name' => 'admin.api',
            'handlers' => [
                // Log info and above to database
                'doctrine' => [
                    'name' => 'custom',
                    'options' => [
                        'service' => \ExprAs\Admin\Handler\AdminDoctrineHandler::class,
                    ],
                ],
                // Optional: Log all to rotating file
                // 'debug_file' => [
                //     'name' => 'rotating_file',
                //     'options' => [
                //         'path' => 'data/logs/admin-api.log',
                //         'maxFiles' => 30,
                //         'level' => Level::Debug,
                //     ],
                // ],
            ],
            'processors' => [
                // Context is added by AdminApiLoggingMiddleware
                // No additional processors needed for automatic logging
            ],
            'metadata' => [
                'entity' => AdminRequestLogEntity::class,
                'description' => 'Admin API Request Logger',
                'module' => 'ExprAs\\Admin',
            ],
        ],
    ],
];

