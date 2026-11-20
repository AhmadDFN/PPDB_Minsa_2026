<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\OtpPurpose;
use App\Models\User;
use App\Services\Authentication\Delivery\ArrayOtpDelivery;
use App\Services\Authentication\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OtpActivationTest extends TestCase
{
    use RefreshDatabase;

    protected OtpService $otpService;

    protected function setUp(): void
    {
        parent::setUp();
        ArrayOtpDelivery::clear();
        $this->otpService = app(OtpService::class);
    }

    public function test_cannot_view_otp_screen_without_activation_session(): void
    {
        $response = $this->get(route('activation.form'));

        $response->assertRedirect(route('register'));
        $response->assertSessionHas('error');
    }

    public function test_active_user_cannot_view_otp_screen_and_is_redirected_to_login(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
            'phone_verified_at' => now(),
        ]);

        $response = $this->withSession(['activation_user_id' => $user->id])
            ->get(route('activation.form'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('info');
    }

    public function test_can_view_otp_screen_with_valid_activation_session(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
            'account_status' => AccountStatus::PENDING_ACTIVATION,
            'phone_verified_at' => null,
        ]);

        $response = $this->withSession(['activation_user_id' => $user->id])
            ->get(route('activation.form'));

        $response->assertStatus(200);
        $response->assertSee('Verifikasi Nomor WhatsApp');
        $response->assertSee('0812*****890');
    }

    public function test_valid_otp_activates_account_and_dispatches_temporary_password_via_whatsapp(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
            'account_status' => AccountStatus::PENDING_ACTIVATION,
            'phone_verified_at' => null,
            'account_type' => AccountType::APPLICANT,
        ]);

        $otpData = $this->otpService->generateAndSend($user, OtpPurpose::ACTIVATION);

        $response = $this->withSession(['activation_user_id' => $user->id])
            ->post(route('activation.verify'), [
                'otp' => $otpData['plain_otp'],
            ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        // Verify user was activated
        $user->refresh();
        $this->assertNotNull($user->phone_verified_at);
        $this->assertSame(AccountStatus::ACTIVE, $user->account_status);
        $this->assertTrue($user->must_change_password);
        $this->assertNull($user->password_changed_at);

        // Verify token is marked verified
        $token = $otpData['token']->fresh();
        $this->assertTrue($token->isVerified());

        // Verify temporary password was dispatched
        $latestPw = ArrayOtpDelivery::latestPasswordFor('081234567890');
        $this->assertNotNull($latestPw);
        $this->assertSame(10, strlen($latestPw));
        $this->assertTrue(Hash::check($latestPw, $user->password));

        // Session activation_user_id cleared and activated_user_id set
        $this->assertNull(session('activation_user_id'));
        $this->assertSame($user->id, session('activated_user_id'));
    }

    public function test_invalid_otp_is_rejected(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
            'account_status' => AccountStatus::PENDING_ACTIVATION,
            'phone_verified_at' => null,
        ]);

        $this->otpService->generateAndSend($user, OtpPurpose::ACTIVATION);

        $response = $this->withSession(['activation_user_id' => $user->id])
            ->post(route('activation.verify'), [
                'otp' => '999999',
            ]);

        $response->assertSessionHasErrors(['otp']);

        $user->refresh();
        $this->assertNull($user->phone_verified_at);
        $this->assertSame(AccountStatus::PENDING_ACTIVATION, $user->account_status);
    }

    public function test_legacy_password_creation_route_redirects_to_login(): void
    {
        $response = $this->get(route('activation.password.form'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('info');
    }

    public function test_resending_otp_dispatches_new_code_and_invalidates_old_code(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
            'account_status' => AccountStatus::PENDING_ACTIVATION,
            'phone_verified_at' => null,
        ]);

        $firstOtp = $this->otpService->generateAndSend($user, OtpPurpose::ACTIVATION);

        $response = $this->withSession(['activation_user_id' => $user->id])
            ->post(route('activation.resend'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Second OTP must exist and differ
        $secondDispatched = ArrayOtpDelivery::latestOtpFor('081234567890');
        $this->assertNotNull($secondDispatched);

        // Verify old OTP was expired
        $firstToken = $firstOtp['token']->fresh();
        $this->assertTrue($firstToken->isExpired());
    }

    public function test_can_resend_temporary_password_to_verified_applicant(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
            'account_status' => AccountStatus::ACTIVE,
            'phone_verified_at' => now(),
            'account_type' => AccountType::APPLICANT,
            'must_change_password' => true,
            'password' => Hash::make('OldTemp1234'),
        ]);

        $response = $this->withSession(['activated_user_id' => $user->id])
            ->post(route('activation.resend_password'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $newPw = ArrayOtpDelivery::latestPasswordFor('081234567890');
        $this->assertNotNull($newPw);
        $this->assertNotSame('OldTemp1234', $newPw);
        $this->assertTrue(Hash::check($newPw, $user->password));
        $this->assertFalse(Hash::check('OldTemp1234', $user->password));
    }
}
