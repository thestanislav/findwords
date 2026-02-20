<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 2/20/2018
 * Time: 18:40
 */

return [
    'mezzio-symfony-console' => [
        'commands' => [
            \App\Console\PhonemeRebuild::class,
            \App\Console\CrawlErrors::class
        ]
    ]

];