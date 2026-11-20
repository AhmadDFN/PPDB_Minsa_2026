<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\AdmissionPeriod;
use App\Models\DocumentRequirement;
use App\Models\Program;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Support\NikManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantManagementController extends Controller
{
    /**
     * Display a paginated, searchable, and filterable list of applicants.
     */
    public function index(Request $request): View
    {
        $query = Registration::query()
            ->with([
                'applicant.user',
                'admissionPeriod',
                'admissionPeriodProgram.program',
                'documents',
            ]);

        // 1. Text Search: Student Name, Registration Number, or 16-digit NIK
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $cleanNumeric = preg_replace('/\D/', '', $search);

            $query->where(function (Builder $builder) use ($search, $cleanNumeric) {
                // Search by registration number
                $builder->where('registration_number', 'like', "%{$search}%");

                // Search by student full name
                $builder->orWhereHas('applicant', function (Builder $applicantQuery) use ($search) {
                    $applicantQuery->where('full_name', 'like', "%{$search}%");
                });

                // If input looks like a valid 16-digit NIK, search deterministically via lookup hash
                if (strlen($cleanNumeric) === 16) {
                    try {
                        $nikHash = NikManager::hash($cleanNumeric);
                        $builder->orWhereHas('applicant.user', function (Builder $userQuery) use ($nikHash) {
                            $userQuery->where('nik_lookup_hash', $nikHash);
                        });
                    } catch (\Throwable) {
                        // Ignore hash failure, search was handled by text conditions
                    }
                }
            });
        }

        // 2. Filter by Admission Period
        if ($request->filled('period_id')) {
            $query->where('admission_period_id', $request->integer('period_id'));
        }

        // 3. Filter by Program
        if ($request->filled('program_id')) {
            $programId = $request->integer('program_id');
            $query->whereHas('admissionPeriodProgram', function (Builder $appQuery) use ($programId) {
                $appQuery->where('program_id', $programId);
            });
        }

        // 4. Filter by Status (DRAFT / SUBMITTED)
        if ($request->filled('status')) {
            $statusVal = strtoupper(trim((string) $request->input('status')));
            if (in_array($statusVal, [RegistrationStatus::DRAFT->value, RegistrationStatus::SUBMITTED->value], true)) {
                $query->where('status', $statusVal);
            }
        }

        // Order: prioritize submitted registrations, latest submission first, then newest draft
        $registrations = $query
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [RegistrationStatus::SUBMITTED->value])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Filter options for dropdowns
        $periods = AdmissionPeriod::query()
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get(['id', 'name', 'academic_year', 'is_active', 'status']);

        $programs = Program::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('admin.applicants.index', [
            'registrations' => $registrations,
            'periods' => $periods,
            'programs' => $programs,
            'filters' => [
                'search' => $request->input('search', ''),
                'period_id' => $request->input('period_id', ''),
                'program_id' => $request->input('program_id', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    /**
     * Display comprehensive details of a single registration across 11 structured sections.
     */
    public function show(Registration $registration): View
    {
        $registration->loadMissing([
            'applicant.user',
            'applicant.address',
            'applicant.father',
            'applicant.mother',
            'applicant.guardian',
            'applicant.education',
            'admissionPeriod',
            'admissionPeriodProgram.program',
            'documents.requirement',
            'documents.revisions',
            'consent',
            'logs.user',
            'reopenWindows',
        ]);

        $applicant = $registration->applicant;
        $period = $registration->admissionPeriod;

        // Dynamic age calculation at period reference date
        $ageInfo = null;
        if ($applicant && $period) {
            $refDate = $period->age_reference_date ?? $period->start_date ?? now();
            $ageInfo = $applicant->calculateAgeAt($refDate);
        }

        // Requirements list for this period and program to evaluate uploaded vs missing documents
        $allRequirements = collect();
        if ($period) {
            $reqQuery = DocumentRequirement::query()
                ->where('admission_period_id', $period->id);

            if ($registration->admissionPeriodProgram?->program_id) {
                $reqQuery->where(function (Builder $b) use ($registration) {
                    $b->where('program_id', $registration->admissionPeriodProgram->program_id)
                        ->orWhereNull('program_id');
                });
            }

            $allRequirements = $reqQuery->orderBy('is_required', 'desc')->get();
        }

        return view('admin.applicants.show', [
            'registration' => $registration,
            'applicant' => $applicant,
            'period' => $period,
            'ageInfo' => $ageInfo,
            'allRequirements' => $allRequirements,
        ]);
    }

    /**
     * Preview an applicant uploaded document securely in browser.
     */
    public function previewDocument(Registration $registration, RegistrationDocument $document): BinaryFileResponse
    {
        abort_unless($document->registration_id === $registration->id, 404);

        $diskName = (string) config('ppdb.documents.disk', 'ppdb_private');
        $disk = Storage::disk($diskName);
        abort_unless($disk->exists($document->file_path), 404);

        $path = $disk->path($document->file_path);

        return response()->file($path, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="'.addslashes($document->original_name).'"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Download an applicant uploaded document securely.
     */
    public function downloadDocument(Registration $registration, RegistrationDocument $document): StreamedResponse
    {
        abort_unless($document->registration_id === $registration->id, 404);

        $diskName = (string) config('ppdb.documents.disk', 'ppdb_private');
        $disk = Storage::disk($diskName);
        abort_unless($disk->exists($document->file_path), 404);

        return $disk->download($document->file_path, $document->original_name, [
            'Content-Type' => $document->mime_type,
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
