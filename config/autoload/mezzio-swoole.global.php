<?php

declare(strict_types=1);

/**
 * Mezzio Swoole - Production defaults.
 * Override via SWOOLE_HOST, SWOOLE_PORT env vars (see env-overrides.php).
 */

use ExprAs\Doctrine\Swoole\EntityManagerClearListener;
use Laminas\Stdlib\ArrayUtils\MergeReplaceKey;
use Mezzio\Swoole\Event\RequestEvent;
use Mezzio\Swoole\Event\RequestHandlerRequestListener;
use Mezzio\Swoole\Event\StaticResourceRequestListener;

return [
    'dependencies' => [
        'invokables' => [
            \Mezzio\Swoole\Log\StdoutLogger::class,
        ],
    ],
    'mezzio-swoole' => [
        'enable_coroutine' => true,
        'swoole-http-server' => [
            // RequestEvent order: clear Doctrine EM first, then view placeholders, then pipeline
            'listeners' => [
                RequestEvent::class => new MergeReplaceKey([
                    EntityManagerClearListener::class,
                    StaticResourceRequestListener::class,
                    RequestHandlerRequestListener::class,
                ]),
            ],
            'host' => "0.0.0.0",
            'port' => 8002,
            'mode' => SWOOLE_PROCESS, // SWOOLE_BASE or SWOOLE_PROCESS;
            'options' => [
    
                // Set the SSL certificate and key paths for SSL support:
                //'ssl_cert_file' => 'path/to/ssl.crt',
                //'ssl_key_file' => 'path/to/ssl.key',
                // Whether or not the HTTP server should use coroutines;
                // enabled by default, and generally should not be disabled:
                'enable_coroutine' => true,

                // Overwrite the default location of the pid file;
                // required when you want to run multiple instances of your service in different ports:
                'pid_file' => 'data/mezzio-swoole-expras.pid',
            ],
            'static-files' => [
                'enable'        => true,
                'document-root' => realpath(dirname(__DIR__, 2) . '/public') ?: (getcwd() . '/public'),
            ],
            'logger' => [
                'logger-name' => \Mezzio\Swoole\Log\StdoutLogger::class,
            ],

            // Since 2.1.0: Set the process name prefix.
            // The master process will be named `{prefix}-master`,
            // worker processes will be named `{prefix}-worker-{id}`,
            // and task worker processes will be named `{prefix}-task-worker-{id}`
            'process-name' => 'findwords-expras',
        ],
    ],
];
