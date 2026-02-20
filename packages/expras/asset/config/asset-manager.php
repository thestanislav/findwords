<?php
/**
 * Author: Stanislav Anisimov<stanislav@ww9.ru>
 * Date: 24.03.13
 * Time: 17:24
 */
return [
    'asset_manager' => [
        'resolvers' => [
            \ExprAs\Asset\Resolver\PatternResolver::class => 0
        ],
        'resolver_configs' => [
            'aliases' => [
                'bower-asset/' => 'vendor/bower-asset/',
            ]
        ]
    ],
];
