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
            'app_entity_driver' => [
                'class' => AttributeDriver::class,
                'paths' => [
                    __DIR__ . '/../src/Entity',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'App\\Entity' => 'app_entity_driver',
                ]
            ]
        ],
        'event_manager' => array(
            'orm_default' => array(
                'subscribers' => array(
                ),
            ),
        ),
        'eventmanager' => array(
            'orm_default' => array(
                'subscribers' => array(
                ),
            ),
        )
    ]
];
