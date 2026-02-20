<?php

use ExprAs\User\Entity\Profile;
use ExprAs\User\Entity\User;
use ExprAs\User\Handler\UserRestApiHandler;
use ExprAs\User\Middleware\MezzioAuthenticationMiddleware;

return [
    'exprass_admin' => [
        'permissions'       => [
            'admin' => [
                [
                    'resource' => 'expras-logger',
                    //'action'   => 'list-simple',
                    'record'   => [
                        'username' => 'dashman'
                    ],
                    'type'     => 'allow',
                ]
            ]
        ],
        'routes'            => [
            'exprass-admin-create-user' => [
                'middleware' => [
                    [
                        'middleware' => UserRestApiHandler::class,
                        'priority'   => 10,
                    ]
                ]
            ],
            'exprass-admin-update-user' => [
                'middleware' => [
                    [
                        'middleware' => UserRestApiHandler::class,
                        'priority'   => 10,
                    ]
                ]
            ],
        ],
        'resource_mappings' => [

            'user'         => [
                'entity'     => User::class,
                'middleware' => UserRestApiHandler::class,
                'spec'       => [
                    'name'                 => 'user',
                    'icon'                 => 'Group',
                    'options'              => [
                        'label'       => 'Пользователи',
                        'labelPlural' => 'Пользователи',
                    ],
                    'recordRepresentation' => 'username',
                    'list'                 => [
                        'title'      => 'Пользователи',
                        'sort'       => [
                            'field' => 'username',
                            'order' => 'asc'
                        ],
                        'perPage'    => 25,
                        'pagination' => [
                            'rowsPerPageOptions' => [10, 25, 50, 100]
                        ],
                        'fields'     => [

                            [
                                'source'   => 'username',
                                'type'     => 'text',
                                'required' => true,
                                'label'    => 'Имя пользователя'
                            ],
                            [
                                'source'   => 'email',
                                'type'     => 'email',
                                'required' => true,
                            ],
                            [
                                'source' => 'displayName',
                                'type'   => 'text',
                                'label'  => 'Псевдоним'
                            ],

                            [
                                'source' => 'active',
                                'type'   => 'bool-switch',
                                'label'  => 'Активный'
                            ],
                            [
                                'label'           => 'Роли',
                                'source'          => 'rbacRoles',
                                'reference'       => 'roles',
                                'referenceSource' => 'label',
                                'linkType'        => false,
                                'type'            => 'referenceArray',
                            ]
                        ],
                    ],
                    'edit'                 => [],
                    'create'               => [],
                    'form'                 => [
                        'type' => 'tabbed',
                        'tabs' => [
                            [
                                'label' => 'Основное',
                                'inputs' => [
                                    [
                                        'source'   => 'username',
                                        'type'     => 'text',
                                        'required' => true,
                                        'label'    => 'Имя пользователя',
                                        'validate' => [
                                            [
                                                'name' => 'regex',
                                                'args' => ['^[a-zA-Z0-9_\-\.]+$', 'Используйте латинские символы и цифры']
                                            ]
                                        ]
                                    ],
                                    [
                                        'source'   => 'email',
                                        'type'     => 'email',
                                        'required' => true,
                                    ],
                                    [
                                        'source'   => 'displayName',
                                        'type'     => 'text',
                                        'label'    => 'Псевдоним',
                                        'required' => true
                                    ],
                                    [
                                        'source' => 'password',
                                        'type'   => 'password',
                                        'label'  => 'Пароль'
                                    ],
                                    [
                                        'source'       => 'active',
                                        'type'         => 'boolean',
                                        'label'        => 'Активный',
                                        'defaultValue' => true
                                    ],
                                    [
                                        'label'     => 'Роли',
                                        'source'    => 'rbacRoles',
                                        'reference' => 'roles',
                                        'type'      => 'reference',
                                        'multiple'  => true,
                                        'required'  => true,
                                        'child'     => [
                                            'optionText' => 'label',
                                            'type'       => 'select'
                                        ]
                                    ],
                                ]
                            ],
                            [
                                'label' => 'Профиль',
                                'inputs' => [
                                    [
                                        'source'    => 'profile',
                                        'type'      => 'toOne',
                                        'label'     => 'Профиль',
                                        'reference' => 'user-profile',
                                        'required'  => false
                                    ],
                                ]
                            ]
                        ]
                    ],
                    'show' => [
                        'fields' => [
                            [
                                'source' => 'username',
                                'type' => 'text',
                                'label' => 'Имя пользователя'
                            ],
                            [
                                'source' => 'email',
                                'type' => 'email',
                                'label' => 'Email'
                            ],
                            [
                                'source' => 'displayName',
                                'type' => 'text',
                                'label' => 'Псевдоним'
                            ],
                            [
                                'source' => 'active',
                                'type' => 'boolean',
                                'label' => 'Активный'
                            ],
                            [
                                'source' => 'rbacRoles',
                                'type' => 'referenceArray',
                                'label' => 'Роли',
                                'reference' => 'roles',
                                'referenceSource' => 'label'
                            ], [
                                'source' => 'lastLoginAt',
                                'type' => 'datetime',
                                'label' => 'Последний вход',
                                'showTime' => true,
                                'showDate' => true,
                                'locales' => 'ru-RU'
                            ],
                            [
                                'source' => 'lastActivityAt',
                                'type' => 'datetime',
                                'label' => 'Последняя активность',
                                'showTime' => true,
                                'showDate' => true,
                                'locales' => 'ru-RU'
                            ]
                        ]
                    ],
                ]
            ],
            'user-profile' => [
                'entity' => Profile::class,
                'spec'   => [
                    'name'     => 'user-profile',
                    'priority' => -100,
                    'form'     => [
                        'inputs' => [
                            [
                                'source'   => 'name',
                                'type'     => 'text',
                                'required' => true,
                                'label'    => 'Имя'
                            ],
                            [
                                'source'   => 'surname',
                                'type'     => 'text',
                                'required' => true,
                                'label'    => 'Фамилия'
                            ],
                            [
                                'source' => 'avatar',
                                'type'   => 'image',
                                'label'  => 'Аватар',
                            ]
                        ]
                    ],
                ]
            ],

        ]
    ]
];
