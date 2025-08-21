<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */

    'version' => env('API_VERSION', 'v1'),
    
    'rate_limit' => env('API_RATE_LIMIT', 60),
    
    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],
    
    'upload' => [
        'max_size' => env('MAX_UPLOAD_SIZE', 5120), // KB
        'allowed_image_types' => explode(',', env('ALLOWED_IMAGE_TYPES', 'jpeg,png,jpg,webp')),
    ],
    
    'logging' => [
        'log_requests' => env('LOG_API_REQUESTS', true),
        'log_slow_queries' => env('LOG_SLOW_QUERIES', true),
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1000), // ms
    ],
];