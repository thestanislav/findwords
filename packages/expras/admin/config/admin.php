<?php

use ExprAs\Admin\Handler\AdminLayoutHandler;
use ExprAs\Admin\Handler\AuthenticationHandler;
use ExprAs\CKEditor\Middleware\CkEditorHandler;
use ExprAs\ElFinder\Middleware\ConnectorMiddleware;
use ExprAs\ElFinder\Middleware\ProxyStaticMiddleware;
use ExprAs\KCFinder\Middleware\KCFinderMiddleware;
use ExprAs\Rest\Hydrator\Configurator\DefaultHydratorConfigurator;
use ExprAs\Admin\HydratorConfigurator;

/**
 * Admin Configuration Schema
 * This file defines the structure for admin.php configuration
 */

/**
 * @typedef InputValidation
 * @property string $name One of: "maxLength", "minLength", "regex", "email", "number", "required", "custom", "min", "max"
 * @property mixed[] $args
 */

/**
 * @typedef Choice
 * @property mixed $id
 * @property string $name
 */

/**
 * @typedef FormInput
 * @property string $source
 * @property string $label
 * @property string $type One of: "reference", "text", "textarea", "number", "select", "toOne", "toMany", "date", "datetime", "boolean", "file", "image", "password", "html", "rich-text", "array", "radio", "autocomplete"
 * @property bool $required
 * @property bool $fullWidth
 * @property string $helperText
 * @property string $placeholder
 * @property string $format
 * @property string $parse
 * @property InputValidation[] $validate
 * @property string $reference
 * @property string $target
 * @property Choice[] $choices
 * @property string $optionText
 * @property string $optionValue
 * @property bool $multiple
 * @property mixed $defaultValue
 * @property bool $disabled
 * @property array<string,mixed> $sx
 * @property bool $translateChoice
 * @property string[] $suggestions
 * @property int $debounce
 * @property mixed $emptyValue
 * @property string $emptyText
 * @property bool $resettable
 * @property string $transform
 * @property bool $autoFocus
 * @property bool $isLoading
 */

/**
 * @typedef ListComponent
 * @property string $type
 * @property string $source
 * @property bool $sortable
 * @property string $label
 * @property string $sortBy
 * @property string $textAlign One of: "inherit", "left", "center", "right", "justify"
 * @property string $headerClassName
 * @property string $cellClassName
 * @property string $emptyText
 * @property bool $translateChoice
 * @property Choice[] $choices
 * @property array<string,mixed> $sx
 */

/**
 * @typedef ListFields
 * @property string|string[] $primarySource
 * @property string|string[] $secondarySource
 * @property string|string[] $tertiarySource
 * @property ListComponent[] $components
 */

/**
 * @typedef Filter
 * @property string $source
 * @property string $type
 * @property string $label
 * @property bool $alwaysOn
 * @property mixed $defaultValue
 * @property array<string,mixed> $sx
 */

/**
 * @typedef AdvanceFilterField
 * @property string $title
 * @property string $name
 * @property string $type
 */

/**
 * @typedef FilterListItem
 * @property string $label
 * @property string $icon
 * @property mixed $value
 * @property string[] $isSelected
 * @property string[] $toggleFilter
 */

/**
 * @typedef FilterList
 * @property string $label
 * @property string $icon
 * @property FilterListItem[] $items
 */

/**
 * @typedef Action
 * @property string $name
 * @property string $label
 * @property string $icon
 * @property string $onClick
 * @property array<string,mixed> $sx
 */

/**
 * @typedef AsidePanel
 * @property string $type
 * @property array<string,mixed> $props
 */

/**
 * @typedef QueryOptions
 * @property int|bool $retry
 * @property int $staleTime
 * @property int $refetchInterval
 */

/**
 * @typedef MutationOptions
 * @property int|bool $retry
 * @property string $onSuccess
 * @property string $onError
 */

/**
 * @typedef ResourceMapping
 * @property class-string $entity
 * @property string $name
 * @property string[] $excludeFields
 * @property string $icon
 * @property array{
 *   options?: array{
 *     label?: string,
 *     recordRepresentation?: string|callable
 *   },
 *   form?: array{
 *     inputs: FormInput[]
 *   },
 *   list?: array{
 *     fields?: ListFields,
 *     filters?: Filter[],
 *     advanceFilter?: array{
 *       enabled: bool,
 *       fields: AdvanceFilterField[]
 *     },
 *     filterList?: array{
 *       lists?: FilterList[],
 *       savedQueriesList?: bool,
 *       filterLiveSearch?: bool
 *     },
 *     expandPanel?: array{
 *       type?: string,
 *       content?: array<string,mixed>
 *     },
 *     sort?: array{field: string, order: "ASC"|"DESC"},
 *     perPage?: int,
 *     filter?: array<string,mixed>,
 *     disableFuzzySearch?: bool,
 *     disableExport?: bool,
 *     disableFilter?: bool,
 *     disableCreate?: bool,
 *     disableDelete?: bool,
 *     disableEdit?: bool,
 *     disableShow?: bool,
 *     disableClone?: bool,
 *     exporter?: string,
 *     empty?: string|array,
 *     actions?: (string|Action)[],
 *     aside?: AsidePanel,
 *     pagination?: array{
 *       perPage?: int,
 *       rowsPerPageOptions?: int[],
 *       sx?: array<string,mixed>
 *     },
 *     rowStyle?: string,
 *     rowClick?: "edit"|"show"|"expand"|"select"|string,
 *     bulkActionButtons?: bool|(string|array)[],
 *     queryOptions?: QueryOptions
 *   },
 *   edit?: array{
 *     title?: string,
 *     disableDelete?: bool,
 *     disableEditActions?: bool,
 *     actions?: (string|Action)[],
 *     aside?: AsidePanel,
 *     redirect?: string|bool,
 *     transform?: string,
 *     mutationMode?: "pessimistic"|"optimistic"|"undoable",
 *     mutationOptions?: MutationOptions,
 *     warnWhenUnsavedChanges?: bool
 *   },
 *   create?: array{
 *     title?: string,
 *     disableCreateActions?: bool,
 *     actions?: (string|Action)[],
 *     aside?: AsidePanel,
 *     redirect?: string|bool,
 *     transform?: string,
 *     mutationOptions?: MutationOptions,
 *     warnWhenUnsavedChanges?: bool
 *   },
 *   show?: array{
 *     title?: string,
 *     disableShowActions?: bool,
 *     actions?: (string|Action)[],
 *     aside?: AsidePanel,
 *     expand?: array{
 *       type: string,
 *       content?: array<string,mixed>
 *     }
 *   },
 *   dashboard?: array{
 *     widgets?: array<array{
 *       type: string,
 *       props?: array<mixed>,
 *       sx?: array<string,mixed>
 *     }>
 *   }
 * } $spec
 */

/**
 * @typedef Route
 * @property string $name
 * @property string $path
 * @property string $method
 * @property class-string[] $middleware
 * @property array $defaults
 * @property array $options
 */

/**
 * @typedef AdminConfig
 * @property Route[] $routes
 * @property array<string,ResourceMapping> $resource_mappings
 */

/**
 * @return array{exprass_admin: AdminConfig}
 */
return [
    'exprass_admin' => [
        'permissions'            => [
            'developer' => [
                [
                    'resource' => '*',
                ]
            ],
            'admin'     => [
                [
                    'resource' => '*',
                ],
                [
                    'type'     => 'deny',
                    'resource' => 'expras-admin-request-logs',
                ],
                [
                    'type'     => 'deny',
                    'resource' => 'app-entity-offermanager',
                    'action'   => '*',
                    'record'   => [
                        'username' => 'thestanislav'
                    ],
                ],
            ]
        ],
        'hydrator_configurators' => [
            [
                'priority'     => -1000,
                'configurator' => HydratorConfigurator\UploadedFieldConfigurator::class
            ],
            DefaultHydratorConfigurator::class,
            HydratorConfigurator\UserFieldsExcludeConfigurator::class,

        ],
        'basePath'               => '/.admin',

        'routes'                 => [
            [
                'name'            => 'exprass-admin',
                'path'            => '',
                'middleware'      => AdminLayoutHandler::class,
                'allowed_methods' => ['GET'],
                'options'         => [
                    'defaults' => [
                        'action' => 'index'
                    ]
                ]
            ],
            [
                'name'            => 'exprass-admin-resources',
                'path'            => '/resources',
                'middleware'      => [
                    AdminLayoutHandler::class
                ],
                'allowed_methods' => ['GET'],
                'options'         => [
                    'defaults' => [
                        'action' => 'resources'
                    ]
                ]
            ],
            [
                'name'            => 'exprass-admin-ping',
                'path'            => '/ping',
                'middleware'      => [
                    AdminLayoutHandler::class
                ],
                'allowed_methods' => ['GET'],
                'options'         => [
                    'defaults' => [
                        'action' => 'ping'
                    ]
                ]
            ],
            [
                'name'            => 'exprass-admin-login',
                'path'            => '/login',
                'middleware'      => [
                    AuthenticationHandler::class
                ],
                'allowed_methods' => ['POST', 'GET'],
                'options'         => [
                    'defaults' => [
                        'action' => 'login'
                    ]
                ]
            ],
            [
                'name'            => 'exprass-admin-logout',
                'path'            => '/logout',
                'middleware'      => [
                    AuthenticationHandler::class
                ],
                'allowed_methods' => ['GET'],
                'options'         => [
                    'defaults' => [
                        'action' => 'logout',
                        'logout' => true
                    ]
                ]
            ],
            [
                'name'            => 'exprass-admin-ckeditor',
                'path'            => '/ckeditor/{path:[\/\.a-zA-Z0-9_-]*}',
                'middleware'      => CkEditorHandler::class,
                'allowed_methods' => ['GET', 'POST'],
            ],
            [
                'name'            => 'exprass-admin-kcfinder',
                'path'            => '/kcfinder/{path:[\/\.a-zA-Z0-9_-]+}',
                'middleware'      => KCFinderMiddleware::class,
                'allowed_methods' => ['POST', 'GET'],
            ],
            [
                'name'            => 'elfinder-connector',
                'path'            => '/elfinder/connector',
                'middleware'      => [
                    ConnectorMiddleware::class
                ],
                'allowed_methods' => ['GET', 'PUT', 'POST'],
                'options'         => [
                    'defaults' => [
                        'action' => 'index'
                    ]
                ]
            ],

            [
                'name'            => 'elfinder-connector-assets',
                'path'            => '/elfinder/{path:[A-Za-z0-9\_\-\/\.]+}',
                'middleware'      => [
                    ProxyStaticMiddleware::class,
                ],
                'allowed_methods' => ['GET', 'HEAD'],
                'options'         => [
                    'defaults' => [
                        'action' => 'index'
                    ]
                ]
            ],
        ],
        'resource_mappings'      => [
            'expras-admin-request-logs' => [
                'priority' => -998,
                'entity'   => \ExprAs\Admin\Entity\AdminRequestLogEntity::class,
                'middleware' => \ExprAs\Admin\Handler\AdminRequestLogAdminHandler::class,
                'name'       => 'expras-admin-request-logs',
                'spec'     => [
                    'icon'    => 'AdminPanelSettings',
                    'show'    => false,
                    'options' => [
                        'label' => 'Журнал API запросов',
                    ],
                    'list'    => [
                        'title'   => 'Журнал API запросов',
                        'perPage' => 50,
                        'type'    => 'simpleList',
                        'sort'    => [
                            'field' => 'datetime',
                            'order' => 'DESC'
                        ],
                        'queryOptions' => [
                            'meta' => [
                                'extractRelations' => [
                                    'user'
                                ]
                            ]
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
                                'source'   => 'level',
                                'label'    => 'Уровень',
                                'alwaysOn' => true,
                                'choices'  => [
                                    ['id' => 100, 'name' => 'Debug'],
                                    ['id' => 200, 'name' => 'Info'],
                                    ['id' => 250, 'name' => 'Notice'],
                                    ['id' => 300, 'name' => 'Warning'],
                                    ['id' => 400, 'name' => 'Error'],
                                ]
                            ],
                            [
                                'type'   => 'text',
                                'source' => 'resource',
                                'label'  => 'Ресурс',
                            ],
                            [
                                'type'   => 'text',
                                'source' => 'action',
                                'label'  => 'Действие',
                            ],
                            [
                                'type'      => 'reference',
                                'source'    => 'user',
                                'label'     => 'Пользователь',
                                'reference' => 'user',
                                'alwaysOn' => true,
                                'child' => [
                                    [
                                        'source' => 'username'
                                    ]
                                ]
                            ],
                        ],
                        'linkType'        => 'show',
                        'primarySource'   => 'message',
                        'secondarySource' => ['datetime', 'user.username', 'resource', 'action'],
                        'tertiarySource'  => 'levelName',
                        'rowSx' => [
                            'record',
                            "
                                switch (record.level){
                                    case 400:
                                        return { backgroundColor: '#ff0000'};
                                    case 300:
                                        return { backgroundColor: '#ff2626'};
                                }
                            "
                        ]
                    ],
                ]
            ],
        ],
    ]
];
