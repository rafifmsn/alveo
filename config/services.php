<?php

return [
    'weatherapi' => [
        'key' => env('WEATHERAPI_KEY', ''),
        'base_url' => env('WEATHERAPI_BASE_URL', 'https://api.weatherapi.com/v1'),
        'cache_ttl' => (int) env('WEATHERAPI_CACHE_TTL', 2700),
    ],
];
