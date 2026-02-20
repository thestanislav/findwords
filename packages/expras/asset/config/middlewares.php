<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 1/30/2018
 * Time: 18:49
 */
return [
    'pre_pipe_dispatch_middleware' => [
        \ExprAs\Asset\MiddleWare\AssetInjectionMiddleware::class,
        //\ExprAs\Asset\MiddleWare\AssetManagerMiddleware::class
    ],
    'post_pipe_dispatch_middleware' => [
        //\AssetManager\Expressive\MiddleWare\AssetManagerMiddleware::class
    ]
];
