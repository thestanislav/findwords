<?php

use ExprAs\Doctrine\Container\DefaultCacheFactory;
use ExprAs\Doctrine\Hydrator\DoctrineEntity;
use ExprAs\Doctrine\Hydrator\DoctrineEntityFactory;

return [
    'hydrators' => [
        'factories' => [
            DoctrineEntity::class => DoctrineEntityFactory::class,
        ],
    ]
];
