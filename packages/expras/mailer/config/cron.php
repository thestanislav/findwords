<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 00:00
 */

return [
    'expras_cron' => [
        'jobs' => [
            'mailer:process' => [
                'schedule' => '* * * * *',
                'command' => 'mailer:process',
            ]
        ]
    ]
];

