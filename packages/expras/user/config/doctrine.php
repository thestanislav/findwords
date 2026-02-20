<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 15:15
 */


use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use ExprAs\User\Entity\Profile;
use ExprAs\User\Entity\RememberMe;
use ExprAs\User\Entity\User;
use ExprAs\User\OwnerAware\OwnerListener;
use ExprAs\User\DoctrineListener\RememberMeUserModifierListener;

return [
    'doctrine' => [
        'driver'       => [
            'exprass_user_entity_driver' => [
                'class' => AttributeDriver::class,
                'paths' => [
                    __DIR__ . '/../src/Entity',
                ],
            ],

            // override this when custom user entity is used
            'orm_default' => [
                'drivers' => [
                    User::class       => 'exprass_user_entity_driver',
                    Profile::class    => 'exprass_user_entity_driver',
                    RememberMe::class => 'exprass_user_entity_driver',
                ]
            ]
        ],
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    OwnerListener::class,
                    RememberMeUserModifierListener::class,
                ]
            ]
        ],
        'event_manager' => [
            'orm_default' => [
                'subscribers' => [
                    OwnerListener::class,
                    RememberMeUserModifierListener::class,
                ]
            ]
        ],
    ]
];
