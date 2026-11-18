<?php

declare(strict_types=1);

namespace App\Http\Controllers\Applicant;

use App\Actions\Document\ReplaceRegistrationDocumentAction;
use App\Actions\Document\UploadRegistrationDocumentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Applicant\UploadDocumentRequest;
use App\Models\RegistrationDocument;
use App\Models\RegistrationDocumentRevision;
use App\Services\Document\DocumentCompletenessService;
use App\Services\Document\DocumentRequirementService;
use App\Services\Document\DocumentUploadService;
use App\Services\Registration\RegistrationEditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentRequirementService $requirementService,
        protected DocumentCompletenessService $completenessService,
        protected RegistrationEditService $registrationEditService,
        protected DocumentUploadService $uploadService
    ) {}

    public function index(Request $request): View
    {
        $registration = $this->requirementService->activeRegistrationFor($request->user()->applicant);
        $requirements = $registration
            ? $this->requirementService->forRegistration($registration)
            : collect();
        $documents = $registration
            ? $registration->documents()->with('revisions')->get()->keyBy('document_requirement_id')
            : collect();
        $canEdit = $registration?->admissionPeriod?->isOpen() ?? false;

        return view('applicant.documents.index', [
            'registration' => $registration,
            'requirements' => $requirements,
            'documents' => $documents,
            'completeness' => $this->completenessService->summarize($registration),
            'globalMaxSizeKb' => min(5120, (int) config('ppdb.documents.global_max_size_kb', 5120)),
            'canEdit' => $canEdit,
        ]);
    }

    public function upload(
        UploadDocumentRequest $request,
        string $requirementId,
        UploadRegistrationDocumentAction $uploadAction,
        ReplaceRegistrationDocumentAction $replaceAction
    ): RedirectResponse {
        $registration = $this->requirementService->activeRegistrationFor($request->user()->applicant);
        abort_unless($registration, 404);

        $requirement = $this->requirementService->findForRegistrationOrFail($requirementId, $registration);
        $document = $registration->documents()
            ->where('document_requirement_id', $requirement->id)
            ->first();
        $file = $request->validated('file');

        $this->registrationEditService->run(
            $request->user()->applicant,
            $request->user(),
            $request->ip(),
            $request->userAgent(),
            function () use (
                $document,
                $registration,
                $requirement,
                $request,
                $file,
                $replaceAction,
                $uploadAction
            ): RegistrationDocument {
                if ($document) {
                    return $replaceAction->execute(
                        $registration,
                        $requirement,
                        $document,
                        $request->user(),
                        $file,
                        $request->validated('reason'),
                    );
                }

                return $uploadAction->execute($registration, $requirement, $file);
            },
            fn (RegistrationDocument $storedDocument): mixed => $this->uploadService->deleteStoredFile($storedDocument->file_path),
        );

        return redirect()
            ->route('applicant.documents.index')
            ->with('success', 'Dokumen berhasil disimpan secara privat.');
    }

    public function preview(Request $request, string $documentId): BinaryFileResponse
    {
        $document = $this->ownedDocument($request, $documentId);

        return $this->inlineResponse($document->file_path, $document->original_name, $document->mime_type);
    }

    public function download(Request $request, string $documentId): StreamedResponse
    {
        $document = $this->ownedDocument($request, $documentId);
        $disk = Storage::disk((string) config('ppdb.documents.disk'));
        abort_unless($disk->exists($document->file_path), 404);

        return $disk->download($document->file_path, $document->original_name, [
            'Content-Type' => $document->mime_type,
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function previewRevision(Request $request, string $documentId, string $revisionId): BinaryFileResponse
    {
        $revision = $this->ownedRevision($request, $documentId, $revisionId);

        return $this->inlineResponse($revision->file_path, $revision->original_name, $revision->mime_type);
    }

    public function downloadRevision(Request $request, string $documentId, string $revisionId): StreamedResponse
    {
        $revision = $this->ownedRevision($request, $documentId, $revisionId);
        $disk = Storage::disk((string) config('ppdb.documents.disk'));
        abort_unless($disk->exists($revision->file_path), 404);

        return $disk->download($revision->file_path, $revision->original_name, [
            'Content-Type' => $revision->mime_type,
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function ownedDocument(Request $request, string $documentId): RegistrationDocument
    {
        return RegistrationDocument::query()
            ->whereHas('registration', fn ($query) => $query->where('applicant_id', $request->user()->applicant->id))
            ->findOrFail($documentId);
    }

    private function ownedRevision(Request $request, string $documentId, string $revisionId): RegistrationDocumentRevision
    {
        return RegistrationDocumentRevision::query()
            ->whereHas('document', fn ($query) => $query
                ->whereKey($documentId)
                ->whereHas('registration', fn ($registrationQuery) => $registrationQuery
                    ->where('applicant_id', $request->user()->applicant->id)))
            ->findOrFail($revisionId);
    }

    private function inlineResponse(string $path, string $originalName, string $mimeType): BinaryFileResponse
    {
        $disk = Storage::disk((string) config('ppdb.documents.disk'));
        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                $originalName,
            ),
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
