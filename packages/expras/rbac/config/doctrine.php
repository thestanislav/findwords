<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 15:15
 */

use Doctrine\ORM\Mapping\Driver\AttributeDriver;

return [
    'doctrine' => [
        'driver'       => [
            'exprass_rbac_entity_driver' => [
                'class' => AttributeDriver::class,
                'paths' => [
                    __DIR__ . '/../src/Entity',
                ],
            ],
            'orm_default'                => [
                'drivers' => [
                    'ExprAs\Rbac\Entity' => 'exprass_rbac_entity_driver',
                ]
            ]
        ],
        'eventmanager' => ['orm_default' => ['subscribers' => [\Gedmo\Tree\TreeListener::class]]],

    ]
];
