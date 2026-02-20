<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 21.08.13
 * Time: 20:49
 */

return [
    'expras_mailer' => [
        'transport' => [
            // Symfony Mailer DSN format
            // Examples:
            // - smtp://user:pass@smtp.example.com:587
            // - smtp://smtp.mail.selcloud.ru:1126?username=user&password=pass
            // - sendmail://default
            // - native://default
            'dsn' => 'native://default',
        ],
        'module' => [
            'sendLimit' => 100
        ],
        'message' => [
            'default' => [
                'from' => [
                    'name' => '',
                    'email' => ''
                ],
                'subject' => '',
            ]
        ]
    ]
];

