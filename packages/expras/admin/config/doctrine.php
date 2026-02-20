<?php

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use ExprAs\Admin\DoctrineListener\AdminLogEntityModifierListener;

/**
 * Doctrine configuration for Admin module
 */
return [
    'doctrine' => [
        'driver' => [
            'expras_admin_entity_driver' => [
                'class' => AttributeDriver::class,
                'paths' => [
                    __DIR__ . '/../src/Entity'
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'ExprAs\Admin\Entity' => 'expras_admin_entity_driver',
                ],
            ],
        ],
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    AdminLogEntityModifierListener::class,
                ],
            ],
        ],
    ]
];

