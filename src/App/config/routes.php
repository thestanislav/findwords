<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 1/30/2018
 * Time: 19:18
 */


use App\Handler\AnagramPageHandler;
use App\Handler\CrossWordPageHandler;
use App\Handler\FiveLettersHandler;
use App\Handler\HomePageHandler;
use App\Handler\PingHandler;
use App\Handler\RhymePageHandler;
use App\Handler\WordPageHandler;

return [
    'routes' => [
        [
            'name'            => 'home',
            'path'            => '/',
            'middleware'      => HomePageHandler::class,
            'allowed_methods' => ['GET', 'POST'],
            'options'         => [
                'defaults' => [
                    'action' => 'index'
                ]
            ]
        ],
        [
            'name'            => 'api.ping',
            'path'            => '/api/ping',
            'middleware'      => PingHandler::class,
            'allowed_methods' => ['GET'],
            'options'         => [
                'defaults' => [
                ]
            ]
        ],
        [
            'name'       => 'dictionary-rhyme',
            'path'       => '/rhyme',
            'middleware' => RhymePageHandler::class,
            'options'    => [
                'route'    => '/rhyme',
                'defaults' => [
                    'controller' => 'Dictionary\Controller\Rhyme',
                    'action'     => 'index',
                ]
            ]
        ],
        [
            'name'       => 'dictionary-rhyme-search',
            'path'       => '/rhyme/search',
            'middleware' => RhymePageHandler::class,
            'options'    => [
                'defaults' => [
                    'action' => 'search',
                ]
            ]
        ],
        [
            'name'       => 'dictionary-rhyme-term',
            'path'       => '/rhyme/[{term:[\w\\-\_%\*]+}[/{length:[0-9]+}]]',
            'middleware' => RhymePageHandler::class,
            'options'    => [
                'defaults' => [
                    'action' => 'index',
                ]
            ]
        ],
        [
            'name'       => 'dictionary-anagram',
            'path'       => '/anagram',
            'middleware' => AnagramPageHandler::class,
            'options'    => [
                'defaults' => [
                    'action' => 'index',
                ]
            ]
        ],
        [
            'name'       => 'dictionary-anagram-search',
            'path'       => '/anagram/search',
            'middleware' => AnagramPageHandler::class,
            'options'    => [
                'defaults' => [
                    'action' => 'search',
                ]
            ]
        ],
        [
            'name'       => 'dictionary-anagram-term',
            'path'       => '/anagram/[{term:[\w\\-\_%\*]+}[/{length:[0-9]+}]]',
            'middleware' => AnagramPageHandler::class,
            'options'    => [
                'defaults' => [
                    'action' => 'index',
                ]
            ]
        ],
        [
            'name'          => 'dictionary-search',
            'path'          => '/term/search',
            'middleware'    => WordPageHandler::class,
            'options'       => [
                'defaults' => [
                    'action' => 'search',
                ]
            ],
            'may_terminate' => true,
        ],
        [
            'name'          => 'dictionary-term',
            'path'          => '/term[/{term}]',
            'middleware'    => WordPageHandler::class,
            'options'       => [
                'defaults' => [
                    'action' => 'index',
                ]
            ],
            'may_terminate' => true,
        ],
        [
            'name'       => 'dictionary-crossword-definition',
            'path'       => '/crossword/{definition_id:\d+}',
            'middleware' => CrossWordPageHandler::class,
            'options'    => [
                'defaults' => [
                    'controller' => CrossWordPageHandler::class,
                    'action'     => 'definition',
                ]
            ]
        ],
        [
            'name'       => 'dictionary-crossword',
            'path'       => '/crossword',
            'middleware' => CrossWordPageHandler::class,
            'options'    => [
                'defaults' => [
                    'controller' => CrossWordPageHandler::class,
                    'action'     => 'index',
                ]
            ]
        ],

        [
            'name'       => 'dictionary-contains',
            'path'       => '/{action:(?:ends|starts|contains|mask)}[/{chars:[\w\-\.\_%\*]+}[/{length:[0-9]+}]]',
            'middleware' => WordPageHandler::class,
            'options'    => [
                'defaults' => [
                    'action' => 'index'
                ],
            ],
        ],

        [
            'name'       => 'dictionary-five-letters',
            'path'       => '/five-letters',
            'middleware' => FiveLettersHandler::class,
            'options'    => [
                'defaults' => [
                    'action' => 'index'
                ],
            ],
        ],

        [
            'name'       => 'dictionary-five-letters-search',
            'path'       => '/five-letters/{mask}',
            'middleware' => FiveLettersHandler::class,
            'options'    => [
                'defaults' => [
                    'action' => 'search'
                ],
            ],
        ]
    ],
];