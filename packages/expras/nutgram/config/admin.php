<?php

use ExprAs\Nutgram\Mezzio\Handler\ChatActions;
use ExprAs\Nutgram\Mezzio\Handler\ScheduledMessageHandler;
use ExprAs\Nutgram\Handler\TelegramLogAdminHandler;

use ExprAs\Nutgram\Entity\DefaultUser;
use ExprAs\Nutgram\Entity\DefaultChat;
use ExprAs\Nutgram\Entity\ScheduledMessage;
use ExprAs\Nutgram\Entity\ScheduledMessageSentStatus;
use ExprAs\Nutgram\Entity\UserMessage;
use ExprAs\Nutgram\Entity\TelegramLogEntity;

return [
    'exprass_admin' => [
        'permissions' => [
            'admin' => [

                [
                    'type' => 'deny',
                    'resource' => 'expras-nutgram-logs',
                ],
            ]
        ],
        'routes' => [
            [
                'name' => 'expras-nutgram-bot-send-message',
                'path' => '/expras-nutgram-bot/send-message',
                'middleware' => ChatActions::class,
                'allowed_methods' => ['POST'],
                'options' => [
                    'defaults' => [
                        'action' => 'sendMessage'
                    ],
                ]
            ],
            [
                'name' => 'expras-nutgram-bot-conversation',
                'path' => '/expras-nutgram-bot/conversation',
                'middleware' => ChatActions::class,
                'allowed_methods' => ['POST'],
                'options' => [
                    'defaults' => [
                        'action' => 'conversation'
                    ],
                ]
            ],
            [
                'name' => 'expras-nutgram-bot-file',
                'path' => '/expras-nutgram-bot/file',
                'middleware' => ChatActions::class,
                'allowed_methods' => ['GET'],
                'options' => [
                    'defaults' => [
                        'action' => 'file'
                    ],
                ]
            ],
            /*[
                'name'            => 'expras-nutgram-bot-schedule-queue-update',
                'path'            => '/expras-nutgram-bot-scheduledmessage-send-status/queueUpdate',
                'allowed_methods' => ['GET'],
                'middleware'      => ScheduledMessageHandler::class,
                'options'         => [
                    'defaults' => [
                        'action' => 'queueUpdate'
                    ],
                ]
            ],
            [
                'name'            => 'expras-nutgram-bot-schedule-queue-delete',
                'path'            => '/expras-nutgram-bot-scheduledmessage-send-status/queueDelete',
                'allowed_methods' => ['GET'],
                'middleware'      => ScheduledMessageHandler::class,
                'options'         => [
                    'defaults' => [
                        'action' => 'queueDelete'
                    ],
                ]
            ]*/
        ],
        'resource_mappings' => [

            'expras-nutgram-bot-scheduledmessage' => [
                'entity' => ScheduledMessage::class,
                'name' => 'expras-nutgram-bot-scheduledmessage',
                'middleware' => ScheduledMessageHandler::class,
                'excludeFields' => [
                    'sentStatuses',
                    'scheduledToUsers'
                ],
                'priority' => -100,
                'spec' => [
                    'options' => [
                        'label' => 'Запланированные пуши',
                    ],
                    'create' => [],
                    'edit' => [],
                    'form' => [
                        'inputs' => [
                            [
                                'source' => 'content',
                                'label' => 'Содержание',
                                'required' => true,
                                'type' => 'text',
                                'multiline' => true,
                            ],
                            [
                                'source' => 'useMarkDown',
                                'label' => 'Использовать разметку MarkDown',
                                'required' => false,
                                'type' => 'boolean',
                                'helperText' => 'Подробнее по ссылке https://core.telegram.org/bots/api#markdown-style"',
                            ],
                            [
                                'source' => 'scheduledTime',
                                'label' => 'Время отправки',
                                'required' => true,
                                'type' => 'datetime',
                                'helperText' => 'Укажите время с интервалом до 10 минут',
                                'pickerOptions' => [
                                    'format' => 'DD-MM-YYYY HH:mm:ss'
                                ],
                                'timezone' => 'UTC'
                            ],
                            [
                                'source' => 'attachment',
                                'label' => 'Прикрепить файл',
                                'type' => 'file',
                                'required' => false,
                                'maxSize' => min(ini_get('upload_max_filesize'), ini_get('post_max_size')),
                            ],
                            [
                                'source' => 'scheduledToUsers',
                                'label' => 'Отправить только следующим пользователям',
                                'type' => 'referenceMany',
                                'target' => 'id',
                                'reference' => 'expras-nutgram-bot-users',
                                'sort' => [
                                    'field' => 'username',
                                    'order' => 'ASC',
                                ],
                                'child' => [
                                    //'type'       => 'select',
                                    'optionText' => 'username',
                                    'maxLength' => 128,
                                ],

                            ],
                            // may be rewritten in other configs with buttons
                            /*[
                                'source'    => 'buttonText',
                                'label'     => 'Кнопка',
                                'type'      => 'text',
                                'required'  => false,
                                'maxLength' => 32,
                                'validate'  => [
                                    [
                                        'name' => 'maxLength',
                                        'args' => [
                                            32
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'source'    => 'buttonCommand',
                                'label'     => 'Команда',
                                'type'      => 'text',
                                'required'  => false,
                                'maxLength' => 128,
                                'validate'  => [
                                    [
                                        'name' => 'maxLength',
                                        'args' => [
                                            128
                                        ]
                                    ]
                                ]
                            ]*/
                        ],
                    ],
                    'list' => [
                        'perPage' => 50,
                        'sort' => [
                            'field' => 'scheduledTime',
                            'order' => 'DESC',
                        ],
                        'fields' => [
                            [
                                'source' => 'content',
                                'label' => 'Содержание',
                                'type' => 'text',
                                'sx' => [
                                    'whiteSpace' => 'pre-wrap',
                                ],
                            ],
                            [
                                'source' => 'scheduledTime',
                                'label' => 'Время отправки',
                                'type' => 'datetime',

                                'options' => [
                                    'timeZone' => 'UTC'
                                ]
                            ],
                        ],
                    ],
                    'show' => [
                        'type' => 'tabbed',
                        'tabs' => [
                            [
                                'label' => 'Данные',
                                'fields' => [
                                    [
                                        'source' => 'content',
                                        'label' => 'Содержание',
                                        'type' => 'text',
                                        'sx' => [
                                            'whiteSpace' => 'pre-wrap',
                                        ],
                                    ],
                                    [
                                        'source' => 'scheduledTime',
                                        'label' => 'Время отправки',
                                        'type' => 'datetime',
                                        'showTime' => true,
                                        'showDate' => true,
                                        'locales' => 'ru-RU',

                                        'options' => [
                                            'timeZone' => 'UTC'
                                        ]
                                    ],
                                ],
                            ],
                            [
                                'label' => 'Отправлено пользователям',
                                'fields' => [
                                    [
                                        'label' => false,
                                        'source' => 'id',
                                        'type' => 'referenceMany',
                                        'perPage' => 50,
                                        'pagination' => [
                                            'rowsPerPageOptions' => [10, 25, 50, 100],
                                        ],
                                        'target' => 'scheduledMessage',
                                        'reference' => 'expras-nutgram-bot-scheduledmessage-send-status',
                                        'sort' => [
                                            'field' => 'username',
                                            'order' => 'ASC',
                                        ],
                                        'child' => [
                                            'type' => 'datagrid',

                                            'footerActions' => [
                                                [
                                                    'action' => 'queueDelete',
                                                    'label' => 'В очередь на удаление',
                                                    'color' => 'secondary',
                                                    'size' => 'small',
                                                    'variant' => 'contained',
                                                    'fetch' => [
                                                        'method' => 'POST',
                                                        'params' => [
                                                            'id' => '%id%',
                                                        ],
                                                    ],
                                                ],
                                                [
                                                    'action' => 'queueUpdate',
                                                    'label' => 'В очередь на обновление',
                                                    'color' => 'secondary',
                                                    'size' => 'small',
                                                    'variant' => 'contained',
                                                    'fetch' => [
                                                        'method' => 'GET',
                                                        'params' => [
                                                            'id' => '%id%',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                            'fields' => [
                                                [
                                                    'source' => 'telegramMessageId',
                                                    'type' => 'number',
                                                    'label' => 'Message Id',
                                                ],
                                                [
                                                    'source' => 'botUser',
                                                    'type' => 'reference',
                                                    'reference' => 'expras-nutgram-bot-users',
                                                    'label' => 'Логин',
                                                    'referenceSource' => 'username',
                                                ],
                                                [
                                                    'source' => 'deleted',
                                                    'type' => 'boolean',
                                                    'label' => 'Удалено',
                                                ],
                                                [
                                                    'source' => 'scheduledToUpdate',
                                                    'type' => 'boolean',
                                                    'label' => 'Запланировано обновление',
                                                ],
                                                [
                                                    'source' => 'scheduledToDelete',
                                                    'type' => 'boolean',
                                                    'label' => 'Запланировано удаление',
                                                ],


                                                [
                                                    'source' => 'statusCode',
                                                    'type' => 'number',
                                                    'label' => 'Статус',
                                                ],
                                                [

                                                    'source' => 'statusText',
                                                    'label' => 'Ошибка',
                                                ],
                                                [
                                                    'type' => 'datetime',
                                                    'source' => 'sentAt',
                                                    'label' => 'Дата',
                                                    'locales' => 'ru-RU',

                                                    'options' => [
                                                        'timeZone' => 'UTC'
                                                    ]
                                                ],

                                            ]
                                        ]
                                    ],
                                ]
                            ],
                            [
                                'label' => 'Отправить тестовое сообщение',
                                'fields' => [
                                    [
                                        'type' => 'nutgramScheduledMessageTestSender'
                                    ],
                                ]
                            ]
                        ],
                    ]
                ],
            ],
            'expras-nutgram-bot-scheduledmessage-send-status' => [
                'entity' => ScheduledMessageSentStatus::class,
                'name' => 'expras-nutgram-bot-scheduledmessage-send-status',
                'spec' => []
            ],
            'expras-nutgram-bot-users' => [
                'entity' => DefaultUser::class,
                'name' => 'expras-nutgram-bot-users',
                'excludeFields' => [
                    'waitingContext',
                    'params',
                    'messages',
                    'waitingContext'
                ],
                'spec' => [
                    'icon' => 'Telegram',
                    'options' => [
                        'label' => 'Пользователи бота',
                        'labelPlural' => 'Пользователи бота',
                    ],
                    'recordRepresentation' => 'username',
                    'show' => [
                        'tabs' => [
                            10 => [
                                'label' => 'Все сообщения пользователя',
                                'fields' => [
                                    [
                                        'source' => 'id',
                                        'type' => 'referencemany',
                                        'reference' => 'expras-nutgram-user-messages',
                                        'target' => 'user',
                                        'sort' => [
                                            'field' => 'ctime',
                                            'order' => 'DESC'
                                        ],
                                        'perPage' => 50,
                                        'pagination' => [
                                            'perPage' => [10, 50, 100],
                                        ],
                                        'label' => false,
                                        'child' => [

                                            'fields' => [
                                                [
                                                    'source' => 'user',
                                                    'label' => 'Пользователь',
                                                    'type' => 'reference',
                                                    'reference' => 'expras-nutgram-bot-users',
                                                    'referenceSource' => 'username',
                                                    'link' => 'show'
                                                ],
                                                [
                                                    'source' => 'textMessage',
                                                    'label' => 'Сообщение',
                                                    'type' => 'element',
                                                    'render' => [
                                                        'record',
                                                        '
                                                        const video = record.messageObject?.message?.video;
                                                        const photo = record.messageObject?.message?.photo;
                                                        const document = record.messageObject?.message?.document;

                                                        const fileId = video?.file_id || photo?.[photo.length-1]?.file_id || document?.file_id;

                                                        return ["div", {}, [
                                                           ["span", {}, `${record.textMessage??""}` + (fileId ? `File Id: ${fileId}` : ``)],
                                                           (photo && photo.length > 0 )? ["img", {style: {maxWidth: "200px"}, src: `/.admin/expras-nutgram-bot/file?fileId=${fileId}`}] : null,
                                                           video ? ["video", {}, [
                                                           ["source", {
                                                           src: `/.admin/expras-nutgram-bot/file?fileId=${fileId}`,
                                                            type: video.mime_type,
                                                            width: video.width,
                                                            height: video.height
                                                            }]
                                                           ]] : null,
                                                        ]]'
                                                    ]
                                                ],
                                                [
                                                    'source' => 'updateType',
                                                    'label' => 'Тип',
                                                    'type' => 'text',
                                                ],
                                                [
                                                    'source' => 'ctime',
                                                    'label' => 'Дата',
                                                    'type' => 'datetime'
                                                ]
                                            ]
                                        ]

                                    ]
                                ]
                            ],
                            20 => [
                                'label' => 'Беседка',
                                'fields' => [
                                    [
                                        'type' => 'nutgramConversation'
                                    ]
                                ]
                            ]
                        ],
                    ],
                ]
            ],
            'expras-nutgram-user-messages' => [
                'entity' => UserMessage::class,
                'name' => 'expras-nutgram-user-messages',

                'spec' => [],
            ],
            'expras-nutgram-bot-chats' => [
                'entity' => DefaultChat::class,
                'name' => 'expras-nutgram-bot-chats',
                'spec' => [
                    'options' => [
                        'label' => 'Чаты бота',
                    ],
                ],
            ],
            'expras-nutgram-logs' => [
                'priority' => -999,
                'entity' => TelegramLogEntity::class,
                'middleware' => TelegramLogAdminHandler::class,
                'name' => 'expras-nutgram-logs',
                'spec' => [

                    'icon' => 'Telegram',
                    'show' => false,
                    'options' => [
                        'label' => 'Журнал Telegram бота',
                    ],
                    'list' => [
                        'title' => 'Журнал Telegram бота',
                        'perPage' => 50,
                        'type' => 'simpleList',
                        'sort' => [
                            'field' => 'datetime',
                            'order' => 'DESC'
                        ],
                        'actions' => [
                            [
                                'action' => 'fetch',
                                'fetch' => 'truncate',
                                'label' => 'Очистить',
                                'icon' => 'ClearAll'
                            ]
                        ],
                        'queryOptions' => [
                            'meta' => [
                                'extractRelations' => [
                                    'user',
                                    'chat'
                                ]
                            ]
                        ],
                        'filters' => [
                            [
                                'type' => 'select',
                                'source' => 'level',
                                'label' => 'Уровень',
                                'alwaysOn' => true,
                                'choices' => [
                                    ['id' => 100, 'name' => 'Debug'],
                                    ['id' => 200, 'name' => 'Info'],
                                    ['id' => 250, 'name' => 'Notice'],
                                    ['id' => 300, 'name' => 'Warning'],
                                    ['id' => 400, 'name' => 'Error'],
                                    ['id' => 500, 'name' => 'Critical'],
                                    ['id' => 550, 'name' => 'Alert'],
                                    ['id' => 600, 'name' => 'Emergency'],
                                ]
                            ],
                            [
                                'type' => 'text',
                                'source' => 'handler',
                                'label' => 'Обработчик',
                            ],
                            [
                                'type' => 'reference',
                                'source' => 'user',
                                'label' => 'User',
                                'reference' => 'expras-nutgram-bot-users',
                                'alwaysOn' => true,
                                'child' => [
                                    [
                                        'source' => 'username'
                                    ]
                                ]
                            ]
                        ],
                        'linkType' => 'show',
                        'primarySource' => 'message',
                        'secondarySource' => ['datetime', 'user.username', 'handler'],
                        'tertiarySource' => 'levelName',
                        'rowSx' => [
                            'record',
                            "
                                switch (record.level){
                                    case 550:
                                    case 600:
                                        return { backgroundColor: '#920000'};
                                    case 500:
                                        return { backgroundColor: '#300000'};  
                                    case 400:
                                        return { backgroundColor: '#ff0000'};
                                    case 300:
                                        return { backgroundColor: '#ff2626'};
                                }
                            "
                        ],

                    ],
                    'show' => [
                        'type' => 'tabbed',
                        'tabs' => [
                            [
                                'label' => 'Данные',
                                'fields' => [
                                    [
                                        'source' => 'updateId',
                                        'label' => 'Update Id',
                                        'type' => 'number',
                                    ],
                                    [
                                        'source' => 'updateType',
                                        'label' => 'Update Type',
                                    ],
                                    [
                                        'source' => 'messageText',
                                        'label' => 'Сообщение',
                                        'type' => 'text',
                                        'sx' => [
                                            'whiteSpace' => 'pre-wrap',
                                        ],
                                    ],
                                    [
                                        'source' => 'datetime',
                                        'label' => 'Дата',
                                        'type' => 'datetime',
                                        'locales' => 'ru-RU',
                                        'showTime' => true,
                                        'showDate' => true,
                                    ],
                                    [
                                        'label' => 'Уровень',
                                        'type' => 'wrapper',
                                        'separator' => ' ',
                                        'children' => [
                                            [
                                                'source' => 'level',
                                                'label' => 'Уровень',
                                                'type' => 'text',
                                            ],
                                            [
                                                'source' => 'levelName',
                                                'label' => 'Уровень',
                                                'type' => 'text',
                                            ],
                                        ],
                                    ],
                                    [
                                        'source' => 'handler',
                                        'label' => 'Обработчик',
                                        'type' => 'text',
                                    ],
                                ]
                            ],
                            [
                                'label' => 'Пользователь',
                                'fields' => [
                                    [
                                        'source' => 'user',
                                        'label' => 'Пользователь',
                                        'type' => 'reference',
                                        'reference' => 'expras-nutgram-bot-users',
                                        'link' => 'show',
                                        'fields' => [
                                            [
                                                'source' => 'username',
                                            ]
                                        ],
                                    ],
                                    [
                                        'source' => 'user',
                                        'label' => 'Пользователь',
                                        'type' => 'reference',
                                        'reference' => 'expras-nutgram-bot-users',
                                        'link' => 'show',
                                        'fields' => [
                                            [
                                                'source' => 'firstName',
                                                'label' => 'Имя',
                                            ],
                                            [
                                                'source' => 'lastName',
                                                'label' => 'Фамилия',
                                            ],
                                        ]
                                    ],

                                ]
                            ],
                            [
                                'label' => 'Обновление',
                                'fields' => [
                                    [
                                        'source' => 'update',
                                        'type' => 'element',
                                        'render' => ['record', 'return ["code", {style: {whiteSpace: "pre-wrap"}}, JSON.stringify(record.update, null, 2)]'],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ]
    ],

];
