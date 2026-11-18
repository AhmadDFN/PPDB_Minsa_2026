<?php

declare(strict_types=1);

namespace App\Actions\Document;

use App\Models\DocumentRequirement;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Models\User;
use App\Services\Document\DocumentUploadService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReplaceRegistrationDocumentAction
{
    public function __construct(
        protected DocumentUploadService $uploadService
    ) {}

    public function execute(
        Registration $registration,
        DocumentRequirement $requirement,
        RegistrationDocument $document,
        User $uploader,
        UploadedFile $file,
        ?string $reason = null
    ): RegistrationDocument {
        $stored = $this->uploadService->store($file, $registration, $requirement);

        try {
            return DB::transaction(function () use ($registration, $requirement, $document, $uploader, $stored, $reason): RegistrationDocument {
                $current = RegistrationDocument::query()
                    ->whereKey($document->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $current->registration_id !== $registration->id
                    || $current->document_requirement_id !== $requirement->id
                ) {
                    throw (new ModelNotFoundException)->setModel(RegistrationDocument::class, [$document->id]);
                }

                $revisionNumber = (int) $current->revisions()->max('revision_number') + 1;
                $current->revisions()->create([
                    'revision_number' => $revisionNumber,
                    'file_path' => $current->file_path,
                    'original_name' => $current->original_name,
                    'mime_type' => $current->mime_type,
                    'file_size_bytes' => $current->file_size_bytes,
                    'file_hash' => $current->file_hash,
                    'uploaded_by' => $uploader->id,
                    'replaced_at' => now(),
                    'reason' => $reason ?: 'Dokumen diganti oleh pendaftar.',
                ]);

                $current->update([
                    ...$stored,
                    'uploaded_at' => now(),
                ]);

                return $current->fresh();
            });
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
                'Penyimpanan penggantian dokumen gagal dan berkas yatim tidak dapat dibersihkan.',
                previous: $cleanupFailure,
            );
        }

        throw $original;
    }
}
