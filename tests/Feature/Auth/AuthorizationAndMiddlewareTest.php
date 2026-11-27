<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationAndMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_unauthenticated_user_cannot_access_applicant_dashboard(): void
    {
        $response = $this->get(route('applicant.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_applicant_cannot_access_admin_dashboard(): void
    {
        $applicant = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
        ]);
        Applicant::factory()->create(['user_id' => $applicant->id]);

        $response = $this->actingAs($applicant, 'web')->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_applicant_dashboard(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $admin->assignRole('admin_ppdb');

        $response = $this->actingAs($admin, 'web')->get(route('applicant.dashboard'));

        $response->assertStatus(403);
    }

    public function test_suspended_user_is_logged_out_by_active_middleware(): void
    {
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::SUSPENDED,
        ]);

        $response = $this->actingAs($user, 'web')->get(route('applicant.dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest('web');
    }

    public function test_pending_activation_user_is_logged_out_by_active_middleware(): void
    {
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::PENDING_ACTIVATION,
        ]);

        $response = $this->actingAs($user, 'web')->get(route('applicant.dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest('web');
    }
}
