<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    
    'allowed_origins_patterns' => [
        '/^https:\/\/.*\.managesuite\.xyz$/',

    ],

    'allowed_headers' => ['*'],
    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
]; 