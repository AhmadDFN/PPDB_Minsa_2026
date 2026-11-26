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
        'delivery_driver' => env('OTP_DRIVER', env('OTP_DELIVERY_DRIVER', 'log')), // 'fonnte', 'log', 'array', 'null'
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
        'pdf_path' => 'ppdb/pdfs',
        'global_max_size_kb' => 5120,
    ],

    'pdf' => [
        'ministry_name' => 'Kementerian Agama Republik Indonesia',
        'school_name' => 'MIN 1 Kota Madiun',
        'collection_instruction' => 'Cetak bukti pendaftaran ini dan serahkan ke loket PTSP MIN 1 Kota Madiun.',
    ],

    'school' => [
        'campus_one' => [
            // Official school directory NPSN 60720864 (Kemendikdasmen).
            'latitude' => -7.6591,
            'longitude' => 111.5146,
        ],
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
        'consent_version' => (string) (env('PPDB_CONSENT_VERSION') ?: ($isProduction ? '' : 'v1.0')),
        'consent_statement' => (string) (
            env('PPDB_CONSENT_STATEMENT') ?: ($isProduction ? '' : $approvedConsentStatement)
        ),
    ],
];
