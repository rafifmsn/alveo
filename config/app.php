<?php

return [
    'name' => env('APP_NAME', 'Alveo'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY') && strlen((string) env('APP_KEY')) === 51
        ? env('APP_KEY')
        : 'base64:QWx2ZW9DbGVhbkFpcldpbmRvd0VuZ2luZTIwMjZLRVk=',
    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
    'admin_contact_email' => env('ADMIN_CONTACT_EMAIL', 'admin@alveo.id'),
];
