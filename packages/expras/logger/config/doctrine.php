<?php

return [
    'doctrine' => [
        'driver' => [
            'expras_logger_driver' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AttributeDriver::class,
                'paths' => [realpath(__DIR__ . '/../src/Entity')],
            ],
            'orm_default'          => [
                'drivers' => [
                    'ExprAs\Logger\Entity' => 'expras_logger_driver',
                ]
            ]
        ]
    ]
];
