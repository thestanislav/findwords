<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 2/6/2018
 * Time: 13:41
 */
return [
    'pagination' => ['profiles' => ['default' => 'search', 'search' => ['partial' => 'expras-view::paginator/search', 'style' => 'sliding']]],
    'templates' => [
        'paths' => [
            'expras-view' => [__DIR__ . '/../templates/expras-view'],
        ]
    ],
];
