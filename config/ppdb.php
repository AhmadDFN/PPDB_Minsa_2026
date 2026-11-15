<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | NIK Privacy & Lookup Security
    |--------------------------------------------------------------------------
    | Dedicated HMAC-SHA256 secret key for deterministic NIK hashing.
    | Must be configured in .env and distinct from APP_KEY. No hardcoded fallback.
    */
    'nik_hmac_key' => env('NIK_HMAC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | One-Time Password (OTP) Configuration
    |--------------------------------------------------------------------------
    */
    'otp' => [
        'hmac_key' => env('OTP_HMAC_KEY'),
        'length' => (int) env('OTP_LENGTH', 6),
        'expiry_minutes' => (int) env('OTP_EXPIRY_MINUTES', 5),
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
        'delivery_driver' => env('OTP_DELIVERY_DRIVER', 'log'), // 'log' for dev, 'null', or custom provider
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Provider Configuration
    |--------------------------------------------------------------------------
    */
    'sms' => [
        'provider' => env('SMS_PROVIDER', 'log'), // 'log' for dev, or third party provider
        'api_key' => env('SMS_API_KEY', ''),
        'sender_id' => env('SMS_SENDER_ID', 'MIN1MADIUN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Storage Configuration
    |--------------------------------------------------------------------------
    */
    'documents' => [
        'disk' => 'local', // Must be private local storage, never public
        'base_path' => 'private/ppdb/documents',
        'pdf_path' => 'private/ppdb/pdfs',
        'max_file_size_kb' => 5120, // 5MB default max
    ],
];
