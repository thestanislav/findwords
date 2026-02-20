<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 07.04.2015
 * Time: 11:28
 */
return [
    'cache' => [
        'adapter' => 'memcached',
        'options' => [
            'ttl'        => 0,
            'namespace'  => sprintf('%u', crc32(__DIR__ . '')),
            'servers'    => [
                [getenv('MEMCACHED_HOST') ?: '127.0.0.1', (int) (getenv('MEMCACHED_PORT') ?: 11211)]
            ],

            'liboptions' => [
                'COMPRESSION'     => true,
                'binary_protocol' => true,
                'no_block'        => true,
                'connect_timeout' => 100
            ]
        ]
    ]
];