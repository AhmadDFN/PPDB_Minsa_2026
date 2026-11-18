<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PdfDocumentType;
use App\Models\Registration;
use App\Models\RegistrationPdfVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationPdfVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_token_shows_minimal_public_verification_without_applicant_pii(): void
    {
        Storage::fake('ppdb_private');
        $token = str_repeat('a', 64);
        $version = $this->createFinalVersion($token);

        $this->get(route('public.registration-pdfs.verify', [
            'token' => $token,
            'registration_number' => $version->registration->registration_number,
        ]))
            ->assertOk()
            ->assertSee('Dokumen valid dan merupakan versi terbaru.')
            ->assertSee($version->registration->registration_number)
            ->assertSee(strtoupper(substr($version->file_hash, 0, 16)))
            ->assertDontSee($version->registration->applicant->full_name)
            ->assertDontSee($version->registration->applicant->user->getDecryptedNik())
            ->assertDontSee('Unduh PDF');
    }

    public function test_verification_rejects_a_mismatched_registration_number(): void
    {
        Storage::fake('ppdb_private');
        $token = str_repeat('b', 64);
        $version = $this->createFinalVersion($token);

        $this->get(route('public.registration-pdfs.verify', [
            'token' => $token,
            'registration_number' => 'PPDB/WRONG/00000',
        ]))->assertNotFound();
    }

    public function test_verification_marks_old_or_corrupted_pdf_as_unverified(): void
    {
        Storage::fake('ppdb_private');
        $token = str_repeat('c', 64);
        $version = $this->createFinalVersion($token);
        $version->registration->update(['data_revision' => 2]);

        $this->get(route('public.registration-pdfs.verify', [
            'token' => $token,
            'registration_number' => $version->registration->registration_number,
        ]))
            ->assertOk()
            ->assertSee('data pendaftaran telah berubah');

        Storage::disk('ppdb_private')->put($version->file_path, "%PDF-1.7\nTampered content");

        $this->get(route('public.registration-pdfs.verify', [
            'token' => $token,
            'registration_number' => $version->registration->registration_number,
        ]))
            ->assertOk()
            ->assertSee('Dokumen tidak dapat diverifikasi.');
    }

    public function test_unknown_verification_token_returns_404(): void
    {
        $this->get(route('public.registration-pdfs.verify', [
            'token' => str_repeat('d', 64),
            'registration_number' => 'PPDB/2026/REGULER/00001',
        ]))->assertNotFound();
    }

    private function createFinalVersion(string $token): RegistrationPdfVersion
    {
        $registration = Registration::factory()->submitted()->create([
            'registration_number' => 'PPDB/2026/REGULER/'.substr($token, 0, 5),
            'data_revision' => 1,
        ]);
        $path = 'ppdb/pdfs/'.$registration->id.'/final/version-1.pdf';
        $contents = "%PDF-1.7\nVerified registration document";
        Storage::disk('ppdb_private')->put($path, $contents);

        return RegistrationPdfVersion::factory()->final()->create([
            'registration_id' => $registration->id,
            'document_type' => PdfDocumentType::FINAL,
            'version_number' => 1,
            'data_revision' => 1,
            'file_path' => $path,
            'file_hash' => hash('sha256', $contents),
            'storage_disk' => 'ppdb_private',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen($contents),
            'verification_token_hash' => hash('sha256', $token),
        ]);
    }
}
