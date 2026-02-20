<?php

use App\View\Helper\HilightSyllables;
use App\View\Helper\LinkelizeTerms;

return [
    'view_helpers' => [
        'invokables' => [
            'HilightSyllables' => HilightSyllables::class,
            'LinkelizeTerms' => LinkelizeTerms::class
        ]
        ],
    'view_placeholder_reset_helpers' => [
        'DictionaryNavigation' => 'removePages'
    ]

];