<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\OtpPurpose;
use App\Models\User;
use App\Models\UserOtpToken;
use App\Services\Authentication\Delivery\ArrayOtpDelivery;
use App\Services\Authentication\OtpDeliveryService;
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
        $this->otpService = new OtpService(new OtpDeliveryService(new ArrayOtpDelivery));
    }

    public function test_cannot_view_otp_screen_without_session(): void
    {
        $response = $this->get(route('activation.form'));

        $response->assertRedirect(route('register'));
        $response->assertSessionHas('error');
    }

    public function test_can_view_otp_screen_with_valid_session(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
            'account_status' => AccountStatus::PENDING_ACTIVATION,
        ]);

        $response = $this->withSession(['activation_user_id' => $user->id])
            ->get(route('activation.form'));

        $response->assertStatus(200);
        $response->assertSee('Verifikasi Nomor WhatsApp');
        $response->assertSee('0812*****890');
    }

    public function test_valid_otp_verifies_phone_and_advances_to_password_creation(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
            'account_status' => AccountStatus::PENDING_ACTIVATION,
            'phone_verified_at' => null,
        ]);

        $otpData = $this->otpService->generateAndSend($user, OtpPurpose::ACTIVATION);

        $response = $this->withSession(['activation_user_id' => $user->id])
            ->post(route('activation.verify'), [
                'otp' => $otpData['plain_otp'],
            ]);

        $response->assertRedirect(route('activation.password.form'));
        $response->assertSessionHas('success');

        // Verify phone_verified_at was stamped
        $user->refresh();
        $this->assertNotNull($user->phone_verified_at);

        // Verify token is marked verified
        $token = $otpData['token']->fresh();
        $this->assertTrue($token->isVerified());

        // Verify session advanced
        $this->assertSame($user->id, session('activation_verified_user_id'));
        $this->assertNull(session('activation_user_id'));
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
    }

    public function test_cannot_view_password_creation_without_verified_session(): void
    {
        $response = $this->get(route('activation.password.form'));

        $response->assertRedirect(route('register'));
        $response->assertSessionHas('error');
    }

    public function test_setting_password_activates_account_and_sets_active_status(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
            'account_status' => AccountStatus::PENDING_ACTIVATION,
            'phone_verified_at' => now(),
            'account_type' => AccountType::APPLICANT,
        ]);

        $response = $this->withSession(['activation_verified_user_id' => $user->id])
            ->post(route('activation.password.store'), [
                'password' => 'RahasiaMinsatama2026',
                'password_confirmation' => 'RahasiaMinsatama2026',
            ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame(AccountStatus::ACTIVE, $user->account_status);
        $this->assertTrue(Hash::check('RahasiaMinsatama2026', $user->password));

        // Session key cleared
        $this->assertNull(session('activation_verified_user_id'));
    }

    public function test_setting_password_requires_minimum_8_characters_and_confirmation(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::PENDING_ACTIVATION,
            'phone_verified_at' => now(),
        ]);

        // Too short (< 8)
        $shortResponse = $this->withSession(['activation_verified_user_id' => $user->id])
            ->post(route('activation.password.store'), [
                'password' => '12345',
                'password_confirmation' => '12345',
            ]);
        $shortResponse->assertSessionHasErrors(['password']);

        // Mismatched confirmation
        $mismatchResponse = $this->withSession(['activation_verified_user_id' => $user->id])
            ->post(route('activation.password.store'), [
                'password' => 'Rahasia12345',
                'password_confirmation' => 'Beda1234567',
            ]);
        $mismatchResponse->assertSessionHasErrors(['password']);
    }

    public function test_resending_otp_dispatches_new_code_and_invalidates_old_code(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
            'account_status' => AccountStatus::PENDING_ACTIVATION,
        ]);

        $first = $this->otpService->generateAndSend($user, OtpPurpose::ACTIVATION);

        $response = $this->withSession(['activation_user_id' => $user->id])
            ->post(route('activation.resend'));

        $response->assertSessionHas('success');

        $firstToken = $first['token']->fresh();
        $this->assertTrue($firstToken->isExpired());

        $newestToken = UserOtpToken::where('user_id', $user->id)
            ->latest('id')
            ->first();
        $this->assertNotSame($firstToken->id, $newestToken->id);
    }
}
