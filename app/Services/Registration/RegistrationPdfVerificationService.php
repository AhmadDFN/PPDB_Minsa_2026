<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Enums\PdfDocumentType;
use App\Models\RegistrationPdfVersion;
use Illuminate\Support\Str;

class RegistrationPdfVerificationService
{
    public function __construct(private RegistrationPdfFileService $fileService) {}

    /**
     * @return array{
     *     status: string,
     *     registration_number: string,
     *     generated_at: ?string,
     *     fingerprint: string
     * }|null
     */
    public function inspect(string $token, ?string $registrationNumber): ?array
    {
        if (
            preg_match('/\A[a-f0-9]{64}\z/i', $token) !== 1
            || $registrationNumber === null
        ) {
            return null;
        }

        $version = RegistrationPdfVersion::query()
            ->where('document_type', PdfDocumentType::FINAL->value)
            ->where('verification_token_hash', hash('sha256', $token))
            ->with('registration')
            ->first();

        if ($version === null) {
            return null;
        }

        $registration = $version->registration;
        if (
            $registration->registration_number === null
            || ! hash_equals($registration->registration_number, $registrationNumber)
        ) {
            return null;
        }

        $fileIsVerified = $this->fileService->readVerified($version) !== null;
        $latestVersionNumber = $registration->pdfVersions()
            ->where('document_type', PdfDocumentType::FINAL->value)
            ->max('version_number');

        $status = 'unavailable';
        if ($fileIsVerified) {
            $status = ! $registration->isSubmitted() || $version->isOutdated()
                ? 'outdated'
                : ((int) $latestVersionNumber === $version->version_number ? 'current' : 'superseded');
        }

        return [
            'status' => $status,
            'registration_number' => (string) $registration->registration_number,
            'generated_at' => $version->generated_at?->copy()->locale('id')->translatedFormat('d F Y, H:i').' WIB',
            'fingerprint' => Str::upper(substr($version->file_hash, 0, 16)),
        ];
    }
}
