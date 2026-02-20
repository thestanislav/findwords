<?php

use ExprAs\Nutgram\Mezzio\Middleware\ValidateWebAppUser;

return [
    'middleware_pipeline' => [
        [
            'middleware' => ValidateWebAppUser::class,
        ],
    ],
];