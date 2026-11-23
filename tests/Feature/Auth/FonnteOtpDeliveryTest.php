<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\OtpPurpose;
use App\Models\CommunicationSetting;
use App\Models\User;
use App\Services\Authentication\Delivery\ArrayOtpDelivery;
use App\Services\Authentication\Delivery\FonnteOtpDelivery;
use App\Services\Authentication\OtpDeliveryService;
use App\Services\Authentication\OtpService;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class FonnteOtpDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        Config::set('ppdb.otp.delivery_driver', 'fonnte');
        Config::set('services.fonnte.token', 'test-fonnte-secret-token-12345');
        Config::set('services.fonnte.url', 'https://api.fonnte.com/send');
        Config::set('services.fonnte.country_code', '62');
        Config::set('services.fonnte.connect_timeout', 5);
        Config::set('services.fonnte.timeout', 10);
        CommunicationSetting::query()->updateOrCreate(
            ['id' => 1],
            ['active_channel_id' => null, 'database_provider_mode_enabled' => false],
        );
    }

    public function test_1_activation_otp_sent_successfully_via_fonnte(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'target' => ['6281234567890'],
                'message' => 'message queued',
                'process' => 'processing',
            ], 200),
        ]);

        $user = User::factory()->create([
            'phone' => '081234567890',
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::PENDING_ACTIVATION,
        ]);

        $delivery = new FonnteOtpDelivery;
        $otpService = new OtpService($delivery);

        $result = $otpService->generateAndSend($user, OtpPurpose::ACTIVATION);

        $this->assertSame(6, strlen($result['plain_otp']));

        Http::assertSent(function (Request $request) use ($result) {
            return $request->url() === 'https://api.fonnte.com/send'
                && $request->hasHeader('Authorization', 'test-fonnte-secret-token-12345')
                && $request['target'] === '6281234567890'
                && str_contains($request['message'], $result['plain_otp'])
                && str_contains($request['message'], 'aktivasi akun Anda');
        });
    }

    public function test_2_password_recovery_otp_sent_successfully_via_fonnte(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'target' => ['6281234567890'],
                'message' => 'message queued',
            ], 200),
        ]);

        $user = User::factory()->create([
            'phone' => '081234567890',
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'phone_verified_at' => now(),
        ]);

        $delivery = new FonnteOtpDelivery;
        $otpService = new OtpService($delivery);

        $result = $otpService->generateAndSend($user, OtpPurpose::PASSWORD_RESET);

        Http::assertSent(function (Request $request) use ($result) {
            return $request['target'] === '6281234567890'
                && str_contains($request['message'], $result['plain_otp'])
                && str_contains($request['message'], 'pemulihan password akun PPDB');
        });
    }

    public function test_3_phone_number_is_properly_normalized_for_various_formats(): void
    {
        $testCases = [
            '081234567890' => '6281234567890',
            '6281234567890' => '6281234567890',
            '+6281234567890' => '6281234567890',
            '+62 812-3456-7890' => '6281234567890',
            '0812-3456-7890' => '6281234567890',
            '81234567890' => '6281234567890',
        ];

        foreach ($testCases as $input => $expected) {
            $this->assertSame($expected, FonnteOtpDelivery::normalizePhone($input));
        }
    }

    public function test_4_authorization_header_does_not_contain_bearer_prefix(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $user = User::factory()->create(['phone' => '081234567890']);
        $delivery = new FonnteOtpDelivery;

        $delivery->send('081234567890', '123456', OtpPurpose::ACTIVATION->value, $user);

        Http::assertSent(function (Request $request) {
            $header = $request->header('Authorization')[0] ?? '';

            return $header === 'test-fonnte-secret-token-12345'
                && ! str_starts_with($header, 'Bearer ');
        });
    }

    public function test_5_http_200_with_status_true_is_treated_as_successful(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true, 'process' => 'queued'], 200),
        ]);

        $user = User::factory()->create(['phone' => '081234567890']);
        $delivery = new FonnteOtpDelivery;

        $success = $delivery->send('081234567890', '123456', OtpPurpose::ACTIVATION->value, $user);

        $this->assertTrue($success);
    }

    public function test_6_http_200_with_status_false_is_treated_as_failure(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => false,
                'reason' => 'general failure',
            ], 200),
        ]);

        $user = User::factory()->create(['phone' => '081234567890']);
        $delivery = new FonnteOtpDelivery;

        $success = $delivery->send('081234567890', '123456', OtpPurpose::ACTIVATION->value, $user);

        $this->assertFalse($success);
    }

    public function test_7_invalid_token_is_handled_gracefully(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => false,
                'reason' => 'invalid token',
            ], 200),
        ]);

        $user = User::factory()->create(['phone' => '081234567890']);
        $delivery = new FonnteOtpDelivery;

        $success = $delivery->send('081234567890', '123456', OtpPurpose::ACTIVATION->value, $user);

        $this->assertFalse($success);
    }

    public function test_8_invalid_phone_number_is_handled_gracefully(): void
    {
        Http::fake();

        $user = User::factory()->create(['phone' => '123']);
        $delivery = new FonnteOtpDelivery;

        $success = $delivery->send('123', '123456', OtpPurpose::ACTIVATION->value, $user);

        $this->assertFalse($success);
        Http::assertNothingSent();
    }

    public function test_9_device_disconnected_is_handled_gracefully(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => false,
                'reason' => 'device disconnected',
            ], 200),
        ]);

        $user = User::factory()->create(['phone' => '081234567890']);
        $delivery = new FonnteOtpDelivery;

        $success = $delivery->send('081234567890', '123456', OtpPurpose::ACTIVATION->value, $user);

        $this->assertFalse($success);
    }

    public function test_10_insufficient_quota_is_handled_gracefully(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => false,
                'reason' => 'insufficient quota',
            ], 200),
        ]);

        $user = User::factory()->create(['phone' => '081234567890']);
        $delivery = new FonnteOtpDelivery;

        $success = $delivery->send('081234567890', '123456', OtpPurpose::ACTIVATION->value, $user);

        $this->assertFalse($success);
    }

    public function test_11_connection_timeout_is_handled_gracefully_without_unhandled_crash(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => fn () => throw new ConnectionException('cURL error 28: Operation timed out'),
        ]);

        $user = User::factory()->create(['phone' => '081234567890']);
        $delivery = new FonnteOtpDelivery;

        $success = $delivery->send('081234567890', '123456', OtpPurpose::ACTIVATION->value, $user);

        $this->assertFalse($success);
    }

    public function test_12_non_json_api_response_is_handled_gracefully(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response('<html>502 Bad Gateway</html>', 502),
        ]);

        $user = User::factory()->create(['phone' => '081234567890']);
        $delivery = new FonnteOtpDelivery;

        $success = $delivery->send('081234567890', '123456', OtpPurpose::ACTIVATION->value, $user);

        $this->assertFalse($success);
    }

    public function test_13_plaintext_otp_never_appears_in_logs(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        Log::spy();

        $secretPlainOtp = '876543';
        $user = User::factory()->create(['phone' => '081234567890']);
        $delivery = new FonnteOtpDelivery;

        $delivery->send('081234567890', $secretPlainOtp, OtpPurpose::ACTIVATION->value, $user);

        Log::shouldNotHaveReceived('info', function (string $message, array $context) use ($secretPlainOtp) {
            return str_contains($message, $secretPlainOtp)
                || in_array($secretPlainOtp, $context, true);
        });

        Log::shouldNotHaveReceived('error', function (string $message, array $context) use ($secretPlainOtp) {
            return str_contains($message, $secretPlainOtp)
                || in_array($secretPlainOtp, $context, true);
        });
    }

    public function test_14_fonnte_token_never_appears_in_logs(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => false, 'reason' => 'invalid token'], 200),
        ]);

        Log::spy();

        $secretToken = 'test-fonnte-secret-token-12345';
        $user = User::factory()->create(['phone' => '081234567890']);
        $delivery = new FonnteOtpDelivery;

        $delivery->send('081234567890', '123456', OtpPurpose::ACTIVATION->value, $user);

        Log::shouldNotHaveReceived('warning', function (string $message, array $context) use ($secretToken) {
            return str_contains($message, $secretToken)
                || in_array($secretToken, $context, true);
        });
    }

    public function test_15_rate_limit_resend_is_enforced(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $user = User::factory()->create([
            'phone' => '081234567890',
            'account_status' => AccountStatus::PENDING_ACTIVATION,
        ]);

        $otpService = new OtpService(new FonnteOtpDelivery);

        $throttleKey = 'resend-otp:'.$user->id.'|127.0.0.1';

        // Hit 3 times to exhaust limit
        $otpService->hitSendRateLimiter($throttleKey, 300);
        $otpService->hitSendRateLimiter($throttleKey, 300);
        $otpService->hitSendRateLimiter($throttleKey, 300);

        $this->assertTrue($otpService->isSendRateLimited($throttleKey, 3, 300));
        $this->assertGreaterThan(0, $otpService->sendRateLimitAvailableIn($throttleKey));
    }

    public function test_16_otp_expiry_duration_remains_five_minutes(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $user = User::factory()->create(['phone' => '081234567890']);
        $otpService = new OtpService(new FonnteOtpDelivery);

        $result = $otpService->generateAndSend($user, OtpPurpose::ACTIVATION);
        $token = $result['token'];

        $this->assertNotNull($token->expires_at);
        $diffMinutes = (int) round(Carbon::now()->diffInSeconds($token->expires_at) / 60);
        $this->assertSame(5, $diffMinutes);
    }

    public function test_17_maximum_five_verification_attempts_is_enforced(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $user = User::factory()->create(['phone' => '081234567890']);
        $otpService = new OtpService(new FonnteOtpDelivery);

        $result = $otpService->generateAndSend($user, OtpPurpose::ACTIVATION);

        for ($i = 0; $i < 5; $i++) {
            $verify = $otpService->verify($user, OtpPurpose::ACTIVATION, '000000');
            $this->assertFalse($verify['success']);
        }

        $token = $result['token']->fresh();
        $this->assertTrue($token->isExhausted());

        // Even with the correct OTP, verification is rejected once exhausted
        $retryCorrect = $otpService->verify($user, OtpPurpose::ACTIVATION, $result['plain_otp']);
        $this->assertFalse($retryCorrect['success']);
        $this->assertStringContainsString('Batas maksimal percobaan', $retryCorrect['message']);
    }

    public function test_18_otp_cross_purpose_reuse_is_rejected(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $user = User::factory()->create(['phone' => '081234567890']);
        $otpService = new OtpService(new FonnteOtpDelivery);

        // Generate activation OTP
        $result = $otpService->generateAndSend($user, OtpPurpose::ACTIVATION);

        // Attempt to verify the activation OTP for password reset
        $verify = $otpService->verify($user, OtpPurpose::PASSWORD_RESET, $result['plain_otp']);
        $this->assertFalse($verify['success']);
        $this->assertStringContainsString('tidak ditemukan', $verify['message']);
    }

    public function test_19_array_driver_remains_fully_functional_for_testing(): void
    {
        ArrayOtpDelivery::clear();
        $delivery = new ArrayOtpDelivery;
        $user = User::factory()->create(['phone' => '081234567890']);

        $sent = $delivery->send('081234567890', '123456', 'activation', $user);

        $this->assertTrue($sent);
        $this->assertSame('123456', ArrayOtpDelivery::latestOtpFor('081234567890'));
        $this->assertCount(1, ArrayOtpDelivery::all());
    }

    public function test_20_log_driver_is_not_an_automatic_fallback_when_fonnte_fails(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => false, 'reason' => 'device disconnected'], 200),
        ]);

        Config::set('ppdb.otp.delivery_driver', 'fonnte');

        $deliveryService = new OtpDeliveryService;
        $user = User::factory()->create(['phone' => '081234567890']);

        $otpService = new OtpService($deliveryService);

        // Expect RuntimeException indicating delivery failed, NOT silent fallback to log
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Kode OTP belum dapat dikirim');

        $otpService->generateAndSend($user, OtpPurpose::ACTIVATION);
    }
}
