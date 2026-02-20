<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 01.04.13
 * Time: 16:23
 */

use Doctrine\ORM\Mapping\Driver\AttributeDriver;

return [
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'types' => [
                    'MailMessage' => ExprAs\Mailer\Doctrine\Types\MailMessage::class
                ]
            ],
        ],
        'driver' => [
            'as_mailer_driver' => [
                'class' => AttributeDriver::class,
                'paths' => [
                    realpath(__DIR__ . '/../src/Entity')
                ],
            ],
            'orm_default' => [

                'drivers' => [
                    'ExprAs\Mailer\Entity' => 'as_mailer_driver',
                ]
            ]
        ]
    ]
];

