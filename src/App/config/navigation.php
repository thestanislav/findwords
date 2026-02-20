<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 08.11.13
 * Time: 16:40
 */
return [
    'navigation' =>
        [
            'dictionary' => [
                'home' => [
                    'label'         => 'Word finder',
                    'route'         => 'home',
                    'useRouteMatch' => true,
                    'pages'         => [
                        'definition' => [
                            'label' => 'Word definitions',
                            'route' => 'dictionary-term',
                        ],
                        'rhyme'      => [
                            'label' => 'Rhyming',
                            'route' => 'dictionary-rhyme',
                        ],
                        'anagram'    => [
                            'label' => 'Anagram solver',
                            'route' => 'dictionary-anagram',
                        ],
                        'starts'     => [
                            'label'  => 'Words starting with ...',
                            'route'  => 'dictionary-contains',
                            'params' => [
                                'action' => 'starts',
                            ]
                        ],
                        'ends'       => [
                            'label'  => 'Words ending with ...',
                            'route'  => 'dictionary-contains',
                            'params' => [
                                'action' => 'ends',
                            ]
                        ],
                        'contains'   => [
                            'label'  => 'Words containing letters',
                            'route'  => 'dictionary-contains',
                            'useRouteMatch' => true,
                            'params' => [
                                'action' => 'contains',
                            ]
                        ],
                        'mask'       => [
                            'label'  => 'Words by mask',
                            'route'  => 'dictionary-contains',
                            'params' => [
                                'action' => 'mask',
                            ]
                        ],
                        'crossword'  => [
                            'label'  => 'Crossword clues',
                            'route'  => 'dictionary-crossword',
                            'params' => [
                                'action' => 'index',
                            ],
                            'pages'  => [
                                'crossword-definition' => [
                                    'label'         => 'Crossword clues',
                                    'route'         => 'dictionary-crossword-definition',
                                    'useRouteMatch' => true,
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
];