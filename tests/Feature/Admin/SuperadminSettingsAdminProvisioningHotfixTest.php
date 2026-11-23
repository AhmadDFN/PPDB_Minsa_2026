<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\SystemSetting;
use App\Models\SystemSettingLog;
use App\Models\User;
use App\Services\AdminConfigurationAuditService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SuperadminSettingsAdminProvisioningHotfixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Cache::flush();
    }

    public function test_admin_creation_audit_failure_rolls_back_without_logging_credentials(): void
    {
        $actor = $this->admin('superadmin');
        Log::shouldReceive('error')->once()->withArgs(function (string $message, array $context): bool {
            return $message === 'Admin account provisioning transaction failed.'
                && ! array_key_exists('password', $context)
                && ! array_key_exists('token', $context)
                && ! array_key_exists('email', $context)
                && ! array_key_exists('phone', $context);
        });
        $this->mock(AdminConfigurationAuditService::class, function ($mock): void {
            $mock->shouldReceive('record')->once()->andThrow(new \RuntimeException('audit unavailable'));
        });

        $this->actingAs($actor)->post(route('admin.admin-users.store'), [
            'name' => 'Admin Rollback',
            'email' => 'rollback@example.test',
            'phone' => '0812000000',
        ])->assertSessionHas('error', 'Akun admin gagal dibuat.');

        $this->assertDatabaseMissing('users', ['email' => 'rollback@example.test']);
    }

    public function test_system_setting_update_and_history_are_atomic_and_cache_is_cleared_after_commit(): void
    {
        $actor = $this->admin('superadmin');
        SystemSetting::set('institution_display_name', 'Nama Lama', byUser: $actor);
        $this->assertSame('Nama Lama', SystemSetting::getInstitutionName());

        $this->actingAs($actor)->post(route('admin.settings.update'), ['institution_display_name' => 'Nama Baru'])->assertSessionHas('success');
        $this->assertSame('Nama Baru', SystemSetting::getInstitutionName());
        $this->assertDatabaseHas('system_setting_logs', ['setting_key' => 'institution_display_name', 'user_id' => $actor->id, 'old_value' => 'Nama Lama', 'new_value' => 'Nama Baru']);

        SystemSettingLog::creating(function (): void {
            throw new \RuntimeException('log unavailable');
        });
        $this->actingAs($actor)->post(route('admin.settings.update'), ['institution_display_name' => 'Tidak Tersimpan'])->assertSessionHas('error');
        SystemSettingLog::flushEventListeners();

        $this->assertSame('Nama Baru', SystemSetting::getInstitutionName());
        $this->assertDatabaseMissing('system_settings', ['value' => 'Tidak Tersimpan']);
        $this->assertDatabaseMissing('system_setting_logs', ['new_value' => 'Tidak Tersimpan']);
    }

    public function test_system_setting_html_is_stored_as_plain_text(): void
    {
        $actor = $this->admin('superadmin');
        $this->actingAs($actor)->post(route('admin.settings.update'), ['institution_display_name' => '<b>Nama Aman</b>'])->assertSessionHas('success');
        $this->assertSame('Nama Aman', SystemSetting::getInstitutionName());
    }

    public function test_new_admin_has_pending_admin_identity_and_hashed_password(): void
    {
        $actor = $this->admin('superadmin');
        $this->actingAs($actor)->post(route('admin.admin-users.store'), ['name' => 'Admin QA', 'email' => 'qa-admin@example.test'])->assertRedirect();
        $user = User::query()->where('email', 'qa-admin@example.test')->firstOrFail();
        $this->assertSame(AccountType::ADMIN, $user->account_type);
        $this->assertSame(AccountStatus::PENDING_ACTIVATION, $user->account_status);
        $this->assertTrue($user->must_change_password);
        $this->assertTrue($user->hasRole('admin_ppdb'));
        $this->assertFalse($user->hasRole('superadmin'));
        $this->assertNotSame('password', $user->password);
        $this->assertDatabaseHas('admin_configuration_audit_logs', ['entity_type' => 'admin_user', 'entity_id' => $user->id, 'action' => 'created']);
    }

    private function admin(string $role): User
    {
        $user = User::factory()->admin()->create(['email' => $role.'-hotfix-'.(User::query()->count() + 1).'@example.test']);
        $user->assignRole($role);

        return $user;
    }
}
