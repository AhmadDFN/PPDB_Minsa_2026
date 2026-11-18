<?php

declare(strict_types=1);

namespace Tests\Feature\Applicant;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AdmissionPeriodStatus;
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
use App\Models\RegistrationConsent;
use App\Models\RegistrationDocument;
use App\Models\RegistrationLog;
use App\Models\RegistrationPdfVersion;
use App\Models\User;
use App\Services\Registration\RegistrationPdfRenderer;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Dompdf\Canvas;
use Dompdf\FontMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class RegistrationPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_incomplete_draft_creates_private_pdf_with_integrity_metadata(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration(complete: false);

        $response = $this->actingAs($data['user'])
            ->post(route('applicant.registration.pdf.draft'));

        $response->assertRedirect(route('applicant.dashboard'))
            ->assertSessionHas('success', 'PDF draf berhasil dibuat.');

        $version = RegistrationPdfVersion::query()->firstOrFail();
        $contents = Storage::disk('ppdb_private')->get($version->file_path);
        $this->assertSame(PdfDocumentType::DRAFT, $version->document_type);
        $this->assertSame('application/pdf', $version->mime_type);
        $this->assertSame(strlen($contents), $version->file_size_bytes);
        $this->assertSame(hash('sha256', $contents), $version->file_hash);
        $this->assertNull($version->verification_token_hash);
        $this->assertStringStartsWith('%PDF-', $contents);
        Storage::disk('ppdb_private')->assertExists($version->file_path);
    }

    public function test_draft_renderer_receives_masked_nik_and_incomplete_profile_data(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration(complete: false);
        $expectedNik = $data['user']->getDecryptedNik();
        $expectedMask = substr($expectedNik, 0, 4).'********'.substr($expectedNik, 12, 4);
        $renderer = $this->mock(RegistrationPdfRenderer::class);
        $renderer->shouldReceive('render')
            ->once()
            ->withArgs(function (PdfDocumentType $type, array $documentData, ?string $verificationUrl) use ($expectedMask): bool {
                $this->assertSame(PdfDocumentType::DRAFT, $type);
                $this->assertSame($expectedMask, $documentData['applicant']['nik']);
                $this->assertNull($documentData['consent']);
                $this->assertNull($verificationUrl);

                return true;
            })
            ->andReturn("%PDF-1.7\nRendered draft");

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.pdf.draft'))
            ->assertSessionHas('success', 'PDF draf berhasil dibuat.');
    }

    public function test_draft_renderer_draws_the_official_watermark_on_each_page(): void
    {
        $documentData = [];
        $pdf = Mockery::mock(DomPdf::class);
        $canvas = Mockery::mock(Canvas::class);
        $fontMetrics = Mockery::mock(FontMetrics::class);

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdf.registration.draft', [
                'document' => $documentData,
                'qrCodeDataUri' => null,
                'verificationUrl' => null,
            ])
            ->andReturn($pdf);
        $pdf->shouldReceive('setPaper')->once()->with('a4', 'portrait')->andReturnSelf();
        $pdf->shouldReceive('setOption')->times(4)->andReturnSelf();
        $pdf->shouldReceive('addInfo')->once()->andReturnSelf();
        $pdf->shouldReceive('render')->once();
        $pdf->shouldReceive('getCanvas')->once()->andReturn($canvas);
        $pdf->shouldReceive('getFontMetrics')->once()->andReturn($fontMetrics);
        $pdf->shouldReceive('output')->once()->with(['compress' => 0])->andReturn('%PDF-1.7');
        $fontMetrics->shouldReceive('getFont')->once()->with('DejaVu Sans', 'normal')->andReturn('DejaVuSans');
        $canvas->shouldReceive('page_text')
            ->once()
            ->with(460, 820, 'Halaman {PAGE_NUM} dari {PAGE_COUNT}', 'DejaVuSans', 8, [0.25, 0.25, 0.25]);
        $canvas->shouldReceive('page_text')
            ->once()
            ->with(78, 440, 'DRAFT - BUKAN BUKTI RESMI', 'DejaVuSans', 34, [0.82, 0.82, 0.82], 0, 0, 45);

        $renderedPdf = app(RegistrationPdfRenderer::class)->render(PdfDocumentType::DRAFT, $documentData);

        $this->assertSame('%PDF-1.7', $renderedPdf);
    }

    public function test_final_renderer_receives_full_nik_and_historical_consent_snapshot(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        config([
            'ppdb.registration.consent_statement' => 'Consent historis yang disetujui.',
            'ppdb.registration.consent_version' => 'v-historis',
        ]);
        $this->actingAs($data['user'])
            ->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Budi Santoso',
            ])
            ->assertRedirect(route('applicant.registration.review'));

        config([
            'ppdb.registration.consent_statement' => 'Consent konfigurasi terbaru yang berbeda.',
            'ppdb.registration.consent_version' => 'v-baru',
        ]);
        $expectedNik = $data['user']->getDecryptedNik();
        $renderer = $this->mock(RegistrationPdfRenderer::class);
        $renderer->shouldReceive('render')
            ->once()
            ->withArgs(function (PdfDocumentType $type, array $documentData, ?string $verificationUrl) use ($expectedNik): bool {
                $this->assertSame(PdfDocumentType::FINAL, $type);
                $this->assertSame($expectedNik, $documentData['applicant']['nik']);
                $this->assertSame('Consent historis yang disetujui.', $documentData['consent']['statement_text']);
                $this->assertStringContainsString('registration_number=', (string) $verificationUrl);
                $this->assertArrayNotHasKey('nik', $documentData['father']);
                $this->assertArrayNotHasKey('nik', $documentData['mother']);

                return true;
            })
            ->andReturn("%PDF-1.7\nRendered final");

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.pdf.final'))
            ->assertSessionHas('success', 'Bukti PDF final berhasil dibuat.');
    }

    public function test_png_photo_without_gd_returns_an_actionable_error(): void
    {
        if (extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD is installed; Dompdf can render PNG photos.');
        }

        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        $photoRequirement = DocumentRequirement::factory()->create([
            'admission_period_id' => $data['period']->id,
            'program_id' => $data['program']->id,
            'code' => 'PAS_FOTO',
            'name' => 'Pas Foto Calon Siswa',
            'allowed_mimes' => 'image/jpeg,image/png',
            'is_required' => false,
        ]);
        $photoBytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVQIHWP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC',
            true,
        );
        $this->assertIsString($photoBytes);
        $photoPath = 'ppdb/documents/'.$data['registration']->id.'/pas-foto.png';
        Storage::disk('ppdb_private')->put($photoPath, $photoBytes);
        RegistrationDocument::query()->create([
            'registration_id' => $data['registration']->id,
            'document_requirement_id' => $photoRequirement->id,
            'file_path' => $photoPath,
            'original_name' => 'pas-foto.png',
            'mime_type' => 'image/png',
            'file_size_bytes' => strlen($photoBytes),
            'file_hash' => hash('sha256', $photoBytes),
            'uploaded_at' => now(),
        ]);
        config([
            'ppdb.registration.consent_statement' => 'Consent historis yang disetujui.',
            'ppdb.registration.consent_version' => 'v-historis',
        ]);

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Budi Santoso',
            ])
            ->assertRedirect(route('applicant.registration.review'));

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.pdf.final'))
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                'Server belum mendukung pas foto PNG. Aktifkan ekstensi PHP GD atau unggah pas foto dalam format JPG.',
            );
    }

    public function test_final_pdf_persists_version_preserves_old_files_and_renders_photo(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        $photoRequirement = DocumentRequirement::factory()->create([
            'admission_period_id' => $data['period']->id,
            'program_id' => $data['program']->id,
            'code' => 'PAS_FOTO',
            'name' => 'Pas Foto Calon Siswa',
            'allowed_mimes' => 'image/jpeg,image/png',
            'is_required' => false,
        ]);
        $photoBytes = base64_decode(
            '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAX/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAGf/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABBQJ//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAwEBPwF//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAgEBPwF//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQAGPwJ//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPyF//9oADAMBAAIRAxEAPwD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAwE/EAf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAECAwE/EAf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAwE/EAf/2Q==',
            true,
        );
        $this->assertIsString($photoBytes);
        $photoPath = 'ppdb/documents/'.$data['registration']->id.'/pas-foto.jpg';
        Storage::disk('ppdb_private')->put($photoPath, $photoBytes);
        RegistrationDocument::query()->create([
            'registration_id' => $data['registration']->id,
            'document_requirement_id' => $photoRequirement->id,
            'file_path' => $photoPath,
            'original_name' => 'pas-foto.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => strlen($photoBytes),
            'file_hash' => hash('sha256', $photoBytes),
            'uploaded_at' => now(),
        ]);
        config([
            'ppdb.registration.consent_statement' => 'Consent historis yang disetujui.',
            'ppdb.registration.consent_version' => 'v-historis',
        ]);
        $this->actingAs($data['user'])
            ->post(route('applicant.registration.pdf.draft'))
            ->assertSessionHas('success');

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Budi Santoso',
            ])
            ->assertRedirect(route('applicant.registration.review'));

        $response = $this->actingAs($data['user'])
            ->post(route('applicant.registration.pdf.final'));

        $response->assertRedirect(route('applicant.dashboard'))
            ->assertSessionHas('success', 'Bukti PDF final berhasil dibuat.');

        $finalVersion = RegistrationPdfVersion::query()
            ->where('document_type', PdfDocumentType::FINAL)
            ->firstOrFail();
        $contents = Storage::disk('ppdb_private')->get($finalVersion->file_path);
        $consent = RegistrationConsent::query()
            ->where('registration_id', $data['registration']->id)
            ->firstOrFail();

        $this->assertSame('Consent historis yang disetujui.', $consent->statement_text);
        $this->assertSame($finalVersion->data_revision, $data['registration']->fresh()->data_revision);
        $this->assertNotNull($finalVersion->verification_token_hash);
        $this->assertSame(64, strlen($finalVersion->verification_token_hash));
        $draftVersion = RegistrationPdfVersion::query()
            ->where('document_type', PdfDocumentType::DRAFT)
            ->firstOrFail();
        Storage::disk('ppdb_private')->assertExists($draftVersion->file_path);

        $this->actingAs($data['user'])
            ->get(route('applicant.dashboard'))
            ->assertOk()
            ->assertSee('Arsip draf sebelum pengiriman')
            ->assertSee('Versi terbaru sesuai data');

        $this->actingAs($data['user'])
            ->get(route('applicant.registration.pdf.download', $finalVersion))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertContent($contents);

        $oldFinalPath = $finalVersion->file_path;
        $this->actingAs($data['user'])
            ->post(route('applicant.biodata.update'), [
                'full_name' => 'Nama PDF Diperbarui',
                'gender' => 'L',
                'birth_place' => 'Kota Madiun',
                'birth_date' => '2020-01-15',
                'nisn' => $data['applicant']->nisn,
                'child_order' => 1,
                'siblings_count' => 0,
            ])
            ->assertSessionHas('success');

        $this->assertSame(2, $data['registration']->fresh()->data_revision);
        $this->actingAs($data['user'])
            ->get(route('applicant.dashboard'))
            ->assertSee('Data berubah - perlu diperbarui');

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.pdf.final'))
            ->assertSessionHas('success');

        $versions = RegistrationPdfVersion::query()
            ->where('registration_id', $data['registration']->id)
            ->where('document_type', PdfDocumentType::FINAL)
            ->orderBy('version_number')
            ->get();

        $this->assertCount(2, $versions);
        $this->assertSame(1, $versions[0]->version_number);
        $this->assertSame(2, $versions[1]->version_number);
        $this->assertSame(1, $versions[0]->data_revision);
        $this->assertSame(2, $versions[1]->data_revision);
        Storage::disk('ppdb_private')->assertExists($oldFinalPath);
        Storage::disk('ppdb_private')->assertExists($versions[1]->file_path);
    }

    public function test_final_pdf_generation_is_rejected_for_a_draft_registration(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.pdf.final'))
            ->assertRedirect()
            ->assertSessionHas('error', 'Bukti PDF final hanya dapat dibuat setelah pendaftaran berhasil dikirim.');

        $this->assertDatabaseCount('registration_pdf_versions', 0);
        $this->assertSame([], Storage::disk('ppdb_private')->allFiles('ppdb/pdfs'));
    }

    public function test_pdf_download_returns_404_for_another_applicants_version(): void
    {
        Storage::fake('ppdb_private');
        $owner = $this->createRegistration(complete: false);
        $otherApplicant = $this->createRegistration(complete: false);
        $path = 'ppdb/pdfs/'.$owner['registration']->id.'/draft/private.pdf';
        $contents = "%PDF-1.7\nPrivate registration PDF";
        Storage::disk('ppdb_private')->put($path, $contents);
        $version = RegistrationPdfVersion::factory()->draft()->create([
            'registration_id' => $owner['registration']->id,
            'generated_by' => $owner['user']->id,
            'file_path' => $path,
            'file_hash' => hash('sha256', $contents),
            'storage_disk' => 'ppdb_private',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen($contents),
        ]);

        $this->actingAs($otherApplicant['user'])
            ->get(route('applicant.registration.pdf.download', $version))
            ->assertNotFound();
    }

    public function test_database_failure_removes_the_new_pdf_and_records_safe_failure_details(): void
    {
        Storage::fake('ppdb_private');
        $data = $this->createRegistration();
        $this->actingAs($data['user'])
            ->post(route('applicant.registration.submit'), [
                'consent' => '1',
                'consent_name' => 'Budi Santoso',
            ])
            ->assertRedirect(route('applicant.registration.review'));

        DB::statement(
            "CREATE TRIGGER fail_pdf_version_insert
             BEFORE INSERT ON registration_pdf_versions
             BEGIN SELECT RAISE(ABORT, 'simulated persistence failure'); END"
        );

        $this->actingAs($data['user'])
            ->post(route('applicant.registration.pdf.final'))
            ->assertRedirect()
            ->assertSessionHas('error', 'PDF tidak dapat dibuat saat ini. Silakan coba kembali.');

        $this->assertDatabaseCount('registration_pdf_versions', 0);
        $this->assertSame([], Storage::disk('ppdb_private')->allFiles('ppdb/pdfs'));
        $failureLog = RegistrationLog::query()
            ->where('registration_id', $data['registration']->id)
            ->where('action', 'pdf_generation_failed')
            ->firstOrFail();
        $this->assertSame([
            'document_type' => 'final',
            'error_code' => 'generation_failed',
        ], $failureLog->changes);
        $this->assertStringNotContainsString(
            $data['user']->getDecryptedNik(),
            json_encode($failureLog->changes, JSON_THROW_ON_ERROR),
        );
    }

    private function createRegistration(bool $complete = true): array
    {
        $user = User::factory()->applicant()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $applicant = Applicant::factory()->create([
            'user_id' => $user->id,
            'full_name' => 'Calon PDF Lengkap',
            'gender' => 'L',
            'birth_place' => 'Kota Madiun',
            'birth_date' => '2020-01-15',
            'nisn' => '0012345678',
            'child_order' => $complete ? 1 : null,
            'siblings_count' => $complete ? 0 : null,
        ]);
        $period = AdmissionPeriod::factory()->create([
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);
        $program = Program::query()->first() ?? Program::factory()->reguler()->create();
        $periodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
            'is_active' => true,
        ]);
        $registration = Registration::query()->create([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $periodProgram->id,
            'status' => RegistrationStatus::DRAFT,
            'data_revision' => 1,
        ]);
        $requirement = DocumentRequirement::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
            'code' => 'AKTA_KELAHIRAN',
            'name' => 'Akta Kelahiran',
            'allowed_mimes' => 'application/pdf,image/jpeg,image/png',
            'is_required' => true,
        ]);

        if ($complete) {
            ApplicantAddress::factory()->create([
                'applicant_id' => $applicant->id,
                'address_line' => 'Jalan Merpati Nomor 10',
                'kelurahan' => 'Kejuron',
                'kecamatan' => 'Taman',
                'city' => 'Kota Madiun',
                'province' => 'Jawa Timur',
            ]);
            ApplicantGuardian::factory()->father()->create([
                'applicant_id' => $applicant->id,
                'name' => 'Budi Santoso',
                'nik' => '3573010101750001',
                'education' => 'SMA/SMK',
                'occupation' => 'Wiraswasta',
                'monthly_income' => '3.000.000 - 5.000.000',
                'phone' => '081234567890',
            ]);
            ApplicantGuardian::factory()->mother()->create([
                'applicant_id' => $applicant->id,
                'name' => 'Siti Aminah',
                'nik' => '3573010101850002',
                'education' => 'SMA/SMK',
                'occupation' => 'Guru',
                'monthly_income' => '3.000.000 - 5.000.000',
                'phone' => '081234567891',
            ]);
            ApplicantEducation::factory()->create([
                'applicant_id' => $applicant->id,
                'education_level' => PreviousEducationLevel::NONE,
                'school_name' => null,
            ]);

            $documentPath = 'ppdb/documents/'.$registration->id.'/akta.pdf';
            $documentContents = "%PDF-1.4\nRequired document";
            Storage::disk('ppdb_private')->put($documentPath, $documentContents);
            RegistrationDocument::query()->create([
                'registration_id' => $registration->id,
                'document_requirement_id' => $requirement->id,
                'file_path' => $documentPath,
                'original_name' => 'akta.pdf',
                'mime_type' => 'application/pdf',
                'file_size_bytes' => strlen($documentContents),
                'file_hash' => hash('sha256', $documentContents),
                'uploaded_at' => now(),
            ]);
        }

        return [
            'user' => $user,
            'applicant' => $applicant,
            'period' => $period,
            'program' => $program,
            'period_program' => $periodProgram,
            'registration' => $registration,
            'requirement' => $requirement,
        ];
    }
}
