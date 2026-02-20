<?php
/**
 * Author: Stanislav Anisimov<stanislav@ww9.ru>
 * Date: 24.03.13
 * Time: 17:24
 */
return
    [
        'asset_manager' => [
            'routes' => [
                '^.*' => [
                    1 => '/assets/css/style.css',
                    3 => [
                        'helper' => 'inlineScript',
                        'name' => '/assets/js/pack.js'
                    ]
                ],
                '^admin.*' => [
                    1 => '/assets/css/admin.css?' . @filemtime('public/assets/css/admin.css'),
                ],
            ],
        ]
    ];
