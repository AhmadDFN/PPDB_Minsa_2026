<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\OtpPurpose;
use App\Models\User;
use App\Models\UserOtpToken;
use App\Services\Authentication\Delivery\ArrayOtpDelivery;
use App\Services\Authentication\OtpDeliveryService;
use App\Services\Authentication\OtpService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use RuntimeException;
use Tests\TestCase;

class OtpServiceSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected OtpService $otpService;

    protected function setUp(): void
    {
        parent::setUp();

        ArrayOtpDelivery::clear();
        $delivery = new OtpDeliveryService(new ArrayOtpDelivery);
        $this->otpService = new OtpService($delivery);
    }

    public function test_compute_hash_produces_deterministic_hmac_sha256(): void
    {
        $hash1 = OtpService::computeHash(1, 'account_activation', '123456', '081234567890');
        $hash2 = OtpService::computeHash(1, 'account_activation', '123456', '081234567890');

        $this->assertSame($hash1, $hash2);
        $this->assertSame(64, strlen($hash1)); // SHA256 hex is 64 characters
    }

    public function test_compute_hash_differs_when_context_changes(): void
    {
        $base = OtpService::computeHash(1, 'account_activation', '123456', '081234567890');

        // Different user ID
        $diffUser = OtpService::computeHash(2, 'account_activation', '123456', '081234567890');
        $this->assertNotSame($base, $diffUser);

        // Different purpose
        $diffPurpose = OtpService::computeHash(1, 'password_reset', '123456', '081234567890');
        $this->assertNotSame($base, $diffPurpose);

        // Different OTP code
        $diffCode = OtpService::computeHash(1, 'account_activation', '654321', '081234567890');
        $this->assertNotSame($base, $diffCode);

        // Different phone
        $diffPhone = OtpService::computeHash(1, 'account_activation', '123456', '081299998888');
        $this->assertNotSame($base, $diffPhone);
    }

    public function test_compute_hash_fails_closed_when_key_is_missing(): void
    {
        Config::set('ppdb.otp.hmac_key', null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('OTP_HMAC_KEY belum dikonfigurasi');

        OtpService::computeHash(1, 'account_activation', '123456', '081234567890');
    }

    public function test_compute_hash_fails_closed_when_key_equals_app_key(): void
    {
        $appKey = 'base64:identicalSecretKeyBetweenAppAndOtp12345';
        Config::set('app.key', $appKey);
        Config::set('ppdb.otp.hmac_key', $appKey);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('OTP_HMAC_KEY tidak boleh sama dengan APP_KEY');

        OtpService::computeHash(1, 'account_activation', '123456', '081234567890');
    }

    public function test_compute_hash_fails_closed_when_key_equals_nik_hmac_key(): void
    {
        $nikKey = 'identicalSecretKeyBetweenNikAndOtp1234567890';
        Config::set('ppdb.nik_hmac_key', $nikKey);
        Config::set('ppdb.otp.hmac_key', $nikKey);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('OTP_HMAC_KEY tidak boleh sama dengan NIK_HMAC_KEY');

        OtpService::computeHash(1, 'account_activation', '123456', '081234567890');
    }

    public function test_generate_and_send_dispatches_otp_and_invalidates_previous_tokens(): void
    {
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::PENDING_ACTIVATION,
            'phone' => '081234567890',
        ]);

        // First OTP
        $first = $this->otpService->generateAndSend($user, OtpPurpose::ACTIVATION);
        $firstTokenId = $first['token']->id;

        $this->assertSame(6, strlen($first['plain_otp']));
        $this->assertNotEmpty(ArrayOtpDelivery::latestOtpFor('081234567890'));

        // Issue second OTP for same purpose
        $second = $this->otpService->generateAndSend($user, OtpPurpose::ACTIVATION);
        $secondTokenId = $second['token']->id;

        $this->assertNotSame($firstTokenId, $secondTokenId);

        // First token must now be expired / invalidated
        $oldToken = UserOtpToken::find($firstTokenId);
        $this->assertTrue($oldToken->isExpired());
    }

    public function test_verify_otp_rejects_wrong_code_and_tracks_attempts(): void
    {
        $user = User::factory()->create(['phone' => '081234567890']);
        $otpData = $this->otpService->generateAndSend($user, OtpPurpose::ACTIVATION);

        $result = $this->otpService->verify($user, OtpPurpose::ACTIVATION, '000000');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Kode OTP tidak cocok', $result['message']);

        $token = $otpData['token']->fresh();
        $this->assertSame(1, $token->attempts);
        $this->assertNull($token->verified_at);
    }

    public function test_verify_otp_rejects_when_attempts_exhausted(): void
    {
        $user = User::factory()->create(['phone' => '081234567890']);
        $otpData = $this->otpService->generateAndSend($user, OtpPurpose::ACTIVATION);

        // Exhaust all 5 attempts
        for ($i = 0; $i < 5; $i++) {
            $this->otpService->verify($user, OtpPurpose::ACTIVATION, '000000');
        }

        $token = $otpData['token']->fresh();
        $this->assertTrue($token->isExhausted());

        // Even with correct code, must now be rejected
        $finalAttempt = $this->otpService->verify($user, OtpPurpose::ACTIVATION, $otpData['plain_otp']);
        $this->assertFalse($finalAttempt['success']);
        $this->assertStringContainsString('Batas maksimal percobaan', $finalAttempt['message']);
    }

    public function test_verify_otp_rejects_expired_token(): void
    {
        $user = User::factory()->create(['phone' => '081234567890']);
        $otpData = $this->otpService->generateAndSend($user, OtpPurpose::ACTIVATION);

        // Travel 6 minutes into future
        Carbon::setTestNow(Carbon::now()->addMinutes(6));

        $result = $this->otpService->verify($user, OtpPurpose::ACTIVATION, $otpData['plain_otp']);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('telah kedaluwarsa', $result['message']);

        Carbon::setTestNow(); // reset
    }

    public function test_verify_otp_rejects_replay_of_consumed_token(): void
    {
        $user = User::factory()->create(['phone' => '081234567890']);
        $otpData = $this->otpService->generateAndSend($user, OtpPurpose::ACTIVATION);

        // First verification: success
        $first = $this->otpService->verify($user, OtpPurpose::ACTIVATION, $otpData['plain_otp']);
        $this->assertTrue($first['success']);

        // Second verification with identical code: must be rejected
        $second = $this->otpService->verify($user, OtpPurpose::ACTIVATION, $otpData['plain_otp']);
        $this->assertFalse($second['success']);
        $this->assertStringContainsString('sudah digunakan sebelumnya', $second['message']);
    }
}
