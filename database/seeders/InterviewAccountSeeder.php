<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InterviewAccountSeeder extends Seeder
{
    /**
     * Seed default administrative and interviewer accounts for PPDB v2.
     */
    public function run(): void
    {
        $defaultPassword = env('DEFAULT_ADMIN_PASSWORD');
        if (! is_string($defaultPassword) || $defaultPassword === '') {
            throw new \RuntimeException('DEFAULT_ADMIN_PASSWORD wajib ditetapkan sebelum menjalankan seeder.');
        }
        $hashedPassword = Hash::make($defaultPassword);

        // 1. Admin PPDB (3 accounts)
        $adminAccounts = [
            ['name' => 'Panitia Verifikator 1', 'email' => 'admin1@minsatama.sch.id', 'phone' => '081234567811'],
            ['name' => 'Panitia Verifikator 2', 'email' => 'admin2@minsatama.sch.id', 'phone' => '081234567812'],
            ['name' => 'Panitia Verifikator 3', 'email' => 'admin3@minsatama.sch.id', 'phone' => '081234567813'],
        ];

        foreach ($adminAccounts as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => $hashedPassword,
                    'account_type' => AccountType::ADMIN,
                    'account_status' => AccountStatus::ACTIVE,
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'must_change_password' => false,
                ]
            );
            $user->syncRoles(['admin_ppdb']);
        }

        // 2. Pewawancara Tahap 1: BTQ / Iqro' (3 accounts)
        $stage1Accounts = [
            ['name' => 'Pewawancara BTQ 1', 'email' => 'penguji1a@minsatama.sch.id', 'phone' => '081234567821'],
            ['name' => 'Pewawancara BTQ 2', 'email' => 'penguji1b@minsatama.sch.id', 'phone' => '081234567822'],
            ['name' => 'Pewawancara BTQ 3', 'email' => 'penguji1c@minsatama.sch.id', 'phone' => '081234567823'],
        ];

        foreach ($stage1Accounts as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => $hashedPassword,
                    'account_type' => AccountType::ADMIN,
                    'account_status' => AccountStatus::ACTIVE,
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'must_change_password' => false,
                ]
            );
            $user->syncRoles(['interviewer_stage_1']);
        }

        // 3. Pewawancara Tahap 2: Calistung Sederhana (3 accounts)
        $stage2Accounts = [
            ['name' => 'Pewawancara Calistung 1', 'email' => 'penguji2a@minsatama.sch.id', 'phone' => '081234567831'],
            ['name' => 'Pewawancara Calistung 2', 'email' => 'penguji2b@minsatama.sch.id', 'phone' => '081234567832'],
            ['name' => 'Pewawancara Calistung 3', 'email' => 'penguji2c@minsatama.sch.id', 'phone' => '081234567833'],
        ];

        foreach ($stage2Accounts as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => $hashedPassword,
                    'account_type' => AccountType::ADMIN,
                    'account_status' => AccountStatus::ACTIVE,
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'must_change_password' => false,
                ]
            );
            $user->syncRoles(['interviewer_stage_2']);
        }

        // 4. Pewawancara Tahap 3: Khusus Program Unggulan (3 accounts)
        $stage3Accounts = [
            ['name' => 'Pewawancara Khusus Unggulan 1', 'email' => 'penguji3a@minsatama.sch.id', 'phone' => '081234567841'],
            ['name' => 'Pewawancara Khusus Unggulan 2', 'email' => 'penguji3b@minsatama.sch.id', 'phone' => '081234567842'],
            ['name' => 'Pewawancara Khusus Unggulan 3', 'email' => 'penguji3c@minsatama.sch.id', 'phone' => '081234567843'],
        ];

        foreach ($stage3Accounts as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => $hashedPassword,
                    'account_type' => AccountType::ADMIN,
                    'account_status' => AccountStatus::ACTIVE,
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'must_change_password' => false,
                ]
            );
            $user->syncRoles(['interviewer_stage_3']);
        }
    }
}
