<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/11/2017
 * Time: 15:31
 */

namespace ExprAs\Telegram;

use ExprAs\Uploadable\Middleware\ChunkedUploadMiddleware;
use ExprAs\Uploadable\Middleware\UploadableEntityInjectionMiddleware;

return [
    'middleware_pipeline' => [
        [
            'middleware' => ChunkedUploadMiddleware::class,
            'priority' => 4001
        ]
    ],
    'pre_pipe_dispatch_middleware' => [
        [
            'middleware' => UploadableEntityInjectionMiddleware::class,
            'priority' => 4000
        ]

    ]

];
