<?php

declare(strict_types=1);

use App\Middleware\NavigationStateResetMiddleware;
use App\Middleware\PageCacheHeaders;
use App\Middleware\PageCacheMiddleware;
use App\Middleware\PluralRuleSetter;
use Mimmi20\Mezzio\Navigation\NavigationMiddleware;

return [
    'middleware_pipeline' => [
        [
            'middleware' => PluralRuleSetter::class
        ],
        [
            'middleware' => NavigationMiddleware::class,
        ],
        [
            'middleware' => PageCacheHeaders::class,
        ]
    ],
    'post_pipe_routing_middleware' => [
        NavigationStateResetMiddleware::class,
    ],
    'pre_pipe_dispatch_middleware' => [
        [
            'middleware' => PageCacheMiddleware::class,
        ]
    ]
];