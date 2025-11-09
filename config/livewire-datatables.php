<?php

return [
    'default_time_format' => 'H:i',
    'default_date_format' => 'd/m/Y',
    'suppress_search_highlights' => false, // When searching, don't highlight matching search results when set to true
    'per_page_options' => [10, 25, 50, 100],
    'default_per_page' => 10,
    'model_namespace' => 'App',
    'default_classes' => [
        'row' => [
            'even' => 'divide-x divide-base-300 text-sm text-base-content bg-base-200',
            'odd' => 'divide-x divide-base-300 text-sm text-base-content bg-base-100',
            'selected' => 'divide-x divide-base-300 text-sm text-base-content bg-yellow-100',
        ],
        'cell' => 'text-sm text-base-content',
    ],
];
