<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 23.03.2019
 * Time: 11:05
 */

return [
    'templates' => [
        'paths' => [
            'app' => [__DIR__ . '/../templates/app'],
            'dictionary' => [__DIR__ . '/../templates/dictionary'],
            'anagram' => [__DIR__ . '/../templates/anagram'],
            'word' => [__DIR__ . '/../templates/word'],
            'cross-word' => [__DIR__ . '/../templates/cross-word'],
            'rhyme' => [__DIR__ . '/../templates/rhyme'],
            'five-letters' => [__DIR__ . '/../templates/five-letters'],
            'error' => [__DIR__ . '/../templates/error'],
            'layout' => [__DIR__ . '/../templates/layout'],
        ],
    ],
    'view_manager' => [
        'doctype' => 'html5',
    ],
];