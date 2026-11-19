<?php

declare(strict_types=1);

namespace App\Services\Authentication\Delivery;

use App\Contracts\OtpDeliveryInterface;
use App\Enums\OtpPurpose;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FonnteOtpDelivery implements OtpDeliveryInterface
{
    /**
     * Send OTP verification code to the recipient WhatsApp number using Fonnte API.
     */
    public function send(string $phone, string $otp, string $purpose, User $user): bool
    {
        $token = config('services.fonnte.token');

        if (empty($token) || ! is_string($token)) {
            Log::error('Fonnte OTP delivery failed: FONNTE_TOKEN is not configured.', [
                'user_id' => $user->id,
                'purpose' => $purpose,
            ]);

            return false;
        }

        $url = (string) config('services.fonnte.url', 'https://api.fonnte.com/send');
        $countryCode = (string) config('services.fonnte.country_code', '62');
        $connectTimeout = (int) config('services.fonnte.connect_timeout', 5);
        $timeout = (int) config('services.fonnte.timeout', 10);
        $sslVerify = (bool) config('services.fonnte.ssl_verify', true);

        $normalizedPhone = self::normalizePhone($phone);

        if (! self::isValidIndonesianPhone($normalizedPhone)) {
            Log::warning('Fonnte OTP delivery rejected: Invalid phone number format.', [
                'user_id' => $user->id,
                'purpose' => $purpose,
                'phone_masked' => self::maskPhone($phone),
            ]);

            return false;
        }

        $message = self::buildMessage($otp, $purpose);

        try {
            $client = Http::asForm()
                ->withHeaders([
                    'Authorization' => $token,
                ])
                ->connectTimeout($connectTimeout)
                ->timeout($timeout);

            if (! $sslVerify) {
                $client->withoutVerifying();
            }

            $response = $client->post($url, [
                'target' => $normalizedPhone,
                'message' => $message,
                'countryCode' => $countryCode,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (! is_array($data)) {
                    Log::error('Fonnte OTP delivery failed: Response is not valid JSON.', [
                        'user_id' => $user->id,
                        'purpose' => $purpose,
                        'phone_masked' => self::maskPhone($phone),
                        'http_status' => $response->status(),
                    ]);

                    return false;
                }

                $status = $data['status'] ?? null;
                $isSuccess = ($status === true || $status === 'true' || (is_string($status) && strtolower($status) === 'true'));

                if ($isSuccess) {
                    Log::info('Fonnte OTP message accepted by provider.', [
                        'user_id' => $user->id,
                        'purpose' => $purpose,
                        'phone_masked' => self::maskPhone($phone),
                        'process' => $data['process'] ?? null,
                    ]);

                    return true;
                }

                $reason = $data['reason'] ?? $data['message'] ?? 'Unknown provider rejection';

                Log::warning('Fonnte OTP delivery rejected by provider.', [
                    'user_id' => $user->id,
                    'purpose' => $purpose,
                    'phone_masked' => self::maskPhone($phone),
                    'reason' => (string) $reason,
                ]);

                return false;
            }

            Log::error('Fonnte OTP delivery failed with HTTP error.', [
                'user_id' => $user->id,
                'purpose' => $purpose,
                'phone_masked' => self::maskPhone($phone),
                'http_status' => $response->status(),
            ]);

            return false;
        } catch (ConnectionException) {
            // Ambiguous condition: connection timed out or network failed.
            // Do NOT retry automatically to avoid duplicate messages.
            Log::warning('Fonnte OTP delivery connection timed out or unreachable. Message status ambiguous.', [
                'user_id' => $user->id,
                'purpose' => $purpose,
                'phone_masked' => self::maskPhone($phone),
            ]);

            return false;
        } catch (Throwable) {
            Log::error('Fonnte OTP delivery encountered unexpected exception.', [
                'user_id' => $user->id,
                'purpose' => $purpose,
                'phone_masked' => self::maskPhone($phone),
            ]);

            return false;
        }
    }

    /**
     * Normalize phone numbers to consistent Indonesian format starting with 62.
     * Handles 08..., 628..., +628..., 8...
     */
    public static function normalizePhone(string|int $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone) ?? '';

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    /**
     * Validate Indonesian WhatsApp phone number format.
     */
    public static function isValidIndonesianPhone(string $normalizedPhone): bool
    {
        // Must start with 628 and have 10 to 16 total digits
        return (bool) preg_match('/^628[0-9]{8,13}$/', $normalizedPhone);
    }

    /**
     * Mask phone number for secure logging without exposing sensitive PII.
     */
    public static function maskPhone(string $phone): string
    {
        $clean = preg_replace('/\D/', '', $phone) ?? '';
        $len = strlen($clean);

        if ($len <= 6) {
            return '****';
        }

        return substr($clean, 0, 4).'****'.substr($clean, -3);
    }

    /**
     * Build localized professional message template based on purpose.
     */
    public static function buildMessage(string $otp, string $purpose): string
    {
        if ($purpose === OtpPurpose::PASSWORD_RESET->value || $purpose === 'password_reset') {
            return "PPDB ONLINE MIN 1 KOTA MADIUN\n\nAssalamu'alaikum.\n\nKode OTP untuk pemulihan kata sandi akun Anda:\n\n{$otp}\n\nKode berlaku selama 5 menit.\nJangan berikan kode ini kepada siapa pun.\n\nApabila Anda tidak merasa melakukan permintaan ini, abaikan pesan ini.\n\nTerima kasih.\nPPDB MIN 1 KOTA MADIUN";
        }

        return "PPDB ONLINE MIN 1 KOTA MADIUN\n\nAssalamu'alaikum.\n\nKode OTP untuk aktivasi akun PPDB Anda:\n\n{$otp}\n\nKode berlaku selama 5 menit.\nJangan berikan kode ini kepada siapa pun.\n\nApabila Anda tidak merasa melakukan permintaan ini, abaikan pesan ini.\n\nTerima kasih.\nPPDB MIN 1 KOTA MADIUN";
    }
}
