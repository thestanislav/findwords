<?php


use ExprAs\Admin\Middleware\AdminAuthenticationMiddleware;
use ExprAs\Admin\Middleware\AdminIsGrantedMiddleware;
use ExprAs\Admin\Middleware\AdminApiLoggingMiddleware;

return [
    'post_pipe_routing_middleware' => [
        [
            'priority' => 5000,
            'middleware' => AdminAuthenticationMiddleware::class,
            'path' => '/.admin'
        ],

    ],
    'pre_pipe_dispatch_middleware' => [
        /*[
            'middleware' => AdminIsGrantedMiddleware::class,
            'path' => '/.admin'
        ]*/
        [
            'priority' => -5000,
            'middleware' => AdminApiLoggingMiddleware::class,
            'path' => '/.admin'  // Only log API requests
        ]
    ]
];
