<?php


use ExprAs\Rbac\Entity\Role;

return [
    'exprass_admin' => [
        'resource_mappings' => [
            'roles' => [
                'entity'               => Role::class,
                'spec' => [
                    'name'                 => 'roles',
                    'recordRepresentation' => 'label',
                    'priority'             => 1000,
                    'form'                 => [
                        'inputs' => [
                            [
                                'source'    => 'parent',
                                'type'      => 'reference',
                                'reference' => 'roles',
                                'required'  => false,
                                'label'     => 'Родитель',
                                'child'     => [
                                    'optionText' => 'label'
                                ]
                            ],
                            [
                                'source'   => 'role_name',
                                'required' => true,
                                'label'    => 'Системное название'
                            ],
                            [
                                'source'   => 'label',
                                'required' => true,
                                'label'    => 'Название людское'
                            ],
                        ]
                    ],
                ]
            ],


        ]
    ]
];
