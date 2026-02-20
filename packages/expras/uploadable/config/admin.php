<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 1/30/2018
 * Time: 19:18
 */


use ExprAs\Uploadable\Handler\FetchHandler;

return [
    'exprass_admin' => [
        'resource_mappings' => [],
        'routes' => [
            [
                'name' => 'fetch-uploaded',
                'path' => '/uploaded/{uploaded_id:[\d]+}',
                'middleware' => [
                    FetchHandler::class
                ],
                'allowed_methods' => ['GET'],
            ]
        ],
    ]];
