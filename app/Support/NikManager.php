<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;
use RuntimeException;

class NikManager
{
    /**
     * Normalize NIK by trimming and stripping non-numeric characters.
     * Validates that NIK contains exactly 16 numeric digits.
     *
     * @throws InvalidArgumentException
     */
    public static function normalize(string $nik): string
    {
        $normalized = preg_replace('/\D/', '', trim($nik));

        if (strlen($normalized) !== 16) {
            throw new InvalidArgumentException('NIK harus terdiri dari tepat 16 digit angka.');
        }

        return $normalized;
    }

    /**
     * Compute deterministic HMAC-SHA256 lookup hash using dedicated secret key.
     * Requires NIK_HMAC_KEY to be configured and distinct from APP_KEY.
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public static function hash(string $nik): string
    {
        $normalized = self::normalize($nik);

        $key = config('ppdb.nik_hmac_key');
        if (empty($key) || ! is_string($key)) {
            throw new RuntimeException('Kunci rahasia NIK_HMAC_KEY belum dikonfigurasi. Harap tentukan pada berkas .env.');
        }

        $appKey = config('app.key');
        if (! empty($appKey) && $key === $appKey) {
            throw new RuntimeException('NIK_HMAC_KEY tidak boleh identik dengan APP_KEY. Gunakan kunci rahasia terpisah.');
        }

        return hash_hmac('sha256', $normalized, $key);
    }

    /**
     * Encrypt normalized NIK using Laravel Crypt (AES-256-CBC).
     *
     * @throws InvalidArgumentException
     */
    public static function encrypt(string $nik): string
    {
        $normalized = self::normalize($nik);

        return Crypt::encryptString($normalized);
    }

    /**
     * Decrypt encrypted NIK string.
     */
    public static function decrypt(string $encryptedNik): string
    {
        return Crypt::decryptString($encryptedNik);
    }

    /**
     * Mask NIK for privacy in user interfaces (e.g. 3573********0001).
     * Format: first 4 digits, 8 asterisks, last 4 digits.
     */
    public static function mask(string $nik): string
    {
        $clean = preg_replace('/\D/', '', $nik);

        if (strlen($clean) !== 16) {
            return '****************';
        }

        return substr($clean, 0, 4).'********'.substr($clean, 12, 4);
    }
}
