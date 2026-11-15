<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\OtpPurpose;
use App\Models\User;
use App\Services\Authentication\Delivery\ArrayOtpDelivery;
use App\Services\Authentication\OtpDeliveryService;
use App\Services\Authentication\OtpService;
use App\Support\NikManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected OtpService $otpService;

    protected function setUp(): void
    {
        parent::setUp();
        ArrayOtpDelivery::clear();
        $this->otpService = new OtpService(new OtpDeliveryService(new ArrayOtpDelivery));
    }

    public function test_can_view_forgot_password_page(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Pemulihan Kata Sandi');
        $response->assertSee('NIK Calon Siswa Terdaftar');
    }

    public function test_submitting_registered_nik_with_verified_phone_issues_otp(): void
    {
        $nik = '3573010101190001';
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'phone' => '081234567890',
            'phone_verified_at' => now(),
            'nik_lookup_hash' => NikManager::hash($nik),
        ]);

        $response = $this->post(route('password.send'), ['nik' => $nik]);

        $response->assertRedirect(route('password.reset.verify.form'));
        $response->assertSessionHas('success');

        $otp = ArrayOtpDelivery::latestOtpFor('081234567890');
        $this->assertNotNull($otp);
        $this->assertSame($user->id, session('password_reset_user_id'));
    }

    public function test_submitting_non_existent_nik_returns_generic_message_without_enumerating(): void
    {
        $response = $this->post(route('password.send'), [
            'nik' => '3573999999999999',
        ]);

        $response->assertRedirect(route('password.reset.verify.form'));
        $response->assertSessionHas('info');

        // No OTP should be sent
        $this->assertEmpty(ArrayOtpDelivery::all());
    }

    public function test_submitting_account_with_unverified_phone_returns_warning(): void
    {
        $nik = '3573010101190002';
        User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'phone' => '081234567890',
            'phone_verified_at' => null, // Unverified
            'nik_lookup_hash' => NikManager::hash($nik),
        ]);

        $response = $this->post(route('password.send'), ['nik' => $nik]);

        $response->assertSessionHas('warning');
        $this->assertEmpty(ArrayOtpDelivery::all());
    }

    public function test_verifying_recovery_otp_advances_to_reset_password_form(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
            'phone_verified_at' => now(),
        ]);

        $otpData = $this->otpService->generateAndSend($user, OtpPurpose::PASSWORD_RESET);

        $response = $this->withSession(['password_reset_user_id' => $user->id])
            ->post(route('password.reset.verify'), [
                'otp' => $otpData['plain_otp'],
            ]);

        $response->assertRedirect(route('password.reset.form'));
        $this->assertSame($user->id, session('password_reset_verified_user_id'));
        $this->assertNull(session('password_reset_user_id'));
    }

    public function test_setting_new_password_updates_user_password_and_enables_login(): void
    {
        $nik = '3573010101190003';
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'phone_verified_at' => now(),
            'nik_lookup_hash' => NikManager::hash($nik),
            'password' => Hash::make('KataSandiLama123'),
        ]);

        $response = $this->withSession(['password_reset_verified_user_id' => $user->id])
            ->post(route('password.reset.store'), [
                'password' => 'KataSandiBaru2026!',
                'password_confirmation' => 'KataSandiBaru2026!',
            ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('KataSandiBaru2026!', $user->password));

        // Test login with the newly set password
        $loginResponse = $this->post(route('login.store'), [
            'nik' => $nik,
            'password' => 'KataSandiBaru2026!',
        ]);

        $loginResponse->assertRedirect(route('applicant.dashboard'));
        $this->assertAuthenticatedAs($user, 'web');
    }
}
