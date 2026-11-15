<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\AccountType;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SpatiePermissionSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /**
     * Verify that all roles are bound to the 'web' guard.
     */
    public function test_all_roles_use_web_guard(): void
    {
        $roles = Role::all();

        $this->assertGreaterThan(0, $roles->count());

        foreach ($roles as $role) {
            $this->assertEquals('web', $role->guard_name, "Role {$role->name} must use the web guard.");
        }
    }

    /**
     * Verify that all permissions are bound to the 'web' guard.
     */
    public function test_all_permissions_use_web_guard(): void
    {
        $permissions = Permission::all();

        $this->assertGreaterThan(0, $permissions->count());

        foreach ($permissions as $permission) {
            $this->assertEquals('web', $permission->guard_name, "Permission {$permission->name} must use the web guard.");
        }
    }

    /**
     * Verify that User model HasRoles trait works correctly.
     */
    public function test_admin_user_can_be_assigned_roles_and_permissions(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertFalse($admin->hasRole('admin_ppdb'));

        $admin->assignRole('admin_ppdb');

        $this->assertTrue($admin->hasRole('admin_ppdb'));
        $this->assertTrue($admin->can('verify_documents'));
        $this->assertTrue($admin->can('view_dashboard'));

        // admin_ppdb does not have user management permission
        $this->assertFalse($admin->can('manage_admin_users'));
    }

    /**
     * Verify superadmin has all permissions.
     */
    public function test_superadmin_has_all_permissions(): void
    {
        $superadmin = User::factory()->admin()->create();
        $superadmin->assignRole('superadmin');

        $this->assertTrue($superadmin->can('manage_admin_users'));
        $this->assertTrue($superadmin->can('manage_admission_periods'));
        $this->assertTrue($superadmin->can('reopen_registration'));
    }

    /**
     * Verify that applicant accounts do NOT receive administrative roles.
     */
    public function test_applicant_accounts_have_no_administrative_roles(): void
    {
        $applicantUser = User::factory()->applicant()->create();

        $this->assertEquals(AccountType::APPLICANT, $applicantUser->account_type);
        $this->assertEmpty($applicantUser->roles);
        $this->assertFalse($applicantUser->hasRole('superadmin'));
        $this->assertFalse($applicantUser->hasRole('admin_ppdb'));
        $this->assertFalse($applicantUser->can('view_dashboard'));
        $this->assertFalse($applicantUser->can('verify_documents'));
    }

    /**
     * Verify that RoleAndPermissionSeeder is idempotent.
     */
    public function test_role_and_permission_seeder_is_idempotent(): void
    {
        $initialRoleCount = Role::count();
        $initialPermCount = Permission::count();

        // Run seeder a second time
        $this->seed(RoleAndPermissionSeeder::class);

        $this->assertEquals($initialRoleCount, Role::count());
        $this->assertEquals($initialPermCount, Permission::count());
    }

    /**
     * Verify SuperAdminSeeder is idempotent.
     */
    public function test_superadmin_seeder_is_idempotent(): void
    {
        $this->seed(SuperAdminSeeder::class);
        $countAfterFirst = User::where('account_type', AccountType::ADMIN->value)->count();

        $this->seed(SuperAdminSeeder::class);
        $countAfterSecond = User::where('account_type', AccountType::ADMIN->value)->count();

        $this->assertEquals($countAfterFirst, $countAfterSecond);
    }
}
