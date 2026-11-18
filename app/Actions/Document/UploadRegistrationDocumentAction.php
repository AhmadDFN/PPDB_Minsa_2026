<?php

declare(strict_types=1);

namespace App\Actions\Document;

use App\Models\DocumentRequirement;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Services\Document\DocumentUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class UploadRegistrationDocumentAction
{
    public function __construct(
        protected DocumentUploadService $uploadService
    ) {}

    public function execute(
        Registration $registration,
        DocumentRequirement $requirement,
        UploadedFile $file
    ): RegistrationDocument {
        $stored = $this->uploadService->store($file, $registration, $requirement);

        try {
            return DB::transaction(fn (): RegistrationDocument => RegistrationDocument::create([
                'registration_id' => $registration->id,
                'document_requirement_id' => $requirement->id,
                ...$stored,
                'uploaded_at' => now(),
            ]));
        } catch (Throwable $exception) {
            $this->cleanupAfterFailure($stored['file_path'], $exception);
        }
    }

    private function cleanupAfterFailure(string $path, Throwable $original): never
    {
        try {
            $this->uploadService->deleteStoredFile($path);
        } catch (Throwable $cleanupFailure) {
            throw new \RuntimeException(
                'Penyimpanan data dokumen gagal dan berkas yatim tidak dapat dibersihkan.',
                previous: $cleanupFailure,
            );
        }

        throw $original;
    }
}
