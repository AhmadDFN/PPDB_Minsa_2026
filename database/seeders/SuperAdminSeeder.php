<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed initial Super Administrator for development and system administration.
     * Credentials can be overridden via environment variables.
     */
    public function run(): void
    {
        $email = env('SUPERADMIN_EMAIL', 'admin@minsatama.sch.id');
        $password = env('SUPERADMIN_PASSWORD', 'SuperAdminM1Madiun2026!');

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Administrator PPDB',
                'password' => Hash::make($password),
                'account_type' => AccountType::ADMIN,
                'account_status' => AccountStatus::ACTIVE,
                'email_verified_at' => now(),
                'phone' => '0895352882228',
            ]
        );

        $admin->assignRole('superadmin');
    }
}
