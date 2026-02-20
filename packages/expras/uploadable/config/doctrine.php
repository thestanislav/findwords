<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 15:15
 */

use Doctrine\ORM\Mapping\Driver\AttributeDriver;

return [
    'doctrine' => [
        'driver' => [
            'exprass_uploadable_entity_driver' => [
                'class' => AttributeDriver::class,
                'paths' => [
                    __DIR__ . '/../src/Entity',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'ExprAs\Uploadable\Entity' => 'exprass_uploadable_entity_driver',
                ]
            ]
        ],

        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    \ExprAs\Uploadable\EventListener\UploadableListener::class,
                ]
            ]
        ],

    ]
];
