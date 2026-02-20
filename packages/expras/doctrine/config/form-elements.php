<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 1/30/2018
 * Time: 17:27
 */

return [
    'form_elements' => [
        'aliases' => [
            'entitySelect' => \ExprAs\Crud\View\Helper\EntitySelect::class
        ],
        'factories' => [
            \ExprAs\Doctrine\Form\Element\EntitySelect::class => \DoctrineORMModule\Service\ObjectSelectFactory::class,
        ],
    ]
];
