<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 1/30/2018
 * Time: 18:36
 */


use ExprAs\User\Middleware\UserActivityTrackerMiddleware;
use ExprAs\User\OwnerAware\OwnerListenerInjectorMiddleware;
use ExprAs\User\View\Helper\IdentityFactoryMiddleware;

return [
    'post_pipe_routing_middleware' => [
        OwnerListenerInjectorMiddleware::class,
        UserActivityTrackerMiddleware::class
    ],
    'pre_pipe_dispatch_middleware' => [
        [
            'priority'   => -1000,
            'middleware' => IdentityFactoryMiddleware::class
        ]
    ],

];
