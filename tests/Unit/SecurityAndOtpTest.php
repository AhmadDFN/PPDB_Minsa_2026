<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\OtpPurpose;
use App\Models\User;
use App\Models\UserOtpToken;
use App\Support\NikManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class SecurityAndOtpTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test NIK normalization and stripping non-numeric characters.
     */
    public function test_nik_normalization(): void
    {
        $input = ' 3573-0101-0118-0001 ';
        $normalized = NikManager::normalize($input);

        $this->assertEquals('3573010101180001', $normalized);
    }

    /**
     * Test NIK with less or more than 16 digits throws InvalidArgumentException.
     */
    public function test_invalid_nik_length_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        NikManager::normalize('123456');
    }

    public function test_too_long_nik_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        NikManager::normalize('35730101011800019999');
    }

    /**
     * Test deterministic NIK HMAC hashing.
     */
    public function test_nik_hashing_is_deterministic(): void
    {
        $nik = '3573010101180001';
        $hash1 = NikManager::hash($nik);
        $hash2 = NikManager::hash($nik);

        $this->assertEquals($hash1, $hash2);
        $this->assertEquals(64, strlen($hash1));
    }

    /**
     * Test missing NIK_HMAC_KEY throws RuntimeException (no hardcoded fallback).
     */
    public function test_missing_hmac_secret_key_throws_exception(): void
    {
        Config::set('ppdb.nik_hmac_key', null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NIK_HMAC_KEY belum dikonfigurasi');

        NikManager::hash('3573010101180001');
    }

    /**
     * Test identical APP_KEY and NIK_HMAC_KEY is forbidden (requires separate secrets).
     */
    public function test_identical_app_key_and_hmac_key_is_forbidden(): void
    {
        $sharedKey = 'base64:sharedSecretKeyThatMustNotBeReused123=';
        Config::set('app.key', $sharedKey);
        Config::set('ppdb.nik_hmac_key', $sharedKey);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NIK_HMAC_KEY tidak boleh identik dengan APP_KEY');

        NikManager::hash('3573010101180001');
    }

    /**
     * Test NIK encryption and decryption round trip.
     */
    public function test_nik_encryption_and_decryption(): void
    {
        $nik = '3573010101180001';
        $encrypted = NikManager::encrypt($nik);

        $this->assertNotEquals($nik, $encrypted);
        $this->assertEquals($nik, NikManager::decrypt($encrypted));
    }

    /**
     * Test NIK masking for UI presentation (e.g. 3573********0001).
     */
    public function test_nik_masking(): void
    {
        $nik = '3573010101180001';
        $masked = NikManager::mask($nik);

        $this->assertEquals('3573********0001', $masked);
        $this->assertEquals(16, strlen($masked));
    }

    /**
     * Test User model hides nik_encrypted and nik_lookup_hash in serialization.
     */
    public function test_user_model_never_exposes_nik_in_serialization(): void
    {
        $nik = '3573010101180001';
        $user = User::factory()->create([
            'nik_lookup_hash' => NikManager::hash($nik),
            'nik_encrypted' => NikManager::encrypt($nik),
        ]);

        $array = $user->toArray();
        $json = $user->toJson();

        $this->assertArrayNotHasKey('nik_encrypted', $array);
        $this->assertArrayNotHasKey('nik_lookup_hash', $array);
        $this->assertArrayNotHasKey('password', $array);

        $this->assertStringNotContainsString($nik, $json);
        $this->assertStringNotContainsString($user->nik_lookup_hash, $json);
    }

    /**
     * Test OTP token verification with valid code.
     */
    public function test_otp_token_successful_verification(): void
    {
        $user = User::factory()->create();

        $token = UserOtpToken::create([
            'user_id' => $user->id,
            'phone' => '081234567890',
            'purpose' => OtpPurpose::ACTIVATION,
            'otp_hash' => Hash::make('654321'),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertTrue($token->isValid());
        $this->assertTrue($token->verify('654321'));
        $this->assertTrue($token->fresh()->isVerified());
    }

    /**
     * Test OTP token failed verification increments attempts.
     */
    public function test_otp_token_failed_verification_increments_attempts(): void
    {
        $user = User::factory()->create();

        $token = UserOtpToken::create([
            'user_id' => $user->id,
            'phone' => '081234567890',
            'purpose' => OtpPurpose::ACTIVATION,
            'otp_hash' => Hash::make('654321'),
            'attempts' => 0,
            'max_attempts' => 3,
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertFalse($token->verify('000000'));
        $this->assertEquals(1, $token->fresh()->attempts);
        $this->assertFalse($token->fresh()->isVerified());
    }

    /**
     * Test expired OTP is rejected.
     */
    public function test_expired_otp_is_rejected(): void
    {
        $user = User::factory()->create();

        $token = UserOtpToken::create([
            'user_id' => $user->id,
            'phone' => '081234567890',
            'purpose' => OtpPurpose::ACTIVATION,
            'otp_hash' => Hash::make('654321'),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->subMinute(),
        ]);

        $this->assertTrue($token->isExpired());
        $this->assertFalse($token->isValid());
        $this->assertFalse($token->verify('654321'));
    }

    /**
     * Test exhausted OTP attempts is rejected.
     */
    public function test_exhausted_otp_is_rejected(): void
    {
        $user = User::factory()->create();

        $token = UserOtpToken::create([
            'user_id' => $user->id,
            'phone' => '081234567890',
            'purpose' => OtpPurpose::ACTIVATION,
            'otp_hash' => Hash::make('654321'),
            'attempts' => 5,
            'max_attempts' => 5,
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertTrue($token->isExhausted());
        $this->assertFalse($token->isValid());
        $this->assertFalse($token->verify('654321'));
    }
}
