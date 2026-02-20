<?php

use ExprAs\Logger\Api\LoggerListHandler;
use ExprAs\Logger\Entity\ErrorLogEntity;
use ExprAs\Logger\Api\ErrorLogAdminHandler;

/**
 * Admin routes and log view configuration for Logger module
 */
return [
    'exprass_admin' => [
        'permissions'            => [
            'admin'     => [
                [
                    'type'     => 'deny',
                    'resource' => 'expras-logger',
                ],
            ]
        ],
        'routes' => [
            [
                'name' => 'logger.list',
                'path' => '/api/admin/loggers',
                'middleware' => [LoggerListHandler::class],
                'allowed_methods' => ['GET'],
            ],
        ],
        'resource_mappings' => [
            'expras-logger' => [
                'priority' => -1000,
                'entity'   => ErrorLogEntity::class,
                'middleware' => ErrorLogAdminHandler::class,
                'spec'     => [
                    'name'    => 'expras-logger',
                    'icon'    => 'BugReport',
                    'show'    => false,
                    'options' => [
                        'label' => 'Журнал ошибок',
                    ],
                    'list'    => [
                        'title'   => 'Журнал ошибок',
                        'perPage' => 50,
                        'type'    => 'simpleList',
                        'sort'    => [
                            'field' => 'timestamp',
                            'order' => 'DESC'
                        ],
                        'actions' => [
                            [
                                'action' => 'fetch',
                                'fetch'  => 'truncate',
                                'label'  => 'Очистить',
                                'icon'   => 'ClearAll'
                            ]
                        ],
                        'filters' => [
                            [
                                'type'     => 'select',
                                'source'   => 'priority',
                                'label'    => 'Приоритет',
                                'alwaysOn' => true,
                                'choices'  => [
                                    ['id' => 0, 'name' => 'чрезвычайно(emerg)'],
                                    ['id' => 1, 'name' => 'тревога(alert)'],
                                    ['id' => 2, 'name' => 'критически(crit)'],
                                    ['id' => 3, 'name' => 'ошибка(err)'],
                                    ['id' => 4, 'name' => 'предупреждение(warn)'],
                                    ['id' => 5, 'name' => 'уведомление(notice)'],
                                    ['id' => 6, 'name' => 'информация(info)'],
                                    ['id' => 7, 'name' => 'отладка(debug)'],
                                ]
                            ]
                        ],
                        'linkType'        => 'show',
                        'primarySource'   => 'message',
                        'secondarySource' => ['timestamp', 'file', 'line'],
                        'tertiarySource'  => 'priorityName',
                        'rowSx' => [
                            'record',
                            "
                                   switch (record.priority){
                                       case 1:
                                         return { backgroundColor: '#920000'};
                                       case 2:
                                         return { backgroundColor: '#300000'};  
                                       case 3:
                                         return { backgroundColor: '#ff0000'};
                                      case 4:
                                         return { backgroundColor: '#ff2626'};
                                   }
                               "
                        ],

                        'fields'  => [
                            [
                                'source'   => 'timestamp',
                                'label'    => 'Время',
                                'type'     => 'date',
                                'showTime' => true,
                                'position' => 'secondary'
                            ],
                            [
                                'source' => 'message',
                                'label'  => 'Сообщение'
                            ],
                            [
                                'source' => 'file',
                                'label'  => 'Файл'
                            ],
                            [
                                'source' => 'line',
                                'label'  => 'Линия'
                            ],
                            [
                                'source' => 'priorityName',
                                'label'  => 'Приоритет'
                            ],
                        ]
                    ],
                    'show' => [
                        'type' => 'tabbed',
                        'tabs' => [
                            [
                                'label' => 'Данные',
                                'fields' => [
                                    [
                                        'source' => 'message',
                                        'label' => 'Сообщение',
                                        'type' => 'text',
                                        'sx' => [
                                            'whiteSpace' => 'pre-wrap',
                                        ],
                                    ],
                                    [
                                        'source' => 'file',
                                        'label' => 'Файл',
                                        'type' => 'text',
                                    ],
                                    [
                                        'source' => 'line',
                                        'label' => 'Линия',
                                        'type' => 'text',
                                    ],
                                    [
                                        'source' => 'priorityName',
                                        'label' => 'Приоритет',
                                        'type' => 'text',
                                    ],
                                    [
                                        'source' => 'datetime',
                                        'label' => 'Дата',
                                        'type' => 'datetime',
                                        'locales' => 'ru-RU',
                                        'showTime' => true,
                                        'showDate' => true,
                                    ]
                                ]
                            ],[
                                'label' => 'Контекст',
                                'fields' => [
                                    [
                                        'source' => 'context',
                                        'label' => false,
                                        'type' => 'element',   
                                        'render' => [
                                            'record', 
                                            'return ["pre", {style: {whiteSpace: "pre-wrap"}}, JSON.stringify(record.context, null, 2)]'
                                        ]
                                    ]
                                ]
                            ],[
                                'label' => 'Трассировка',
                                'fields' => [
                                    [
                                        'source' => 'trace',
                                        'label' => false,
                                        'type' => 'element',   
                                        'render' => [
                                            'record', 
                                            'return ["pre", {style: {whiteSpace: "pre-wrap"}}, record.context?.trace]'
                                        ]
                                    ]
                                ]
                            ]
                        ]   
                    ]
                ]
            ]
        ]
    ]
];
