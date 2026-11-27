<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\Applicant;
use App\Models\Registration;
use App\Models\User;
use App\Support\NikManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class BlankApplicantLiveTestSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Seeder ini hanya untuk lingkungan local atau testing.');
        }

        $period = AdmissionPeriod::where('is_active', true)->firstOrFail();
        $path = storage_path('app/private/live-test-blank-accounts.json');
        if (file_exists($path)) {
            throw new RuntimeException('Berkas akun uji sudah ada; tidak menimpa akun atau kredensial.');
        }

        $accounts = [];
        DB::transaction(function () use ($period, $path, &$accounts): void {
            for ($i = 1; $i <= 5; $i++) {
                $nik = sprintf('000000000000%04d', $i);
                if (User::where('nik_lookup_hash', NikManager::hash($nik))->exists()) {
                    throw new RuntimeException('Identitas uji sudah ada; tidak ada akun yang dibuat.');
                }
                $name = sprintf('Calon Siswa Uji %02d', $i);
                $password = Str::random(24);
                $phone = sprintf('080000000%03d', $i);
                $user = User::create([
                    'name' => $name,
                    'nik_lookup_hash' => NikManager::hash($nik),
                    'nik_encrypted' => NikManager::encrypt($nik),
                    'phone' => $phone,
                    'password' => $password,
                    'account_type' => AccountType::APPLICANT,
                    'account_status' => AccountStatus::ACTIVE,
                    'phone_verified_at' => now(),
                    'must_change_password' => false,
                ]);
                $applicant = Applicant::create(['user_id' => $user->id, 'full_name' => $name]);
                Registration::create([
                    'applicant_id' => $applicant->id,
                    'admission_period_id' => $period->id,
                    'status' => RegistrationStatus::DRAFT,
                    'data_revision' => 1,
                ]);
                $accounts[] = compact('name', 'nik', 'phone', 'password');
            }
            if (file_put_contents($path, json_encode($accounts, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), LOCK_EX) === false) {
                throw new RuntimeException('Tidak dapat menyimpan kredensial uji.');
            }
        });

        $this->command?->info('Lima akun draft dibuat; kredensial tersedia di storage/app/private/live-test-blank-accounts.json.');
    }
}
