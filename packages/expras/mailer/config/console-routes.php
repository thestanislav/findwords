<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 2/21/2018
 * Time: 00:30
 */

return [
    'mezzio-symfony-console' => [
        'commands' => [
            ExprAs\Mailer\Console\ProcessDispatcher::class
        ]
    ]
];

