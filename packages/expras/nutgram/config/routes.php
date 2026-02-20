<?php


use ExprAs\Nutgram\Mezzio\Handler\WebhookHandler;

return [
    'routes' => [
        [
            'name'            => 'webhook-nutgram',
            'path'            => '/ng/wh/update',
            'middleware'      => WebhookHandler::class,
            'allowed_methods' => ['POST'],
            'options'         => [
                'defaults' => [

                ]
            ]
        ]
    ],
];
