<?php

declare(strict_types=1);

$approvedConsentStatement = 'Saya selaku orang tua/wali menyatakan bahwa seluruh data yang diisikan adalah benar dan dapat dipertanggungjawabkan.';
$isProduction = env('APP_ENV', 'production') === 'production';

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
        'disk' => 'ppdb_private',
        'base_path' => 'ppdb/documents',
        'pdf_path' => 'private/ppdb/pdfs',
        'global_max_size_kb' => 5120,
    ],

    /*
    |--------------------------------------------------------------------------
    | Registration Submission
    |--------------------------------------------------------------------------
    | The number format uses the configured prefix, academic year, period
    | program code, and a sequence scoped to the academic year and program.
    */
    'registration' => [
        'number_prefix' => env('PPDB_REGISTRATION_NUMBER_PREFIX', 'PPDB'),
        'number_format' => '{prefix}/{academic_year}/{program_code}/{sequence}',
        'program_codes' => ['REGULER', 'UNGGULAN'],
        'sequence_padding' => 5,
        'consent_version' => env('PPDB_CONSENT_VERSION', $isProduction ? '' : 'v1.0'),
        'consent_statement' => env(
            'PPDB_CONSENT_STATEMENT',
            $isProduction ? '' : $approvedConsentStatement
        ),
    ],
];
