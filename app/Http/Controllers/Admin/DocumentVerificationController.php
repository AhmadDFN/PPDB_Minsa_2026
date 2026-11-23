<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\DocumentVerificationStatus;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\Program;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Services\AdminConfigurationAuditService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class DocumentVerificationController
{
    public function __construct(private readonly AdminConfigurationAuditService $audit) {}

    public function index(Request $request): View
    {
        $query = Registration::query()->submitted()->with(['applicant', 'admissionPeriod', 'admissionPeriodProgram.program', 'documents.requirement']);
        $query->when($request->filled('search'), fn (Builder $q) => $q->where(fn (Builder $x) => $x->where('registration_number', 'like', '%'.$request->string('search').'%')->orWhereHas('applicant', fn (Builder $a) => $a->where('full_name', 'like', '%'.$request->string('search').'%'))));
        $query->when($request->filled('period_id'), fn (Builder $q) => $q->where('admission_period_id', $request->integer('period_id')));
        $query->when($request->filled('program_id'), fn (Builder $q) => $q->whereHas('admissionPeriodProgram', fn (Builder $p) => $p->where('program_id', $request->integer('program_id'))));
        $query->when($request->filled('verification_status'), fn (Builder $q) => $q->whereHas('documents', fn (Builder $d) => $d->where('verification_status', $request->string('verification_status'))));
        $registrations = $query->latest('submitted_at')->paginate(15)->withQueryString();
        $submittedDocumentIds = RegistrationDocument::query()->whereHas('registration', fn (Builder $q) => $q->submitted())->select('id');
        $summary = [
            'complete' => Registration::submitted()->whereHas('documents')->whereDoesntHave('documents', fn (Builder $q) => $q->where('verification_status', '!=', DocumentVerificationStatus::APPROVED->value))->count(),
            'pending' => (clone $submittedDocumentIds)->where('verification_status', DocumentVerificationStatus::PENDING->value)->count(),
            'approved' => (clone $submittedDocumentIds)->where('verification_status', DocumentVerificationStatus::APPROVED->value)->count(),
            'revision' => (clone $submittedDocumentIds)->whereIn('verification_status', [DocumentVerificationStatus::REVISION_REQUIRED->value, DocumentVerificationStatus::REJECTED->value])->count(),
        ];

        return view('admin.document-verification.index', ['registrations' => $registrations, 'periods' => AdmissionPeriod::latest('start_date')->get(), 'programs' => Program::where('is_active', true)->orderBy('name')->get(), 'statuses' => DocumentVerificationStatus::cases(), 'summary' => $summary]);
    }

    public function preview(Registration $registration, RegistrationDocument $document): BinaryFileResponse
    {
        $this->ensureDocument($registration, $document);
        $disk = Storage::disk((string) config('ppdb.documents.disk', 'ppdb_private'));
        abort_unless($disk->exists($document->file_path), 404);
        abort_unless(in_array($document->mime_type, ['application/pdf', 'image/jpeg', 'image/png'], true), 404);

        return response()->file($disk->path($document->file_path), ['Content-Type' => $document->mime_type, 'Content-Disposition' => 'inline', 'Cache-Control' => 'private, no-store, max-age=0', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function approve(Request $request, Registration $registration, RegistrationDocument $document): RedirectResponse
    {
        return $this->changeStatus($request, $registration, $document, DocumentVerificationStatus::APPROVED);
    }

    public function reject(Request $request, Registration $registration, RegistrationDocument $document): RedirectResponse
    {
        return $this->changeStatus($request, $registration, $document, DocumentVerificationStatus::REJECTED);
    }

    public function revision(Request $request, Registration $registration, RegistrationDocument $document): RedirectResponse
    {
        return $this->changeStatus($request, $registration, $document, DocumentVerificationStatus::REVISION_REQUIRED);
    }

    public function history(Registration $registration, RegistrationDocument $document): View
    {
        $this->ensureDocument($registration, $document);

        return view('admin.document-verification.history', ['registration' => $registration, 'document' => $document->load('verificationHistory.actor')]);
    }

    private function changeStatus(Request $request, Registration $registration, RegistrationDocument $document, DocumentVerificationStatus $status): RedirectResponse
    {
        $this->ensureDocument($registration, $document);
        $data = $request->validate(['reason' => [$status === DocumentVerificationStatus::APPROVED ? 'nullable' : 'required', 'string', 'max:255']]);
        try {
            DB::transaction(function () use ($request, $registration, $document, $status, $data): void {
                $lockedRegistration = Registration::query()->lockForUpdate()->findOrFail($registration->id);
                $lockedDocument = RegistrationDocument::query()->lockForUpdate()->findOrFail($document->id);
                if ($lockedRegistration->status !== RegistrationStatus::SUBMITTED || $lockedDocument->registration_id !== $lockedRegistration->id) {
                    abort(422, 'Dokumen hanya dapat diverifikasi pada pendaftaran terkirim.');
                }
                if ($lockedDocument->verification_status === $status) {
                    abort(422, 'Status dokumen sudah sama.');
                }
                $before = ['verification_status' => $lockedDocument->verification_status?->value, 'verification_reason' => $lockedDocument->verification_reason];
                $lockedDocument->update(['verification_status' => $status, 'verification_reason' => $data['reason'] ?? null, 'verified_by' => $request->user()->id, 'verified_at' => now()]);
                $lockedDocument->verificationHistory()->create(['actor_id' => $request->user()->id, 'from_status' => $before['verification_status'], 'to_status' => $status, 'reason' => $data['reason'] ?? null, 'created_at' => now()]);
                $this->audit->record('registration_document', $lockedDocument->id, 'verification_'.$status->value, $request->user(), $before, ['verification_status' => $status->value, 'verification_reason' => $data['reason'] ?? null]);
            });
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Status verifikasi gagal disimpan.');
        }

        return back()->with('success', 'Status dokumen berhasil diperbarui.');
    }

    private function ensureDocument(Registration $registration, RegistrationDocument $document): void
    {
        abort_unless($registration->status === RegistrationStatus::SUBMITTED && $document->registration_id === $registration->id, 404);
    }
}
