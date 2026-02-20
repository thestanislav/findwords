<?php

use ExprAs\User\MezzioAuthentication;

return [
    'authentication' => [
        'redirect' => '',
        'username' => 'identity',
        'password' => 'credential',
        'adapters' => [
            MezzioAuthentication\DoctrineAdapter::class,
            MezzioAuthentication\SessionAdapter::class,
            MezzioAuthentication\RememberMeAdapter::class

        ],
        'remember_me' => [
            'cookie_expire' => 2_592_000,
            'cookie_domain' => null,
            'cookie_name' => 'remember_me',
            'entity_name' => \ExprAs\User\Entity\RememberMe::class
        ]
    ]
];
