<?php

use ExprAs\Uploadable\Entity\Uploaded;

return [
    'uploadable' => [
        'entity' => [
            'default' => Uploaded::class
        ],
        'store_content' => false
    ]
];
