<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * All permissions are scoped to the default 'web' guard.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Application administrative permissions
        $permissions = [
            'view_dashboard',
            'view_applicants',
            'export_reports',
            'verify_documents',
            'reopen_registration',
            'manage_admission_periods',
            'manage_programs',
            'manage_document_requirements',
            'manage_admin_users',
            'view_activity_logs',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // Role 1: superadmin (has all permissions)
        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $superadmin->syncPermissions(Permission::where('guard_name', 'web')->get());

        // Role 2: admin_ppdb (operational admission staff)
        $adminPpdb = Role::firstOrCreate(['name' => 'admin_ppdb', 'guard_name' => 'web']);
        $adminPpdb->syncPermissions([
            'view_dashboard',
            'view_applicants',
            'export_reports',
            'verify_documents',
            'reopen_registration',
        ]);
    }
}
