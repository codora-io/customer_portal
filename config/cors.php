<?php

return [
    'paths' => ['api/*'],
    'supports_credentials' => false,
    'allowed_origins' => ['http://localhost:59109', 'https://ccr-demo.web.app/'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'allowed_methods' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
];
