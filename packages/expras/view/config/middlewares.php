<?php

declare(strict_types=1);

return [
    'pre_pipe_routing_middleware' => [
        \ExprAs\View\Middleware\ResetViewHelperStateMiddleware::class,
    ],
    'pre_pipe_dispatch_middleware' => [
        \ExprAs\View\Helper\ProfiledPaginationControlMiddleware::class,
        \ExprAs\View\Helper\RouteMatchFactory::class,
    ],
    'post_pipe_dispatch_middleware' => [],
];
