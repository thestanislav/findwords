<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 1/30/2018
 * Time: 18:36
 */

use ExprAs\Core\Middleware\JsonAttachmentDetectMiddleware;
use ExprAs\Core\Middleware\JsonQueryDetectMiddleware;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;

return [
    'middleware_pipeline' => [
        [
            'middleware' => JsonQueryDetectMiddleware::class,
            'priority' => 4500
        ],[
            'middleware' => JsonAttachmentDetectMiddleware::class,
            'priority' => 4100
        ], [
            'middleware' => BodyParamsMiddleware::class,
            'priority' => 4500
        ], [
            'middleware' => ErrorHandler::class,
            'priority' => 10000
        ]
    ],
    'pre_pipe_dispatch_middleware' => [

    ],
    'post_pipe_dispatch_middleware' => [],
];
