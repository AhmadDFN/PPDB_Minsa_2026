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
            'manage_system_settings',
            'manage_communication_settings',
            'view_activity_logs',
            // Interview module permissions
            'interview.manage_master',
            'manage_interview_settings',
            'approve_registration_queue',
            'view_interview_recaps',
            'override_interview_scores',
            'conduct_interview_stage_1',
            'conduct_interview_stage_2',
            'conduct_interview_stage_3',
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
            'approve_registration_queue',
            'view_interview_recaps',
            'override_interview_scores',
        ]);

        // Role 3: interviewer_stage_1 (examiner for stage 1: BTQ/Iqro)
        $interviewer1 = Role::firstOrCreate(['name' => 'interviewer_stage_1', 'guard_name' => 'web']);
        $interviewer1->syncPermissions(['conduct_interview_stage_1']);

        // Role 4: interviewer_stage_2 (examiner for stage 2: Calistung)
        $interviewer2 = Role::firstOrCreate(['name' => 'interviewer_stage_2', 'guard_name' => 'web']);
        $interviewer2->syncPermissions(['conduct_interview_stage_2']);

        // Role 5: interviewer_stage_3 (examiner for stage 3: Khusus Unggulan)
        $interviewer3 = Role::firstOrCreate(['name' => 'interviewer_stage_3', 'guard_name' => 'web']);
        $interviewer3->syncPermissions(['conduct_interview_stage_3']);
    }
}
