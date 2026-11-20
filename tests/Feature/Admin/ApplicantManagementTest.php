<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AdmissionPeriodStatus;
use App\Enums\Gender;
use App\Enums\GuardianRelationship;
use App\Enums\PreviousEducationLevel;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Applicant;
use App\Models\ApplicantAddress;
use App\Models\ApplicantEducation;
use App\Models\ApplicantGuardian;
use App\Models\DocumentRequirement;
use App\Models\Program;
use App\Models\Registration;
use App\Models\RegistrationConsent;
use App\Models\RegistrationDocument;
use App\Models\RegistrationLog;
use App\Models\User;
use App\Support\NikManager;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicantManagementTest extends TestCase
{
    use RefreshDatabase;

    private AdmissionPeriod $activePeriod;

    private AdmissionPeriod $closedPeriod;

    private Program $regulerProgram;

    private Program $unggulanProgram;

    private AdmissionPeriodProgram $activeRegulerPeriodProgram;

    private AdmissionPeriodProgram $activeUnggulanPeriodProgram;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->activePeriod = AdmissionPeriod::factory()->create([
            'name' => 'PPDB 2026/2027 Gelombang 1',
            'academic_year' => '2026/2027',
            'start_date' => Carbon::now()->subDays(2),
            'end_date' => Carbon::now()->addDays(20),
            'age_reference_date' => Carbon::parse('2026-07-01'),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);

        $this->closedPeriod = AdmissionPeriod::factory()->create([
            'name' => 'PPDB 2025/2026 Masa Lalu',
            'academic_year' => '2025/2026',
            'start_date' => Carbon::now()->subYear()->subDays(30),
            'end_date' => Carbon::now()->subYear(),
            'status' => AdmissionPeriodStatus::CLOSED,
            'is_active' => false,
        ]);

        $this->regulerProgram = Program::factory()->create(['name' => 'Program Reguler', 'code' => 'REGULER', 'is_active' => true]);
        $this->unggulanProgram = Program::factory()->create(['name' => 'Program Unggulan', 'code' => 'UNGGULAN', 'is_active' => true]);

        $this->activeRegulerPeriodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $this->activePeriod->id,
            'program_id' => $this->regulerProgram->id,
            'is_active' => true,
        ]);

        $this->activeUnggulanPeriodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $this->activePeriod->id,
            'program_id' => $this->unggulanProgram->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_with_view_applicants_permission_can_access_applicants_index(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');

        $response = $this->actingAs($admin)->get(route('admin.applicants.index'));

        $response->assertOk();
        $response->assertSee('Data Pendaftar PPDB');
        $response->assertSee('Cari Pendaftar');
    }

    public function test_superadmin_can_access_applicants_index(): void
    {
        $superadmin = $this->createAdminUser('superadmin');

        $response = $this->actingAs($superadmin)->get(route('admin.applicants.index'));

        $response->assertOk();
        $response->assertSee('Data Pendaftar PPDB');
    }

    public function test_user_without_view_applicants_permission_is_forbidden(): void
    {
        $userWithoutPermission = User::factory()->create([
            'email' => 'staff_no_perm@minsatama.sch.id',
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
        ]);
        // Do not assign roles or permissions

        $response = $this->actingAs($userWithoutPermission)->get(route('admin.applicants.index'));

        $response->assertForbidden();
    }

    public function test_applicant_cannot_access_admin_applicants_index(): void
    {
        $applicantUser = User::factory()->applicant()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $response = $this->actingAs($applicantUser)->get(route('admin.applicants.index'));

        $response->assertForbidden();
    }

    public function test_applicants_list_displays_pagination(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');

        // Create 20 registrations
        for ($i = 1; $i <= 20; $i++) {
            $nik = sprintf('35730101012000%02d', $i);
            $this->createCompleteRegistration(
                nik: $nik,
                fullName: "Siswa Urut {$i}",
                regNumber: sprintf('27%03d', $i),
                period: $this->activePeriod,
                periodProgram: $this->activeRegulerPeriodProgram,
                status: RegistrationStatus::SUBMITTED,
            );
        }

        $response = $this->actingAs($admin)->get(route('admin.applicants.index'));

        $response->assertOk();
        // Page 1 should contain items
        $response->assertSee('Siswa Urut 20');
        $response->assertSee('page=2'); // Pagination controls present (link to page 2)

        // Page 2 should contain remaining 5 items
        $page2Response = $this->actingAs($admin)->get(route('admin.applicants.index', ['page' => 2]));
        $page2Response->assertOk();
    }

    public function test_search_by_student_name_filters_results(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');

        $this->createCompleteRegistration('3573010101200051', 'Muhammad Al Fatih', '27051', $this->activePeriod, $this->activeRegulerPeriodProgram);
        $this->createCompleteRegistration('3573010101200052', 'Siti Fatimah Az Zahra', '27052', $this->activePeriod, $this->activeRegulerPeriodProgram);

        $response = $this->actingAs($admin)->get(route('admin.applicants.index', ['search' => 'Fatih']));

        $response->assertOk();
        $response->assertSee('Muhammad Al Fatih');
        $response->assertDontSee('Siti Fatimah Az Zahra');
    }

    public function test_search_by_registration_number_filters_results(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');

        $this->createCompleteRegistration('3573010101200061', 'Ahmad Dhani', '27061', $this->activePeriod, $this->activeRegulerPeriodProgram);
        $this->createCompleteRegistration('3573010101200062', 'Budi Hartono', '27062', $this->activePeriod, $this->activeRegulerPeriodProgram);

        $response = $this->actingAs($admin)->get(route('admin.applicants.index', ['search' => '27061']));

        $response->assertOk();
        $response->assertSee('Ahmad Dhani');
        $response->assertDontSee('Budi Hartono');
    }

    public function test_search_by_16_digit_nik_uses_lookup_hash_securely(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');
        $targetNik = '3573010101200077';

        $this->createCompleteRegistration($targetNik, 'Pendaftar Rahasia NIK', '27077', $this->activePeriod, $this->activeRegulerPeriodProgram);
        $this->createCompleteRegistration('3573010101200078', 'Pendaftar Lainnya', '27078', $this->activePeriod, $this->activeRegulerPeriodProgram);

        $response = $this->actingAs($admin)->get(route('admin.applicants.index', ['search' => $targetNik]));

        $response->assertOk();
        $response->assertSee('Pendaftar Rahasia NIK');
        $response->assertDontSee('Pendaftar Lainnya');

        // Masked NIK must be shown on screen
        $response->assertSee('3573********0077');
        $response->assertDontSee('3573********0078');
    }

    public function test_filter_by_admission_period(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');

        $this->createCompleteRegistration('3573010101200081', 'Siswa Periode Aktif', '27081', $this->activePeriod, $this->activeRegulerPeriodProgram);

        $oldProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $this->closedPeriod->id,
            'program_id' => $this->regulerProgram->id,
        ]);
        $this->createCompleteRegistration('3573010101200082', 'Siswa Periode Lampau', '26082', $this->closedPeriod, $oldProgram);

        $response = $this->actingAs($admin)->get(route('admin.applicants.index', ['period_id' => $this->activePeriod->id]));

        $response->assertOk();
        $response->assertSee('Siswa Periode Aktif');
        $response->assertDontSee('Siswa Periode Lampau');
    }

    public function test_filter_by_program(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');

        $this->createCompleteRegistration('3573010101200091', 'Siswa Reguler', '27091', $this->activePeriod, $this->activeRegulerPeriodProgram);
        $this->createCompleteRegistration('3573010101200092', 'Siswa Unggulan', '27092', $this->activePeriod, $this->activeUnggulanPeriodProgram);

        $response = $this->actingAs($admin)->get(route('admin.applicants.index', ['program_id' => $this->unggulanProgram->id]));

        $response->assertOk();
        $response->assertSee('Siswa Unggulan');
        $response->assertDontSee('Siswa Reguler');
    }

    public function test_filter_by_status_draft_and_submitted(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');

        $this->createCompleteRegistration('3573010101200101', 'Siswa Terkirim', '27101', $this->activePeriod, $this->activeRegulerPeriodProgram, RegistrationStatus::SUBMITTED);
        $this->createCompleteRegistration('3573010101200102', 'Siswa Masih Draf', '27102', $this->activePeriod, $this->activeRegulerPeriodProgram, RegistrationStatus::DRAFT);

        // Filter SUBMITTED
        $submittedResponse = $this->actingAs($admin)->get(route('admin.applicants.index', ['status' => 'SUBMITTED']));
        $submittedResponse->assertOk();
        $submittedResponse->assertSee('Siswa Terkirim');
        $submittedResponse->assertDontSee('Siswa Masih Draf');

        // Filter DRAFT
        $draftResponse = $this->actingAs($admin)->get(route('admin.applicants.index', ['status' => 'DRAFT']));
        $draftResponse->assertOk();
        $draftResponse->assertSee('Siswa Masih Draf');
        $draftResponse->assertDontSee('Siswa Terkirim');
    }

    public function test_detail_page_displays_all_11_sections_correctly(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');
        Storage::fake('ppdb_private');

        $registrationData = $this->createCompleteRegistration(
            nik: '3573010101200111',
            fullName: 'Ahmad Fauzi Minsatama',
            regNumber: '27111',
            period: $this->activePeriod,
            periodProgram: $this->activeUnggulanPeriodProgram,
            status: RegistrationStatus::SUBMITTED,
            withAllDetails: true
        );

        $registration = $registrationData['registration'];

        $response = $this->actingAs($admin)->get(route('admin.applicants.show', $registration));

        $response->assertOk();

        // 1. Identitas Calon Siswa
        $response->assertSee('Ahmad Fauzi Minsatama');
        $response->assertSee('3573********0111');
        $response->assertSee('0123456789'); // NISN
        $response->assertSee('Laki-laki');
        $response->assertSee('Kota Madiun');
        $response->assertSee('Tahun'); // Usia runtime

        // 2. Alamat Domisili
        $response->assertSee('Jalan Merpati Nomor 45');
        $response->assertSee('Kartoharjo');
        $response->assertSee('63117');

        // 3. Data Ayah
        $response->assertSee('Bambang Sudarmono');
        $response->assertSee('3573********0001');

        // 4. Data Ibu
        $response->assertSee('Siti Aminah');
        $response->assertSee('3573********0002');

        // 5. Data Wali
        $response->assertSee('Haji Sulaiman');

        // 6. Pendidikan Sebelumnya
        $response->assertSee('RA Perwanida 1 Kota Madiun');

        // 7. Program & Periode
        $response->assertSee('Program Unggulan');
        $response->assertSee('TP 2026/2027');

        // 8. Berkas Dokumen
        $response->assertSee('Kartu Keluarga');
        $response->assertSee('kk_siswa.pdf');

        // 9. Status & Persetujuan
        $response->assertSee('SUBMITTED (Terkirim Resmi)');
        $response->assertSee('Persetujuan Orang Tua');

        // 10. Audit Log
        $response->assertSee('Riwayat Perubahan Data');

        // 11. Buka Ulang
        $response->assertSee('Riwayat Buka Ulang Pendaftaran');
    }

    public function test_admin_can_preview_and_download_uploaded_document(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');
        Storage::fake('ppdb_private');

        $regData = $this->createCompleteRegistration(
            '3573010101200121',
            'Siswa Dokumen Test',
            '27121',
            $this->activePeriod,
            $this->activeRegulerPeriodProgram,
            RegistrationStatus::SUBMITTED,
            true
        );

        $registration = $regData['registration'];
        $document = $regData['document'];

        // Preview document
        $previewResponse = $this->actingAs($admin)->get(
            route('admin.applicants.documents.preview', [$registration, $document])
        );
        $previewResponse->assertOk();
        $previewResponse->assertHeader('Content-Type', 'application/pdf');

        // Download document
        $downloadResponse = $this->actingAs($admin)->get(
            route('admin.applicants.documents.download', [$registration, $document])
        );
        $downloadResponse->assertOk();
    }

    public function test_admin_cannot_access_cross_registration_document(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');
        Storage::fake('ppdb_private');

        $regDataA = $this->createCompleteRegistration('3573010101200131', 'Siswa A', '27131', $this->activePeriod, $this->activeRegulerPeriodProgram, RegistrationStatus::SUBMITTED, true);
        $regDataB = $this->createCompleteRegistration('3573010101200132', 'Siswa B', '27132', $this->activePeriod, $this->activeRegulerPeriodProgram, RegistrationStatus::SUBMITTED, true);

        // Attempting to access Document B through Registration A URL must return 404
        $idorResponse = $this->actingAs($admin)->get(
            route('admin.applicants.documents.preview', [$regDataA['registration'], $regDataB['document']])
        );
        $idorResponse->assertNotFound();
    }

    private function createAdminUser(string $role): User
    {
        $admin = User::factory()->create([
            'email' => $role.'_'.bin2hex(random_bytes(4)).'@minsatama.sch.id',
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $admin->assignRole($role);

        return $admin;
    }

    /**
     * @return array{registration: Registration, applicant: Applicant, user: User, document: ?RegistrationDocument}
     */
    private function createCompleteRegistration(
        string $nik,
        string $fullName,
        string $regNumber,
        AdmissionPeriod $period,
        AdmissionPeriodProgram $periodProgram,
        RegistrationStatus $status = RegistrationStatus::SUBMITTED,
        bool $withAllDetails = false
    ): array {
        $user = User::factory()->create([
            'name' => $fullName,
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'nik_lookup_hash' => NikManager::hash($nik),
            'nik_encrypted' => NikManager::encrypt($nik),
            'phone' => '081234567890',
        ]);

        $applicant = Applicant::factory()->create([
            'user_id' => $user->id,
            'full_name' => $fullName,
            'gender' => Gender::MALE,
            'birth_place' => 'Kota Madiun',
            'birth_date' => Carbon::parse('2020-01-15'),
            'nisn' => '0123456789',
            'child_order' => 1,
            'siblings_count' => 1,
        ]);

        $registration = Registration::create([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $periodProgram->id,
            'registration_number' => $regNumber,
            'status' => $status,
            'submitted_at' => $status === RegistrationStatus::SUBMITTED ? now()->subHours(2) : null,
            'data_revision' => 1,
        ]);

        $document = null;

        if ($withAllDetails) {
            ApplicantAddress::create([
                'applicant_id' => $applicant->id,
                'address_line' => 'Jalan Merpati Nomor 45',
                'rt' => '02',
                'rw' => '05',
                'kelurahan' => 'Kartoharjo',
                'kecamatan' => 'Kartoharjo',
                'city' => 'Kota Madiun',
                'province' => 'Jawa Timur',
                'postal_code' => '63117',
                'distance_to_school_km' => 1.25,
            ]);

            ApplicantGuardian::create([
                'applicant_id' => $applicant->id,
                'relationship_type' => GuardianRelationship::FATHER,
                'name' => 'Bambang Sudarmono',
                'nik' => '3573010101800001',
                'education' => 'S1 Teknik',
                'occupation' => 'Karyawan Swasta',
                'monthly_income' => 'Rp 5.000.000 - Rp 10.000.000',
                'phone' => '081234567891',
                'is_alive' => true,
            ]);

            ApplicantGuardian::create([
                'applicant_id' => $applicant->id,
                'relationship_type' => GuardianRelationship::MOTHER,
                'name' => 'Siti Aminah',
                'nik' => '3573010101850002',
                'education' => 'D3 Keperawatan',
                'occupation' => 'Ibu Rumah Tangga',
                'monthly_income' => 'Rp 0',
                'phone' => '081234567892',
                'is_alive' => true,
            ]);

            ApplicantGuardian::create([
                'applicant_id' => $applicant->id,
                'relationship_type' => GuardianRelationship::GUARDIAN,
                'name' => 'Haji Sulaiman',
                'nik' => '3573010101650003',
                'education' => 'SMA',
                'occupation' => 'Wiraswasta',
                'monthly_income' => 'Rp 10.000.000+',
                'phone' => '081234567893',
                'is_alive' => true,
            ]);

            ApplicantEducation::create([
                'applicant_id' => $applicant->id,
                'education_level' => PreviousEducationLevel::RA,
                'school_name' => 'RA Perwanida 1 Kota Madiun',
                'npsn' => '69785421',
                'city' => 'Kota Madiun',
            ]);

            $req = DocumentRequirement::firstOrCreate([
                'admission_period_id' => $period->id,
                'name' => 'Kartu Keluarga',
            ], [
                'code' => 'KK_TEST',
                'is_required' => true,
                'allowed_mimes' => 'application/pdf',
                'max_size_kb' => 5120,
            ]);

            $docPath = 'ppdb/documents/'.$registration->id.'/kk_siswa.pdf';
            Storage::disk('ppdb_private')->put($docPath, "%PDF-1.4\n%dummy pdf content\n%%EOF");

            $document = RegistrationDocument::create([
                'registration_id' => $registration->id,
                'document_requirement_id' => $req->id,
                'file_path' => $docPath,
                'original_name' => 'kk_siswa.pdf',
                'mime_type' => 'application/pdf',
                'file_size_bytes' => 1024,
                'file_hash' => hash('sha256', '%PDF-dummy'),
                'uploaded_at' => now(),
            ]);

            RegistrationConsent::create([
                'registration_id' => $registration->id,
                'statement_text' => 'Saya menyatakan seluruh data adalah benar.',
                'consent_version' => 'v1.0',
                'accepted_by_name' => 'Bambang Sudarmono',
                'is_agreed' => true,
                'agreed_at' => now(),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit Browser',
            ]);

            RegistrationLog::create([
                'registration_id' => $registration->id,
                'user_id' => $user->id,
                'action' => 'submitted',
                'from_revision' => 1,
                'to_revision' => 1,
                'changes' => ['fields' => ['status']],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit Browser',
                'created_at' => now(),
            ]);
        }

        return [
            'registration' => $registration,
            'applicant' => $applicant,
            'user' => $user,
            'document' => $document,
        ];
    }
}
