<?php

declare(strict_types=1);

namespace Tests\Feature\Integration;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AdmissionPeriodStatus;
use App\Enums\Gender;
use App\Enums\GuardianRelationship;
use App\Enums\PdfDocumentType;
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
use App\Models\RegistrationDocument;
use App\Models\RegistrationLog;
use App\Models\RegistrationPdfVersion;
use App\Models\User;
use App\Services\Authentication\Delivery\ArrayOtpDelivery;
use App\Support\NikManager;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EndToEndRegistrationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ArrayOtpDelivery::clear();
    }

    /**
     * Scenario 1: Pendaftaran normal (Reguler) dari registrasi akun sampai PDF Final & verifikasi QR.
     */
    public function test_scenario_1_normal_registration_flow_from_account_to_pdf_and_qr(): void
    {
        Storage::fake('ppdb_private');
        $this->travelTo(Carbon::parse('2026-11-19 08:00:00'));

        // 0. Setup Admission Period & Programs
        $period = AdmissionPeriod::factory()->create([
            'name' => 'PPDB MIN 1 Kota Madiun 2026/2027',
            'academic_year' => '2026/2027',
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'age_reference_date' => Carbon::parse('2026-07-01'),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);

        $programReguler = Program::factory()->create([
            'code' => 'REGULER',
            'name' => 'Program Reguler',
            'is_active' => true,
        ]);

        $periodProgramReguler = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $programReguler->id,
            'is_active' => true,
        ]);

        $reqKk = DocumentRequirement::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $programReguler->id,
            'code' => 'KK',
            'name' => 'Kartu Keluarga',
            'is_required' => true,
            'allowed_mimes' => 'application/pdf,image/jpeg,image/png',
            'max_size_kb' => 5120,
        ]);

        $reqFoto = DocumentRequirement::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $programReguler->id,
            'code' => 'PAS_FOTO',
            'name' => 'Pas Foto Calon Siswa',
            'is_required' => true,
            'allowed_mimes' => 'image/jpeg,image/png',
            'max_size_kb' => 5120,
        ]);

        // 1. Buat akun calon siswa
        $nik = '3573010101190001';
        $registerPayload = [
            'nik' => $nik,
            'full_name' => 'Ahmad Fauzi Minsatama',
            'gender' => 'L',
            'birth_place' => 'Kota Madiun',
            'birth_date' => '2020-01-15',
            'phone' => '081234567890',
            'consent' => '1',
        ];

        $registerResponse = $this->post(route('register.store'), $registerPayload);
        $registerResponse->assertRedirect(route('activation.form'));

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->firstOrFail();
        $this->assertSame(AccountStatus::PENDING_ACTIVATION, $user->account_status);
        $this->assertSame(AccountType::APPLICANT, $user->account_type);

        // 2. Aktivasi akun dengan OTP (Phase 10.4: auto-generated temporary password)
        $plainOtp = ArrayOtpDelivery::latestOtpFor('081234567890');
        $this->assertNotNull($plainOtp);

        $verifyOtpResponse = $this->withSession(['activation_user_id' => $user->id])
            ->post(route('activation.verify'), ['otp' => $plainOtp]);
        $verifyOtpResponse->assertRedirect(route('login'));

        $user->refresh();
        $this->assertSame(AccountStatus::ACTIVE, $user->account_status);
        $this->assertTrue($user->must_change_password);

        $tempPassword = ArrayOtpDelivery::latestPasswordFor('081234567890');
        $this->assertNotNull($tempPassword);

        // 3. Login dengan temporary password -> diarahkan ganti password wajib
        $loginResponse = $this->post(route('login.store'), [
            'nik' => $nik,
            'password' => $tempPassword,
        ]);
        $loginResponse->assertRedirect(route('applicant.password.change'));
        $this->assertAuthenticatedAs($user);

        // Ubah kata sandi sementara menjadi permanen
        $changePasswordResponse = $this->actingAs($user)->post(route('applicant.password.change.update'), [
            'current_password' => $tempPassword,
            'password' => 'PasswordAman2026!',
            'password_confirmation' => 'PasswordAman2026!',
        ]);
        $changePasswordResponse->assertRedirect(route('applicant.dashboard'));

        $user->refresh();
        $this->assertFalse($user->must_change_password);

        // 4. Isi Biodata
        $this->actingAs($user)->from(route('applicant.biodata.edit'))
            ->post(route('applicant.biodata.update'), [
                'full_name' => 'Ahmad Fauzi Minsatama',
                'gender' => 'L',
                'birth_place' => 'Kota Madiun',
                'birth_date' => '2020-01-15',
                'nisn' => '0123456789',
                'child_order' => 1,
                'siblings_count' => 2,
            ])->assertRedirect(route('applicant.biodata.edit'))
            ->assertSessionHas('success');

        // 5. Isi Alamat
        $this->actingAs($user)->from(route('applicant.address.edit'))
            ->post(route('applicant.address.update'), [
                'address_line' => 'Jalan Pahlawan Nomor 123',
                'rt' => '002',
                'rw' => '005',
                'kelurahan' => 'Kartoharjo',
                'kecamatan' => 'Kartoharjo',
                'city' => 'Kota Madiun',
                'province' => 'Jawa Timur',
                'postal_code' => '63117',
                'distance_to_school_meters' => 850,
            ])->assertRedirect(route('applicant.address.edit'))
            ->assertSessionHas('success');

        // 6. Isi Orang Tua
        $this->actingAs($user)->from(route('applicant.parents.edit'))
            ->post(route('applicant.parents.update'), [
                'father_name' => 'Bambang Sudarmono',
                'father_nik' => '3573010101800001',
                'father_is_alive' => '1',
                'father_education' => 'S1',
                'father_occupation' => 'Pegawai Swasta',
                'father_monthly_income' => 'Rp 5.000.000 - Rp 10.000.000',
                'father_phone' => '081234567891',
                'mother_name' => 'Siti Aminah',
                'mother_nik' => '3573010101850002',
                'mother_is_alive' => '1',
                'mother_education' => 'D3',
                'mother_occupation' => 'Wiraswasta',
                'mother_monthly_income' => 'Rp 3.000.000 - Rp 5.000.000',
                'mother_phone' => '081234567892',
                'has_guardian' => '0',
            ])->assertRedirect(route('applicant.parents.edit'))
            ->assertSessionHas('success');

        // 7. Isi Pendidikan Sebelumnya
        $this->actingAs($user)->from(route('applicant.education.edit'))
            ->post(route('applicant.education.update'), [
                'education_level' => 'ra',
                'school_name' => 'RA Perwanida 1 Kota Madiun',
                'npsn' => '69785421',
                'city' => 'Kota Madiun',
            ])->assertRedirect(route('applicant.education.edit'))
            ->assertSessionHas('success');

        // 8. Pilih Program Reguler
        $this->actingAs($user)->from(route('applicant.program.edit'))
            ->post(route('applicant.program.update'), [
                'admission_period_program_id' => $periodProgramReguler->id,
            ])->assertRedirect(route('applicant.dashboard'))
            ->assertSessionHas('success');

        $applicant = $user->applicant->fresh();
        $registration = Registration::where('applicant_id', $applicant->id)->firstOrFail();
        $this->assertSame(RegistrationStatus::DRAFT, $registration->status);
        $this->assertSame($periodProgramReguler->id, $registration->admission_period_program_id);

        // 9. Upload Dokumen Wajib (KK PDF & Pas Foto JPG)
        $kkFile = UploadedFile::fake()->createWithContent('kartu_keluarga.pdf', "%PDF-1.4\n%real pdf bytes\n%%EOF");
        $this->actingAs($user)->post(route('applicant.documents.upload', $reqKk->id), [
            'file' => $kkFile,
        ])->assertRedirect(route('applicant.documents.index'))
            ->assertSessionHas('success');

        $rawJpg = base64_decode(
            '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAX/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAGf/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABBQJ//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAwEBPwF//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAgEBPwF//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQAGPwJ//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPyF//9oADAMBAAIRAxEAPwD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAwE/EAf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAECAwE/EAf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAwE/EAf/2Q==',
            true
        );
        $fotoPath = 'ppdb/documents/'.$registration->id.'/pas_foto.jpg';
        Storage::disk('ppdb_private')->put($fotoPath, $rawJpg);

        RegistrationDocument::create([
            'registration_id' => $registration->id,
            'document_requirement_id' => $reqFoto->id,
            'file_path' => $fotoPath,
            'original_name' => 'pas_foto.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => strlen($rawJpg),
            'file_hash' => hash('sha256', $rawJpg),
            'uploaded_at' => now(),
        ]);

        // 10. Generate Draft PDF
        $draftPdfResponse = $this->actingAs($user)->post(route('applicant.registration.pdf.draft'));
        $draftPdfResponse->assertRedirect(route('applicant.dashboard'))
            ->assertSessionHas('success', 'PDF draf berhasil dibuat.');

        $draftVersion = RegistrationPdfVersion::where('registration_id', $registration->id)
            ->where('document_type', PdfDocumentType::DRAFT)
            ->firstOrFail();

        $this->assertSame(1, $draftVersion->version_number);
        $draftContents = Storage::disk('ppdb_private')->get($draftVersion->file_path);
        $this->assertStringStartsWith('%PDF-', $draftContents);
        $this->assertNull($draftVersion->verification_token_hash);

        // 11. Review pendaftaran
        $reviewResponse = $this->actingAs($user)->get(route('applicant.registration.review'));
        $reviewResponse->assertOk()
            ->assertSee('Ahmad Fauzi Minsatama')
            ->assertSee('Program Reguler')
            ->assertSee('Kirim Pendaftaran');

        // 12. Submit pendaftaran
        $submitResponse = $this->actingAs($user)->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Bambang Sudarmono',
        ]);
        $submitResponse->assertRedirect(route('applicant.registration.review'))
            ->assertSessionHas('success');

        $registration->refresh();
        $this->assertSame(RegistrationStatus::SUBMITTED, $registration->status);
        $this->assertSame('27001', $registration->registration_number);
        $this->assertNotNull($registration->submitted_at);

        // 13. Generate Final PDF
        $finalPdfResponse = $this->actingAs($user)->post(route('applicant.registration.pdf.final'));
        $finalPdfResponse->assertRedirect(route('applicant.dashboard'))
            ->assertSessionHas('success', 'Bukti PDF final berhasil dibuat.');

        $finalVersion = RegistrationPdfVersion::where('registration_id', $registration->id)
            ->where('document_type', PdfDocumentType::FINAL)
            ->firstOrFail();

        $this->assertSame(1, $finalVersion->version_number);
        $this->assertNotNull($finalVersion->verification_token_hash);

        // 14. Download Final PDF
        $downloadResponse = $this->actingAs($user)->get(route('applicant.registration.pdf.download', $finalVersion->id));
        $downloadResponse->assertOk();
        $downloadResponse->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $downloadResponse->getContent());

        // 15. Verifikasi QR
        // Reverse token check via database verification token hash match
        $allTokens = DB::table('registration_pdf_versions')
            ->where('id', $finalVersion->id)
            ->value('verification_token_hash');
        $this->assertNotNull($allTokens);

        // Verify invalid token returns 404
        $invalidTokenResponse = $this->get(route('public.registration-pdfs.verify', [
            'token' => str_repeat('a', 64),
            'registration_number' => $registration->registration_number,
        ]));
        $invalidTokenResponse->assertNotFound();

        // 16. Verifikasi konsistensi data
        $this->assertGreaterThanOrEqual(1, $registration->data_revision);
        $this->assertSame($registration->data_revision, $finalVersion->data_revision);
        $this->assertTrue($registration->consent->is_agreed);
        $this->assertSame('Bambang Sudarmono', $registration->consent->accepted_by_name);
    }

    /**
     * Scenario 2: Program Unggulan - sequence terisolasi dan tidak tercampur dengan Reguler.
     */
    public function test_scenario_2_unggulan_program_sequence_isolation(): void
    {
        Storage::fake('ppdb_private');
        $this->travelTo(Carbon::parse('2026-11-19 09:00:00'));

        $period = AdmissionPeriod::factory()->create([
            'academic_year' => '2026/2027',
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);

        $programReguler = Program::factory()->create(['code' => 'REGULER', 'name' => 'Program Reguler']);
        $programUnggulan = Program::factory()->create(['code' => 'UNGGULAN', 'name' => 'Program Unggulan']);

        $periodProgramReguler = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $programReguler->id,
        ]);

        $periodProgramUnggulan = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $programUnggulan->id,
        ]);

        // Buat dan submit pendaftar 1 (Reguler)
        $regulerApplicant = $this->createFullyPreparedApplicant(
            '3573010101190011',
            $period,
            $periodProgramReguler
        );

        $this->actingAs($regulerApplicant['user'])->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Orang Tua Reguler',
        ])->assertSessionHas('success');

        $this->assertSame(
            '27001',
            $regulerApplicant['registration']->fresh()->registration_number
        );

        // Buat dan submit pendaftar 2 (Unggulan 1)
        $unggulanApplicant1 = $this->createFullyPreparedApplicant(
            '3573010101190012',
            $period,
            $periodProgramUnggulan
        );

        $this->actingAs($unggulanApplicant1['user'])->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Orang Tua Unggulan 1',
        ])->assertSessionHas('success');

        $this->assertSame(
            '27002',
            $unggulanApplicant1['registration']->fresh()->registration_number
        );

        // Buat dan submit pendaftar 3 (Unggulan 2)
        $unggulanApplicant2 = $this->createFullyPreparedApplicant(
            '3573010101190013',
            $period,
            $periodProgramUnggulan
        );

        $this->actingAs($unggulanApplicant2['user'])->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Orang Tua Unggulan 2',
        ])->assertSessionHas('success');

        $this->assertSame(
            '27003',
            $unggulanApplicant2['registration']->fresh()->registration_number
        );

        // Buat dan submit pendaftar 4 (Reguler 2)
        $regulerApplicant2 = $this->createFullyPreparedApplicant(
            '3573010101190014',
            $period,
            $periodProgramReguler
        );

        $this->actingAs($regulerApplicant2['user'])->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Orang Tua Reguler 2',
        ])->assertSessionHas('success');

        $this->assertSame(
            '27004',
            $regulerApplicant2['registration']->fresh()->registration_number
        );
    }

    /**
     * Scenario 3: Submission ditolak ketika data/dokumen/persetujuan belum lengkap.
     */
    public function test_scenario_3_incomplete_submissions_are_rejected(): void
    {
        Storage::fake('ppdb_private');
        $period = AdmissionPeriod::factory()->create([
            'academic_year' => '2026/2027',
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);

        $program = Program::factory()->create(['code' => 'REGULER', 'name' => 'Program Reguler']);
        $periodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
        ]);

        $req = DocumentRequirement::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
            'name' => 'Akta Kelahiran',
            'is_required' => true,
        ]);

        // Incomplete: child_order is null
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'nik_lookup_hash' => NikManager::hash('3573010101190021'),
            'nik_encrypted' => NikManager::encrypt('3573010101190021'),
        ]);

        $applicant = Applicant::factory()->create([
            'user_id' => $user->id,
            'child_order' => null,
            'siblings_count' => null,
        ]);

        $registration = Registration::create([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $periodProgram->id,
            'status' => RegistrationStatus::DRAFT,
            'data_revision' => 1,
        ]);

        // Coba submit tanpa data lengkap
        $response = $this->actingAs($user)->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Orang Tua',
        ]);

        $response->assertSessionHasErrors('registration');
        $this->assertSame(RegistrationStatus::DRAFT, $registration->fresh()->status);
        $this->assertNull($registration->fresh()->registration_number);
    }

    /**
     * Scenario 4: Edit setelah submission - nomor registrasi tetap, submitted_at tetap, revisi bertambah, PDF lama stale.
     */
    public function test_scenario_4_edit_after_submission_workflow(): void
    {
        Storage::fake('ppdb_private');
        $this->travelTo(Carbon::parse('2026-11-19 10:00:00'));

        $period = AdmissionPeriod::factory()->create([
            'academic_year' => '2026/2027',
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);

        $program = Program::factory()->create(['code' => 'REGULER', 'name' => 'Program Reguler']);
        $periodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
        ]);

        $data = $this->createFullyPreparedApplicant('3573010101190031', $period, $periodProgram);
        $user = $data['user'];

        // Submit pendaftaran
        $this->actingAs($user)->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Budi Sudarmono',
        ])->assertSessionHas('success');

        $registration = $data['registration']->fresh();
        $initialNumber = $registration->registration_number;
        $initialSubmittedAt = $registration->submitted_at;
        $this->assertSame(1, $registration->data_revision);

        // Generate PDF final v1
        $this->actingAs($user)->post(route('applicant.registration.pdf.final'))
            ->assertSessionHas('success');

        $pdfV1 = RegistrationPdfVersion::where('registration_id', $registration->id)
            ->where('document_type', PdfDocumentType::FINAL)
            ->firstOrFail();
        $this->assertSame(1, $pdfV1->version_number);
        $this->assertSame(1, $pdfV1->data_revision);
        $this->assertFalse($pdfV1->isOutdated());

        // Ubah alamat ketika periode masih aktif
        $this->actingAs($user)->post(route('applicant.address.update'), [
            'address_line' => 'Jalan Diponegoro Baru Nomor 99',
            'rt' => '003',
            'rw' => '008',
            'kelurahan' => 'Kartoharjo',
            'kecamatan' => 'Kartoharjo',
            'city' => 'Kota Madiun',
            'province' => 'Jawa Timur',
            'postal_code' => '63117',
            'distance_to_school_meters' => 500,
        ])->assertSessionHas('success');

        $registration->refresh();
        $this->assertSame(2, $registration->data_revision);
        $this->assertSame($initialNumber, $registration->registration_number);
        $this->assertSame($initialSubmittedAt->toDateTimeString(), $registration->submitted_at->toDateTimeString());

        // Pastikan PDF v1 lama terdeteksi outdated
        $this->assertTrue($pdfV1->fresh()->isOutdated());

        // Generate PDF final v2
        $this->actingAs($user)->post(route('applicant.registration.pdf.final'))
            ->assertSessionHas('success');

        $pdfV2 = RegistrationPdfVersion::where('registration_id', $registration->id)
            ->where('document_type', PdfDocumentType::FINAL)
            ->where('version_number', 2)
            ->firstOrFail();

        $this->assertSame(2, $pdfV2->data_revision);
        $this->assertFalse($pdfV2->isOutdated());

        // Pastikan file PDF v1 dan v2 dua-duanya tetap tersimpan privat
        $this->assertTrue(Storage::disk('ppdb_private')->exists($pdfV1->file_path));
        $this->assertTrue(Storage::disk('ppdb_private')->exists($pdfV2->file_path));
        $this->assertNotSame($pdfV1->file_path, $pdfV2->file_path);
    }

    /**
     * Scenario 5: Admin reopen saat periode tutup, expiry enforcement, dan audit trail.
     */
    public function test_scenario_5_admin_reopen_and_expiration(): void
    {
        Storage::fake('ppdb_private');
        $this->travelTo(Carbon::parse('2026-11-19 11:00:00'));

        // 1. Setup closed period
        $period = AdmissionPeriod::factory()->create([
            'academic_year' => '2026/2027',
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subDay(),
            'status' => AdmissionPeriodStatus::CLOSED,
            'is_active' => false,
        ]);

        $program = Program::factory()->create(['code' => 'REGULER', 'name' => 'Program Reguler']);
        $periodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
        ]);

        $applicantA = $this->createFullyPreparedApplicant('3573010101190051', $period, $periodProgram);
        $applicantB = $this->createFullyPreparedApplicant('3573010101190052', $period, $periodProgram);

        // Set status SUBMITTED
        $applicantA['registration']->update([
            'status' => RegistrationStatus::SUBMITTED,
            'registration_number' => 'PPDB/2026-2027/REGULER/00005',
            'submitted_at' => now()->subDays(2),
            'data_revision' => 1,
        ]);

        $applicantB['registration']->update([
            'status' => RegistrationStatus::SUBMITTED,
            'registration_number' => 'PPDB/2026-2027/REGULER/00006',
            'submitted_at' => now()->subDays(2),
            'data_revision' => 1,
        ]);

        // Coba edit saat periode tutup -> ditolak dengan error registrasi
        $this->actingAs($applicantA['user'])->post(route('applicant.address.update'), [
            'address_line' => 'Jalan Ditutup',
            'rt' => '001',
            'rw' => '001',
            'kelurahan' => 'Demangan',
            'kecamatan' => 'Taman',
            'city' => 'Kota Madiun',
            'province' => 'Jawa Timur',
        ])->assertSessionHasErrors('registration');

        // 2. Setup Admin dengan permission reopen_registration
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = User::factory()->create([
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
            'email' => 'admin@minsatama.sch.id',
        ]);
        $admin->assignRole('admin_ppdb');

        // Admin membuka akses edit untuk pendaftar A selama 2 jam
        $expiresAt = now()->addHours(2);
        $this->actingAs($admin)->post(route('admin.registrations.reopen.store', $applicantA['registration']->id), [
            'reason' => 'Perbaikan data alamat berdasarkan instruksi kepala madrasah',
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ])->assertRedirect(route('admin.registrations.index'))
            ->assertSessionHas('success');

        // Pendaftar A sekarang dapat mengedit data
        $this->actingAs($applicantA['user'])->post(route('applicant.address.update'), [
            'address_line' => 'Jalan Pahlawan Direvisi Nomor 55',
            'rt' => '004',
            'rw' => '002',
            'kelurahan' => 'Demangan',
            'kecamatan' => 'Taman',
            'city' => 'Kota Madiun',
            'province' => 'Jawa Timur',
            'postal_code' => '63118',
            'distance_to_school_meters' => 600,
        ])->assertSessionHas('success');

        $this->assertSame(2, $applicantA['registration']->fresh()->data_revision);

        // Pendaftar B tetap terkunci
        $this->actingAs($applicantB['user'])->post(route('applicant.address.update'), [
            'address_line' => 'Jalan Pendaftar B Ditolak',
            'rt' => '001',
            'rw' => '001',
            'kelurahan' => 'Demangan',
            'kecamatan' => 'Taman',
            'city' => 'Kota Madiun',
            'province' => 'Jawa Timur',
        ])->assertSessionHasErrors('registration');

        // 3. Majukan waktu melampaui kedaluwarsa reopen
        $this->travelTo(now()->addHours(3));

        // Pendaftar A otomatis terkunci kembali setelah expired
        $this->actingAs($applicantA['user'])->post(route('applicant.address.update'), [
            'address_line' => 'Jalan Setelah Expired',
            'rt' => '001',
            'rw' => '001',
            'kelurahan' => 'Demangan',
            'kecamatan' => 'Taman',
            'city' => 'Kota Madiun',
            'province' => 'Jawa Timur',
        ])->assertSessionHasErrors('registration');

        // 4. Periksa audit trail
        $logs = RegistrationLog::where('registration_id', $applicantA['registration']->id)->get();
        $this->assertTrue($logs->contains('action', 'reopen_window_opened'));
        $this->assertTrue($logs->contains('action', 'updated_after_submission'));
    }

    /**
     * Scenario 6: Security, ownership, dan data isolation antar-pendaftar.
     */
    public function test_scenario_6_security_ownership_and_data_isolation(): void
    {
        Storage::fake('ppdb_private');
        $period = AdmissionPeriod::factory()->create([
            'academic_year' => '2026/2027',
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);

        $program = Program::factory()->create(['code' => 'REGULER', 'name' => 'Program Reguler']);
        $periodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
        ]);

        $applicantA = $this->createFullyPreparedApplicant('3573010101190061', $period, $periodProgram);
        $applicantB = $this->createFullyPreparedApplicant('3573010101190062', $period, $periodProgram);

        // Buat PDF untuk B
        $pdfB = RegistrationPdfVersion::create([
            'registration_id' => $applicantB['registration']->id,
            'document_type' => PdfDocumentType::DRAFT,
            'version_number' => 1,
            'data_revision' => 1,
            'file_path' => 'ppdb/pdf/'.$applicantB['registration']->id.'/draft/test.pdf',
            'file_hash' => hash('sha256', '%PDF-test'),
            'storage_disk' => 'ppdb_private',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 9,
            'generated_by' => $applicantB['user']->id,
            'generated_at' => now(),
        ]);
        Storage::disk('ppdb_private')->put($pdfB->file_path, '%PDF-test');

        // Applicant A mencoba mengunduh PDF B -> 404 (scoped by applicant ownership)
        $this->actingAs($applicantA['user'])
            ->get(route('applicant.registration.pdf.download', $pdfB->id))
            ->assertNotFound();

        // Applicant A mencoba melihat preview dokumen B -> 404
        $docB = $applicantB['registration']->documents()->first();
        if ($docB) {
            $this->actingAs($applicantA['user'])
                ->get(route('applicant.documents.download', $docB->id))
                ->assertNotFound();
        }

        // Applicant A mencoba bypass status atau registration_number pada submit
        $this->actingAs($applicantA['user'])->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Hacker Name',
            'registration_number' => 'PPDB/FORGED/NUMBER/99999',
            'status' => 'ACCEPTED_DIRECTLY',
        ])->assertSessionHas('success');

        $regA = $applicantA['registration']->fresh();
        $this->assertSame(RegistrationStatus::SUBMITTED, $regA->status);
        $this->assertNotSame('PPDB/FORGED/NUMBER/99999', $regA->registration_number);
        $this->assertMatchesRegularExpression('/^[0-9]{5}$/', $regA->registration_number);
    }

    /**
     * Scenario 7: Failure and recovery (upload spoofing, file cleanup, double submit).
     */
    public function test_scenario_7_failure_and_recovery_scenarios(): void
    {
        Storage::fake('ppdb_private');
        $period = AdmissionPeriod::factory()->create([
            'academic_year' => '2026/2027',
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);

        $program = Program::factory()->create(['code' => 'REGULER', 'name' => 'Program Reguler']);
        $periodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
        ]);

        $req = DocumentRequirement::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
            'name' => 'Akta Kelahiran',
            'allowed_mimes' => 'application/pdf',
            'max_size_kb' => 5120,
        ]);

        $data = $this->createFullyPreparedApplicant('3573010101190071', $period, $periodProgram);
        $user = $data['user'];

        // 1. Upload file palsu (teks diberi nama .pdf)
        $fakePdf = UploadedFile::fake()->createWithContent('palsu.pdf', 'INI BUKAN PDF TAPI PLAIN TEXT');
        $this->actingAs($user)->post(route('applicant.documents.upload', $req->id), [
            'file' => $fakePdf,
        ])->assertSessionHasErrors('file');

        // Pastikan tidak ada berkas tersimpan
        $this->assertSame(0, $data['registration']->documents()->where('document_requirement_id', $req->id)->count());

        // Unggah berkas valid untuk memenuhi persyaratan wajib sebelum submit
        $validPdf = UploadedFile::fake()->createWithContent('akta_valid.pdf', "%PDF-1.4\n%real pdf bytes\n%%EOF");
        $this->actingAs($user)->post(route('applicant.documents.upload', $req->id), [
            'file' => $validPdf,
        ])->assertSessionHas('success');

        // 2. Double submit idempotency
        $this->actingAs($user)->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Budi Santoso',
        ])->assertSessionHas('success');

        $firstNumber = $data['registration']->fresh()->registration_number;

        // Submit kedua kalinya pada pendaftaran yang sama -> idempoten, nomor tidak berubah
        $this->actingAs($user)->post(route('applicant.registration.submit'), [
            'consent' => '1',
            'consent_name' => 'Budi Santoso',
        ])->assertSessionHas('success');

        $this->assertSame($firstNumber, $data['registration']->fresh()->registration_number);
    }

    /**
     * Helper untuk menyiapkan pendaftar dengan profil lengkap.
     *
     * @return array{user: User, applicant: Applicant, registration: Registration}
     */
    private function createFullyPreparedApplicant(
        string $nik,
        AdmissionPeriod $period,
        AdmissionPeriodProgram $periodProgram
    ): array {
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'nik_lookup_hash' => NikManager::hash($nik),
            'nik_encrypted' => NikManager::encrypt($nik),
        ]);

        $applicant = Applicant::factory()->create([
            'user_id' => $user->id,
            'full_name' => 'Siswa Test '.substr($nik, -4),
            'gender' => Gender::MALE,
            'birth_place' => 'Kota Madiun',
            'birth_date' => '2020-01-15',
            'child_order' => 1,
            'siblings_count' => 1,
        ]);

        ApplicantAddress::factory()->create([
            'applicant_id' => $applicant->id,
            'address_line' => 'Jalan Merpati Nomor 10',
            'kelurahan' => 'Kejuron',
            'kecamatan' => 'Taman',
            'city' => 'Kota Madiun',
            'province' => 'Jawa Timur',
            'postal_code' => '63116',
        ]);

        ApplicantGuardian::factory()->create([
            'applicant_id' => $applicant->id,
            'relationship_type' => GuardianRelationship::FATHER,
            'name' => 'Ayah Siswa',
            'occupation' => 'Wiraswasta',
            'monthly_income' => 'Rp 5.000.000',
            'phone' => '081234567890',
        ]);

        ApplicantGuardian::factory()->create([
            'applicant_id' => $applicant->id,
            'relationship_type' => GuardianRelationship::MOTHER,
            'name' => 'Ibu Siswa',
            'occupation' => 'Ibu Rumah Tangga',
            'monthly_income' => 'Rp 0',
            'phone' => '081234567891',
        ]);

        ApplicantEducation::factory()->create([
            'applicant_id' => $applicant->id,
            'education_level' => PreviousEducationLevel::TK,
            'school_name' => 'TK Minsatama',
        ]);

        $registration = Registration::create([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $periodProgram->id,
            'status' => RegistrationStatus::DRAFT,
            'data_revision' => 1,
        ]);

        $req = DocumentRequirement::firstOrCreate([
            'admission_period_id' => $period->id,
            'program_id' => $periodProgram->program_id,
            'name' => 'Kartu Keluarga',
        ], [
            'code' => 'KK_'.$periodProgram->id,
            'is_required' => true,
            'allowed_mimes' => 'application/pdf,image/jpeg',
            'max_size_kb' => 5120,
        ]);

        $docPath = 'ppdb/documents/'.$registration->id.'/kk.pdf';
        Storage::disk('ppdb_private')->put($docPath, '%PDF-dummy');

        RegistrationDocument::create([
            'registration_id' => $registration->id,
            'document_requirement_id' => $req->id,
            'file_path' => $docPath,
            'original_name' => 'kk.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 10,
            'file_hash' => hash('sha256', '%PDF-dummy'),
            'uploaded_at' => now(),
        ]);

        return [
            'user' => $user,
            'applicant' => $applicant,
            'registration' => $registration,
        ];
    }
}
