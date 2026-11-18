<?php

declare(strict_types=1);

namespace App\Services\Document;

use App\Models\Registration;
use Illuminate\Support\Facades\Storage;

class DocumentCompletenessService
{
    public function __construct(
        protected DocumentRequirementService $requirementService
    ) {}

    /**
     * @return array{
     *     required_count: int,
     *     uploaded_count: int,
     *     missing: list<string>,
     *     percentage: int,
     *     is_complete: bool
     * }
     */
    public function summarize(?Registration $registration): array
    {
        if (! $registration) {
            return [
                'required_count' => 0,
                'uploaded_count' => 0,
                'missing' => [],
                'percentage' => 0,
                'is_complete' => false,
            ];
        }

        $configuredRequirements = $this->requirementService->forRegistration($registration);
        $required = $configuredRequirements
            ->where('is_required', true)
            ->values();

        if ($required->isEmpty()) {
            return [
                'required_count' => 0,
                'uploaded_count' => 0,
                'missing' => [],
                'percentage' => $configuredRequirements->isEmpty() ? 0 : 100,
                'is_complete' => $configuredRequirements->isNotEmpty(),
            ];
        }

        $documents = $registration->documents()
            ->whereIn('document_requirement_id', $required->modelKeys())
            ->get()
            ->keyBy('document_requirement_id');
        $disk = Storage::disk((string) config('ppdb.documents.disk'));
        $missing = [];
        $uploadedCount = 0;

        foreach ($required as $requirement) {
            $document = $documents->get($requirement->id);

            if (! $document || ! $disk->exists($document->file_path)) {
                $missing[] = $requirement->name;

                continue;
            }

            $uploadedCount++;
        }

        return [
            'required_count' => $required->count(),
            'uploaded_count' => $uploadedCount,
            'missing' => $missing,
            'percentage' => (int) round(($uploadedCount / $required->count()) * 100),
            'is_complete' => $missing === [],
        ];
    }
}
