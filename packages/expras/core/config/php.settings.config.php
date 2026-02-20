<?php

return [
    'php_settings' => [
        'display_startup_errors'        => true,
        'display_errors'                => true,
        'error_reporting'               => E_ALL ^ E_USER_DEPRECATED,
        'date.timezone'                 => 'Europe/Moscow',
        'intl.default_locale'           => 'ru_RU.UTF-8',
        'locale.all'                    => 'ru_RU.UTF-8',
        'mbstring.internal_encoding'    => 'UTF-8',
        'session.name' => '__expras'
    ]
];
