<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProgramSeeder::class,
            RoleAndPermissionSeeder::class,
            SuperAdminSeeder::class,
            AdmissionPeriodSeeder::class,
            InterviewAccountSeeder::class,
            InterviewMasterSeeder::class,
        ]);
    }
}
