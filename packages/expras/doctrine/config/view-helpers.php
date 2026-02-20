<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 12.09.2018
 * Time: 12:58
 */


use ExprAs\Core\ServiceManager\Factory\EntityManagerInvokableFactory;
use ExprAs\Doctrine\View\Helper\SelectElement;

return [
    'view_helpers' => [
        'factories' => [
            SelectElement::class => EntityManagerInvokableFactory::class,

        ],
        'invokables' => [

        ],
        'aliases' => [
            'doctrineObjectSelectElement' => SelectElement::class,

        ]
    ]
];
