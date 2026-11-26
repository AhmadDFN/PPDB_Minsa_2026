<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AdmissionPeriodStatus;
use App\Enums\Gender;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Applicant;
use App\Models\ApplicantAddress;
use App\Models\ApplicantEducation;
use App\Models\ApplicantGuardian;
use App\Models\InterviewBatch;
use App\Models\Program;
use App\Models\Registration;
use App\Models\RegistrationConsent;
use App\Models\User;
use App\Support\NikManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LiveTestSeeder extends Seeder
{
    /**
     * Run the LiveTest database seeder.
     * Seeds administrative accounts and 3 deterministic applicant states for manual & automated live testing.
     */
    public function run(): void
    {
        // 1. Ensure Roles, Programs, Periods, Admins & Interviewers
        $this->call([
            RoleAndPermissionSeeder::class,
            ProgramSeeder::class,
            AdmissionPeriodSeeder::class,
            SuperAdminSeeder::class,
            InterviewAccountSeeder::class,
            InterviewMasterSeeder::class,
        ]);

        $period = AdmissionPeriod::where('is_active', true)->first()
            ?? AdmissionPeriod::firstOrCreate(
                ['academic_year' => '2026/2027'],
                [
                    'name' => 'PPDB MIN 1 Kota Madiun 2026/2027',
                    'start_date' => now()->subMonth(),
                    'end_date' => now()->addMonth(),
                    'age_reference_date' => '2026-07-01',
                    'status' => AdmissionPeriodStatus::ACTIVE,
                    'is_active' => true,
                ]
            );

        $regularProg = Program::where('code', 'REGULER')->first()
            ?? Program::firstOrCreate(
                ['code' => 'REGULER'],
                ['name' => 'Program Reguler', 'description' => 'Program pendidikan dasar Madrasah Ibtidaiyah terintegrasi kurikulum nasional.', 'is_active' => true]
            );

        $unggulanProg = Program::where('code', 'UNGGULAN')->first()
            ?? Program::firstOrCreate(
                ['code' => 'UNGGULAN'],
                ['name' => 'Program Unggulan', 'description' => 'Program intensif tahfidz Al-Qur\'an dan sains madrasah.', 'is_active' => true]
            );

        $periodProgReguler = AdmissionPeriodProgram::firstOrCreate(
            ['admission_period_id' => $period->id, 'program_id' => $regularProg->id],
            ['quota' => 140, 'is_active' => true]
        );

        $periodProgUnggulan = AdmissionPeriodProgram::firstOrCreate(
            ['admission_period_id' => $period->id, 'program_id' => $unggulanProg->id],
            ['quota' => 70, 'is_active' => true]
        );

        $defaultApplicantPassword = Hash::make('Pendaftar2026!');

        // =========================================================================
        // AKUN UJI 1: Status DRAFT (Siap uji pengisian form 5 tahap & upload berkas)
        // =========================================================================
        $nik1 = '3577022003990001';
        $user1 = User::updateOrCreate(
            ['nik_lookup_hash' => NikManager::hash($nik1)],
            [
                'name' => 'Budi Santoso',
                'nik_encrypted' => NikManager::encrypt($nik1),
                'phone' => '081234560001',
                'password' => $defaultApplicantPassword,
                'account_type' => AccountType::APPLICANT,
                'account_status' => AccountStatus::ACTIVE,
                'email_verified_at' => null,
                'phone_verified_at' => now(),
                'must_change_password' => false,
            ]
        );

        $applicant1 = Applicant::updateOrCreate(
            ['user_id' => $user1->id],
            [
                'full_name' => 'Budi Santoso',
                'gender' => Gender::MALE,
                'birth_place' => 'Madiun',
                'birth_date' => '2019-08-15',
                'student_phone' => '081234560001',
                'child_order' => 1,
                'siblings_count' => 1,
            ]
        );

        Registration::updateOrCreate(
            ['applicant_id' => $applicant1->id, 'admission_period_id' => $period->id],
            [
                'admission_period_program_id' => $periodProgReguler->id,
                'status' => RegistrationStatus::DRAFT,
                'registration_number' => null,
                'queue_sequence' => null,
                'submitted_at' => null,
                'verified_at' => null,
                'data_revision' => 1,
            ]
        );

        // =========================================================================
        // AKUN UJI 2: Status SUBMITTED (Siap uji pengesahan berkas oleh Panitia)
        // =========================================================================
        $nik2 = '3577022003990002';
        $user2 = User::updateOrCreate(
            ['nik_lookup_hash' => NikManager::hash($nik2)],
            [
                'name' => 'Siti Rahmawati',
                'nik_encrypted' => NikManager::encrypt($nik2),
                'phone' => '081234560002',
                'password' => $defaultApplicantPassword,
                'account_type' => AccountType::APPLICANT,
                'account_status' => AccountStatus::ACTIVE,
                'email_verified_at' => null,
                'phone_verified_at' => now(),
                'must_change_password' => false,
            ]
        );

        $applicant2 = Applicant::updateOrCreate(
            ['user_id' => $user2->id],
            [
                'full_name' => 'Siti Rahmawati',
                'gender' => Gender::FEMALE,
                'birth_place' => 'Madiun',
                'birth_date' => '2019-05-10',
                'student_phone' => '081234560002',
                'child_order' => 2,
                'siblings_count' => 2,
            ]
        );

        ApplicantAddress::updateOrCreate(
            ['applicant_id' => $applicant2->id],
            [
                'address_line' => 'Jl. Pahlawan No. 45',
                'rt' => '03',
                'rw' => '02',
                'kelurahan' => 'Kartoharjo',
                'kecamatan' => 'Kartoharjo',
                'city' => 'Kota Madiun',
                'province' => 'Jawa Timur',
                'postal_code' => '63117',
                'distance_to_school_km' => 1.5,
            ]
        );

        ApplicantGuardian::updateOrCreate(
            ['applicant_id' => $applicant2->id, 'relationship_type' => 'FATHER'],
            [
                'name' => 'Bambang Sudarmanto',
                'nik' => '3577021102750001',
                'phone' => '081234560002',
                'occupation' => 'Wiraswasta',
                'education' => 'S1',
                'monthly_income' => 'Rp 3.000.000 - Rp 5.000.000',
                'is_alive' => true,
            ]
        );

        ApplicantGuardian::updateOrCreate(
            ['applicant_id' => $applicant2->id, 'relationship_type' => 'MOTHER'],
            [
                'name' => 'Endang Sri Lestari',
                'nik' => '3577025508780002',
                'phone' => '081234560002',
                'occupation' => 'Ibu Rumah Tangga',
                'education' => 'SMA/SMK',
                'monthly_income' => '< Rp 1.000.000',
                'is_alive' => true,
            ]
        );

        ApplicantEducation::updateOrCreate(
            ['applicant_id' => $applicant2->id],
            [
                'education_level' => 'ra',
                'school_name' => 'RA Perwanida 1 Kota Madiun',
                'npsn' => '69812345',
                'city' => 'Kota Madiun',
            ]
        );

        $reg2 = Registration::updateOrCreate(
            ['applicant_id' => $applicant2->id, 'admission_period_id' => $period->id],
            [
                'admission_period_program_id' => $periodProgUnggulan->id,
                'status' => RegistrationStatus::SUBMITTED,
                'registration_number' => null,
                'queue_sequence' => null,
                'submitted_at' => now()->subHours(2),
                'verified_at' => null,
                'data_revision' => 1,
            ]
        );

        RegistrationConsent::updateOrCreate(
            ['registration_id' => $reg2->id],
            [
                'is_agreed' => true,
                'agreed_at' => now()->subHours(2),
                'statement_text' => 'Saya selaku orang tua/wali menyatakan bahwa seluruh data yang diisikan adalah benar dan dapat dipertanggungjawabkan.',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) LiveTest/1.0',
            ]
        );

        // =========================================================================
        // AKUN UJI 3: Status VERIFIED (Sudah punya No. Registrasi 270001 & Jadwal Wawancara)
        // =========================================================================
        $nik3 = '3577022003990003';
        $user3 = User::updateOrCreate(
            ['nik_lookup_hash' => NikManager::hash($nik3)],
            [
                'name' => 'Ahmad Dany Pratama',
                'nik_encrypted' => NikManager::encrypt($nik3),
                'phone' => '081234560003',
                'password' => $defaultApplicantPassword,
                'account_type' => AccountType::APPLICANT,
                'account_status' => AccountStatus::ACTIVE,
                'email_verified_at' => null,
                'phone_verified_at' => now(),
                'must_change_password' => false,
            ]
        );

        $applicant3 = Applicant::updateOrCreate(
            ['user_id' => $user3->id],
            [
                'full_name' => 'Ahmad Dany Pratama',
                'gender' => Gender::MALE,
                'birth_place' => 'Madiun',
                'birth_date' => '2019-07-20',
                'student_phone' => '081234560003',
                'child_order' => 1,
                'siblings_count' => 0,
            ]
        );

        ApplicantAddress::updateOrCreate(
            ['applicant_id' => $applicant3->id],
            [
                'address_line' => 'Jl. Serayu No. 12',
                'rt' => '01',
                'rw' => '01',
                'kelurahan' => 'Pandean',
                'kecamatan' => 'Taman',
                'city' => 'Kota Madiun',
                'province' => 'Jawa Timur',
                'postal_code' => '63133',
                'distance_to_school_km' => 0.85,
            ]
        );

        ApplicantGuardian::updateOrCreate(
            ['applicant_id' => $applicant3->id, 'relationship_type' => 'FATHER'],
            [
                'name' => 'Fathur Rahman',
                'nik' => '3577011010720003',
                'phone' => '081234560003',
                'occupation' => 'PNS/ASN',
                'education' => 'S2',
                'monthly_income' => '> Rp 10.000.000',
                'is_alive' => true,
            ]
        );

        ApplicantGuardian::updateOrCreate(
            ['applicant_id' => $applicant3->id, 'relationship_type' => 'MOTHER'],
            [
                'name' => 'Nurul Hidayati',
                'nik' => '3577015012750004',
                'phone' => '081234560003',
                'occupation' => 'Guru',
                'education' => 'S1',
                'monthly_income' => 'Rp 3.000.000 - Rp 5.000.000',
                'is_alive' => true,
            ]
        );

        ApplicantEducation::updateOrCreate(
            ['applicant_id' => $applicant3->id],
            [
                'education_level' => 'tk',
                'school_name' => 'TK Pertiwi Kota Madiun',
                'npsn' => '69899999',
                'city' => 'Kota Madiun',
            ]
        );

        $firstBatch = InterviewBatch::where('admission_period_id', $period->id)->orderBy('batch_number')->first();

        $reg3 = Registration::updateOrCreate(
            ['applicant_id' => $applicant3->id, 'admission_period_id' => $period->id],
            [
                'admission_period_program_id' => $periodProgUnggulan->id,
                'status' => RegistrationStatus::SUBMITTED,
                'registration_number' => '270001',
                'queue_sequence' => 1,
                'interview_batch_id' => $firstBatch?->id,
                'submitted_at' => now()->subDay(),
                'verified_at' => now()->subHours(6),
                'data_revision' => 1,
            ]
        );

        RegistrationConsent::updateOrCreate(
            ['registration_id' => $reg3->id],
            [
                'is_agreed' => true,
                'agreed_at' => now()->subDay(),
                'statement_text' => 'Saya selaku orang tua/wali menyatakan bahwa seluruh data yang diisikan adalah benar dan dapat dipertanggungjawabkan.',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) LiveTest/1.0',
            ]
        );
    }
}
