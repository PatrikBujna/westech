<?php

declare(strict_types=1);

/**
 * Application configuration.
 */

return [
    'app_env' => getenv('APP_ENV') ?: 'prod',

    'request' => [
        'max_body_bytes' => 262144,
    ],

    'pagination' => [
        'default_per_page' => 20,
        'max_per_page'     => 100,
    ],

    'auth' => [
        'token' => getenv('APP_AUTH_TOKEN') ?: '',
    ],

    'database' => [
        'host'                 => getenv('DB_HOST') ?: '127.0.0.1',
        'name'                 => getenv('DB_NAME') ?: 'westech',
        'user'                 => getenv('DB_USER') ?: 'westech',
        'password'             => getenv('DB_PASSWORD') ?: '',
        'duplicate_entry_code' => 1062,
    ],

    'product_source' => [
        'default'            => 'local',
        'dummyjson_base_url' => 'https://dummyjson.com',
        'dummyjson_limit'    => 10,
        'local_dataset'      => dirname(__DIR__) . '/src/Service/Source/data/products_seed.json',
    ],

    'product' => [
        'default_vat_rate' => '23.00',
        'fallback_brand'   => 'Unknown',
    ],
];
