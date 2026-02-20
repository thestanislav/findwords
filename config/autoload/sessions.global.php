<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 12.12.2018
 * Time: 12:40
 */
return [
    'php_settings' => [
        'session.cookie_lifetime' => 86400,
        'session.gc_maxlifetime' => 86400,
        'session.save_handler' => 'memcached',
        'session.save_path' => (getenv('MEMCACHED_HOST') ?: '127.0.0.1') . ':' . (getenv('MEMCACHED_PORT') ?: '11211'),
        'memcached.sess_locking' => 'Off',
    ]
];