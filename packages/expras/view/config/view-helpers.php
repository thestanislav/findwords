<?php

/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/22/2017
 * Time: 18:07
 */

use ExprAs\Core\ServiceManager\ServiceContainerInitializer;
use Laminas\View\Helper\Doctype;

return [
    'view_helpers' => [
        'invokables'   => [
            \ExprAs\View\Helper\FileSize::class => \ExprAs\View\Helper\FileSize::class,
            'imageThumb'                             => \ExprAs\View\Helper\ImageThumb::class,
            'truncate'                               => \ExprAs\View\Helper\Truncate::class,
            'truncateHtml'                           => \ExprAs\View\Helper\TruncateHtml::class,
            'routeMatch'                             => \ExprAs\View\Helper\RouteMatch::class,
            'nlToP'                                  => \ExprAs\View\Helper\NlToP::class,
            'flash'                                  => \ExprAs\View\Helper\Flash::class
        ],
        'aliases'      => [
            'profiledPaginator' => \ExprAs\View\Helper\ProfiledPaginationControl::class,
            'service'           => \ExprAs\View\Helper\Service::class,
            'truncate'          => 'Truncate',
            'truncateHtml'      => 'TruncateHtml',
            'fileSize'          => \ExprAs\View\Helper\FileSize::class
        ],
        'factories'    => [
            \ExprAs\View\Helper\ProfiledPaginationControl::class => \ExprAs\Core\ServiceManager\Factory\ContainerInvokableFactory::class,
            \ExprAs\View\Helper\Service::class                   => \ExprAs\Core\ServiceManager\Factory\ContainerInvokableFactory::class,
            'initializers' => [
                ServiceContainerInitializer::class
            ],
        ],


    ],

    'view_helper_config' => [
        'doctype' => Doctype::HTML5,
    ]

];
