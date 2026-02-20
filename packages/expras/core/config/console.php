<?php

use ExprAs\Core\Console;

return [
    'mezzio-symfony-console' => [
        'commands' => [
            Console\CacheFlush::class,
            Console\ShowConfig::class,
        ]
    ]
];
