<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:5173','dorja.io','https://dorja.io','https://www.dorja.io','app.dorja.io','https://app.dorja.io','https://www.app.dorja.io','http://app.dorja.io','http://www.app.dorja.io'], 
    'allowed_headers' => ['*'],
    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
