<?php
return [
    'doctrine' => [
        'dbal' => [
            'charset' => 'utf8mb4',
            'table_options' => [
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci'
            ],
        ],
        'configuration' => [
            'orm_default' => [
                'metadata_cache' => 'default',
                'query_cache' => 'default',
                'result_cache' => 'default',
                'generate_proxies' => true
            ]
        ],

        'connection' => [
            'orm_default' => [
                'driverClass' => \Doctrine\DBAL\Driver\Mysqli\Driver::class,
                'params' => [
                    ...(getenv('MYSQL_SOCKET')
                        ? ['unix_socket' => getenv('MYSQL_SOCKET')]
                        : ['host' => getenv('MYSQL_HOST') ?: '127.0.0.1', 'port' => getenv('MYSQL_PORT') ?: '3306']
                    ),
                    'port' => '3306',
                    'user' => 'findwords',
                    'password' => 'ffHe9QI87hmsHCedRhM',
                    'dbname' => 'db_findwords',
                    'charset' => 'utf8mb4',
                    'driverOptions' => [
                        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
                    ]
                ]
            ],
        ]
    ]
];
