<?php

use ExprAs\Rest\Hydrator\RestHydrator;

return [
    'hydrators' => [
        'factories' => [
            RestHydrator::class => \ExprAs\Rest\Factory\RestHydratorFactory::class,
        ]
    ]
];
