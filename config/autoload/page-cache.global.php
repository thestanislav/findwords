<?php

use PageCache\IdGenerator\FullUriIdGenerator\FullUriIdGenerator;
use PageCache\PageCacheMiddleware;
use PageCache\Strategy\RouteNameStrategy\RouteNameStrategy;

return [
    PageCacheMiddleware::class => [
        'enabled'                    => true,
        'id_generator'               => FullUriIdGenerator::class,
        'strategy'                   => [
            RouteNameStrategy::class => [
                'names' => [
                    'dictionary-anagram-term',
                    'dictionary-rhyme-term',
                    'dictionary-crossword'
                ]
            ]
        ],
        'storage_adapter'            => [
            'name'    => \Laminas\Cache\Storage\Adapter\Filesystem::class,
            'options' => [
                //'ttl' => 15552000,
                'cache_dir'       => getcwd() . '/data/cache',
                'dir_level'       => 2,
                'dir_permission'  => 0770,
                'file_permission' => 0660
            ],
            'plugins' => [
                [
                    'name' => 'serializer',
                    'options' => [
                        'serializer' => \ExprAs\Core\Serializer\Gzip::class,
                        'serializer_options' => [
                            'compressionLevel' => 9
                        ]
                    ]
                ],
            ]
        ],

    ]
];