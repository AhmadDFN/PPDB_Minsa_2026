<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_can_view_admin_login_page(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('Portal Administrator');
        $response->assertSee('Alamat Email Administrator');
    }

    public function test_admin_with_valid_credentials_and_role_can_login(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'email' => 'admin@minsatama.sch.id',
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
            'password' => Hash::make('AdminPPDB2026!'),
        ]);
        $admin->assignRole('admin_ppdb');

        $response = $this->post(route('admin.login.store'), [
            'email' => 'admin@minsatama.sch.id',
            'password' => 'AdminPPDB2026!',
        ]);

        $response->assertRedirect(route('admin.preview'));
        $this->assertAuthenticatedAs($admin, 'web');
    }

    public function test_applicant_cannot_login_through_admin_login(): void
    {
        User::factory()->create([
            'email' => 'applicant@gmail.com',
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'password' => Hash::make('Applicant2026!'),
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => 'applicant@gmail.com',
            'password' => 'Applicant2026!',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('web');
    }

    public function test_admin_without_required_role_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'norole@minsatama.sch.id',
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
            'password' => Hash::make('NoRole2026!'),
        ]);
        // Do NOT assign any role

        $response = $this->post(route('admin.login.store'), [
            'email' => 'norole@minsatama.sch.id',
            'password' => 'NoRole2026!',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('web');
    }

    public function test_inactive_admin_cannot_login(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'email' => 'suspended@minsatama.sch.id',
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::SUSPENDED,
            'password' => Hash::make('Suspended2026!'),
        ]);
        $admin->assignRole('admin_ppdb');

        $response = $this->post(route('admin.login.store'), [
            'email' => 'suspended@minsatama.sch.id',
            'password' => 'Suspended2026!',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('web');
    }

    public function test_admin_with_wrong_password_is_rejected(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'email' => 'admin@minsatama.sch.id',
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
            'password' => Hash::make('Benar12345'),
        ]);
        $admin->assignRole('admin_ppdb');

        $response = $this->post(route('admin.login.store'), [
            'email' => 'admin@minsatama.sch.id',
            'password' => 'SalahPassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('web');
    }

    public function test_admin_logout_terminates_session(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $admin->assignRole('superadmin');

        $response = $this->actingAs($admin, 'web')->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('web');
    }
}
