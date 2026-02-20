<?php

use ExprAs\Doctrine\Console;
use Doctrine\Common\DataFixtures\Loader;

return [
    'mezzio-symfony-console' => [
        'commands' => [
           ...class_exists(Loader::class) ? [ Console\LoadFixtures::class] : []
        ]
    ]
];
