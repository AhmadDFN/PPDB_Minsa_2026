<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\Applicant;
use App\Models\User;
use App\Support\NikManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ApplicantLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login:applicant:*');
    }

    public function test_can_view_applicant_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Masuk Akun Calon Siswa');
        $response->assertSee('NIK Calon Siswa');
    }

    public function test_active_applicant_can_login_with_valid_nik_and_password(): void
    {
        $nik = '3573010101190001';
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'nik_lookup_hash' => NikManager::hash($nik),
            'nik_encrypted' => NikManager::encrypt($nik),
            'password' => Hash::make('Minsatama2026!'),
        ]);
        Applicant::factory()->create(['user_id' => $user->id]);

        $response = $this->post(route('login.store'), [
            'nik' => $nik,
            'password' => 'Minsatama2026!',
        ]);

        $response->assertRedirect(route('applicant.dashboard'));
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_login_rejects_pending_activation_account_and_redirects_to_activation(): void
    {
        $nik = '3573010101190002';
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::PENDING_ACTIVATION,
            'nik_lookup_hash' => NikManager::hash($nik),
            'nik_encrypted' => NikManager::encrypt($nik),
            'password' => Hash::make('Minsatama2026!'),
        ]);

        $response = $this->post(route('login.store'), [
            'nik' => $nik,
            'password' => 'Minsatama2026!',
        ]);

        $response->assertRedirect(route('activation.form'));
        $response->assertSessionHas('info');
        $this->assertSame($user->id, session('activation_user_id'));
        $this->assertGuest('web');
    }

    public function test_login_rejects_suspended_account(): void
    {
        $nik = '3573010101190003';
        User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::SUSPENDED,
            'nik_lookup_hash' => NikManager::hash($nik),
            'nik_encrypted' => NikManager::encrypt($nik),
            'password' => Hash::make('Minsatama2026!'),
        ]);

        $response = $this->post(route('login.store'), [
            'nik' => $nik,
            'password' => 'Minsatama2026!',
        ]);

        $response->assertSessionHasErrors(['nik']);
        $this->assertGuest('web');
    }

    public function test_login_rejects_wrong_password_with_generic_error(): void
    {
        $nik = '3573010101190004';
        User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'nik_lookup_hash' => NikManager::hash($nik),
            'password' => Hash::make('Benar12345'),
        ]);

        $response = $this->post(route('login.store'), [
            'nik' => $nik,
            'password' => 'SalahTotal',
        ]);

        $response->assertSessionHasErrors(['nik']);
        $this->assertGuest('web');
    }

    public function test_login_rejects_non_existent_nik_with_generic_error(): void
    {
        $response = $this->post(route('login.store'), [
            'nik' => '3573999999999999',
            'password' => 'SembarangPassword',
        ]);

        $response->assertSessionHasErrors(['nik']);
        $this->assertGuest('web');
    }

    public function test_rate_limiter_throttles_excessive_failed_applicant_logins(): void
    {
        $nik = '3573010101190005';
        User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'nik_lookup_hash' => NikManager::hash($nik),
            'password' => Hash::make('Benar12345'),
        ]);

        // Attempt 5 wrong logins
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), [
                'nik' => $nik,
                'password' => 'SalahPassword',
            ]);
        }

        // 6th attempt should be throttled
        $response = $this->post(route('login.store'), [
            'nik' => $nik,
            'password' => 'SalahPassword',
        ]);

        $response->assertSessionHasErrors(['nik']);
        $error = session('errors')->first('nik');
        $this->assertStringContainsString('Terlalu banyak percobaan masuk', $error);
    }

    public function test_applicant_logout_terminates_session(): void
    {
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $response = $this->actingAs($user, 'web')->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest('web');
    }
}
