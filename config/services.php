<?php

return [
    'visualcrossing' => [
        'key' => env('VISUALCROSSING_KEY', ''),
        'base_url' => env('VISUALCROSSING_BASE_URL', 'https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services'),
        'cache_ttl' => (int) env('VISUALCROSSING_CACHE_TTL', 3600),
    ],
];
