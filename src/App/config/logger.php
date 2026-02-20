<?php

use ExprAs\Logger\Processor\RequestData;
use Laminas\Log\Formatter\ErrorHandler;
use Laminas\Log\Processor\PsrPlaceholder;

return [
    'log' => [
        'expras_error_logger' => [
            'writers'    => [
                'error' => [
                    'name'      => 'doctrine',
                    'priority'  => 1,
                    'options'   => [
                        'entityManager' => \Doctrine\ORM\EntityManager::class,

                    ],
                ],
            ],
            'processors' => [
                'requestData' => [
                    'name'    =>  RequestData::class,
                    'priority' => -1
                ],

            ],
        ],
    ],
];