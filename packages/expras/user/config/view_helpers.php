<?php

use ExprAs\User\View\Helper\Identity;
use ExprAs\User\View\Helper\IdentityFactoryFactory;

return [
    'view_helpers' => [
        'factories' => [
            Identity::class => IdentityFactoryFactory::class,
        ],
    ],
];
