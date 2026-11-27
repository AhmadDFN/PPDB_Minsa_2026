<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use App\Notifications\AdminAccountPasswordNotification;
use App\Services\AdminConfigurationAuditService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserAccessManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_only_superadmin_can_manage_admin_accounts_and_applicants_are_hidden(): void
    {
        $superadmin = $this->admin('superadmin');
        $admin = $this->admin('admin_ppdb');
        User::factory()->applicant()->create(['email' => 'applicant@example.test']);
        $this->actingAs($superadmin)->get(route('admin.admin-users.index'))->assertOk()->assertDontSee('applicant@example.test');
        $this->actingAs($admin)->get(route('admin.admin-users.index'))->assertForbidden();
        $this->app['auth']->logout();
        $this->get(route('admin.admin-users.index'))->assertRedirect(route('admin.login'));
    }

    public function test_superadmin_can_create_pending_admin_with_fixed_role(): void
    {
        Notification::fake();
        $actor = $this->admin('superadmin');
        $this->actingAs($actor)->post(route('admin.admin-users.store'), ['name' => 'Admin Baru', 'email' => 'new-admin@example.test', 'phone' => '0812000000'])->assertRedirect();
        $user = User::where('email', 'new-admin@example.test')->firstOrFail();
        $this->assertSame(AccountType::ADMIN, $user->account_type);
        $this->assertSame(AccountStatus::PENDING_ACTIVATION, $user->account_status);
        $this->assertTrue($user->hasRole('admin_ppdb'));
        $this->assertFalse($user->hasRole('superadmin'));
        Notification::assertSentTo($user, AdminAccountPasswordNotification::class);
        $notification = Notification::sent($user, AdminAccountPasswordNotification::class)->first();
        $mail = $notification->toMail($user);
        $this->assertStringContainsString('/admin/password/reset/', (string) $mail->actionUrl);
        $this->assertStringContainsString('email='.urlencode($user->email), (string) $mail->actionUrl);
    }

    public function test_admin_invitation_token_can_activate_pending_account_and_login(): void
    {
        Notification::fake();
        $actor = $this->admin('superadmin');
        $this->actingAs($actor)->post(route('admin.admin-users.store'), ['name' => 'Aktivasi Admin', 'email' => 'activate@example.test'])->assertRedirect();
        $user = User::where('email', 'activate@example.test')->firstOrFail();
        $notification = Notification::sent($user, AdminAccountPasswordNotification::class)->first();
        $reflection = new \ReflectionProperty($notification, 'token');
        $reflection->setAccessible(true);
        $token = $reflection->getValue($notification);

        $this->app['auth']->logout();
        $this->get(route('admin.password.reset', ['token' => $token, 'email' => $user->email]))->assertOk();
        $this->post(route('admin.password.reset.store'), ['token' => $token, 'email' => $user->email, 'password' => 'NewSecurePassword123!', 'password_confirmation' => 'NewSecurePassword123!'])->assertRedirect(route('admin.login'));
        $this->assertSame(AccountStatus::ACTIVE, $user->fresh()->account_status);
        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertDatabaseHas('admin_configuration_audit_logs', ['entity_type' => 'admin_user', 'entity_id' => $user->id, 'action' => 'password_reset_completed']);
    }

    public function test_invalid_admin_password_token_is_rejected_and_applicant_is_not_eligible(): void
    {
        $this->get(route('admin.password.reset', ['token' => 'invalid', 'email' => 'missing@example.test']))->assertRedirect(route('admin.login'));
        $applicant = User::factory()->applicant()->create(['email' => 'applicant-reset@example.test']);
        $this->post(route('admin.password.reset.store'), ['token' => 'invalid', 'email' => $applicant->email, 'password' => 'NewSecurePassword123!', 'password_confirmation' => 'NewSecurePassword123!'])->assertSessionHasErrors('email');
    }

    public function test_admin_can_be_toggled_but_self_and_last_superadmin_are_protected(): void
    {
        $superadmin = $this->admin('superadmin');
        $target = $this->admin('admin_ppdb');
        $this->actingAs($superadmin)->post(route('admin.admin-users.toggle', $target))->assertSessionHas('success');
        $this->assertSame(AccountStatus::SUSPENDED, $target->fresh()->account_status);
        $this->actingAs($target->fresh())->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->actingAs($superadmin)->post(route('admin.admin-users.toggle', $superadmin))->assertSessionHas('error');
        $this->actingAs($superadmin)->post(route('admin.admin-users.toggle', $superadmin))->assertSessionHas('error');
    }

    public function test_permission_update_is_allowlisted_and_superadmin_role_is_protected(): void
    {
        $superadmin = $this->admin('superadmin');
        $role = Role::findByName('admin_ppdb', 'web');
        $permission = $role->permissions->first();
        $this->actingAs($superadmin)->put(route('admin.roles.permissions.update', $role), ['permissions' => [$permission->id]])->assertRedirect();
        $this->assertCount(1, $role->fresh()->permissions);
        $root = Role::findByName('superadmin', 'web');
        $this->actingAs($superadmin)->get(route('admin.roles.permissions.edit', $root))->assertForbidden();
    }

    public function test_password_reset_uses_notification_without_exposing_token(): void
    {
        Notification::fake();
        $superadmin = $this->admin('superadmin');
        $target = $this->admin('admin_ppdb');
        $this->actingAs($superadmin)->post(route('admin.admin-users.password-reset', $target))->assertSessionHas('success');
        $this->assertDatabaseHas('admin_configuration_audit_logs', ['entity_type' => 'admin_user', 'action' => 'password_reset_requested']);
    }

    public function test_password_reset_request_respects_broker_throttle(): void
    {
        Notification::fake();
        $superadmin = $this->admin('superadmin');
        $target = $this->admin('admin_ppdb');
        $this->actingAs($superadmin)->post(route('admin.admin-users.password-reset', $target))->assertSessionHas('success');
        $this->actingAs($superadmin)->post(route('admin.admin-users.password-reset', $target))->assertSessionHas('error');
        $this->assertCount(1, Notification::sent($target, AdminAccountPasswordNotification::class));
    }

    public function test_duplicate_email_and_profile_idor_are_rejected_and_updates_are_audited(): void
    {
        $superadmin = $this->admin('superadmin');
        $target = $this->admin('admin_ppdb');
        $other = $this->admin('admin_ppdb');
        $this->actingAs($superadmin)->post(route('admin.admin-users.store'), ['name' => 'Duplicate', 'email' => $target->email])->assertSessionHasErrors('email');
        $this->actingAs($superadmin)->put(route('admin.admin-users.update', $other), ['name' => 'Updated Admin', 'email' => $other->email])->assertRedirect();
        $this->assertDatabaseHas('admin_configuration_audit_logs', ['entity_type' => 'admin_user', 'entity_id' => $other->id, 'action' => 'updated']);
        $applicant = User::factory()->applicant()->create(['email' => 'id-or@example.test']);
        $this->actingAs($superadmin)->get(route('admin.admin-users.edit', $applicant))->assertNotFound();
    }

    public function test_invitation_resend_is_pending_only_and_rate_limited(): void
    {
        Notification::fake();
        $superadmin = $this->admin('superadmin');
        $pending = User::factory()->admin()->pendingActivation()->create(['email' => 'pending-resend@example.test']);
        $pending->assignRole('admin_ppdb');
        $active = $this->admin('admin_ppdb');
        $this->actingAs($superadmin)->post(route('admin.admin-users.invitation-resend', $pending))->assertSessionHas('success');
        $this->actingAs($superadmin)->post(route('admin.admin-users.invitation-resend', $pending))->assertSessionHas('error');
        $this->actingAs($superadmin)->post(route('admin.admin-users.invitation-resend', $active))->assertSessionHas('error');
    }

    public function test_audit_failure_rolls_back_profile_update(): void
    {
        $superadmin = $this->admin('superadmin');
        $target = $this->admin('admin_ppdb');
        $this->mock(AdminConfigurationAuditService::class, function ($mock): void {
            $mock->shouldReceive('record')->andThrow(new \RuntimeException('audit unavailable'));
        });
        $this->actingAs($superadmin)->put(route('admin.admin-users.update', $target), ['name' => 'Should Rollback', 'email' => $target->email])->assertSessionHas('error');
        $this->assertSame($target->name, $target->fresh()->name);
    }

    private function admin(string $role): User
    {
        $user = User::factory()->admin()->create(['email' => $role.'-access-'.(User::query()->count() + 1).'@example.test']);
        $user->assignRole($role);

        return $user;
    }
}
