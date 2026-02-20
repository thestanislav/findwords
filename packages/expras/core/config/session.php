<?php

use Laminas\Session;

return [
    'session_config' => [
        'remember_me_seconds' => 1800,
        'name'                => '__expras',
    ],
    'session_manager' => [
        'storage' => Session\Storage\SessionStorage::class,
        'validators' => [
            Session\Validator\RemoteAddr::class,
            Session\Validator\HttpUserAgent::class,
        ],
    ],
    'session_storage' => [
        'type' => Session\Storage\SessionArrayStorage::class,
    ]
];
