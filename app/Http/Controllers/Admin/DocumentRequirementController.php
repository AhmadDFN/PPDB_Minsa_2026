<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\DocumentRequirement;
use App\Models\Program;
use App\Services\AdminConfigurationAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class DocumentRequirementController
{
    public function __construct(private readonly AdminConfigurationAuditService $audit) {}

    public function index(): View
    {
        return view('admin.document-requirements.index', ['requirements' => DocumentRequirement::query()->with(['admissionPeriod', 'program'])->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.document-requirements.create', ['requirement' => new DocumentRequirement(['allowed_mimes' => 'application/pdf,image/jpeg,image/png', 'max_size_kb' => 5120]), 'periods' => AdmissionPeriod::query()->latest('start_date')->get(), 'programs' => Program::query()->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->validateProgramScope($data);
        try {
            DB::transaction(function () use ($data, $request, &$requirement): void {
                $requirement = DocumentRequirement::query()->create($data);
                $this->audit->record('document_requirement', $requirement->id, 'created', $request->user(), null, $this->snapshot($requirement));
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Persyaratan dokumen gagal disimpan.');
        }

        return redirect()->route('admin.document-requirements.index')->with('success', 'Persyaratan dokumen berhasil dibuat.');
    }

    public function edit(DocumentRequirement $documentRequirement): View
    {
        return view('admin.document-requirements.edit', ['requirement' => $documentRequirement, 'periods' => AdmissionPeriod::query()->latest('start_date')->get(), 'programs' => Program::query()->orderBy('name')->get()]);
    }

    public function update(Request $request, DocumentRequirement $documentRequirement): RedirectResponse
    {
        $data = $this->validated($request);
        $before = $this->snapshot($documentRequirement);
        if ($documentRequirement->registrationDocuments()->exists() && ($data['admission_period_id'] != $documentRequirement->admission_period_id || ($data['program_id'] ?? null) != $documentRequirement->program_id || $data['code'] !== $documentRequirement->code)) {
            return back()->withInput()->with('error', 'Persyaratan yang sudah dipakai pendaftaran tidak dapat dipindahkan atau diganti kodenya.');
        }
        $this->validateProgramScope($data);
        try {
            DB::transaction(function () use ($documentRequirement, $data, $request, $before): void {
                $documentRequirement->update($data);
                $this->audit->record('document_requirement', $documentRequirement->id, 'updated', $request->user(), $before, $this->snapshot($documentRequirement->fresh()));
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Persyaratan dokumen gagal diperbarui.');
        }

        return redirect()->route('admin.document-requirements.index')->with('success', 'Persyaratan dokumen berhasil diperbarui.');
    }

    public function toggle(Request $request, DocumentRequirement $documentRequirement): RedirectResponse
    {
        $before = $this->snapshot($documentRequirement);
        try {
            DB::transaction(function () use ($documentRequirement, $request, $before): void {
                $documentRequirement->update(['is_required' => ! $documentRequirement->is_required]);
                $this->audit->record('document_requirement', $documentRequirement->id, 'required_status_changed', $request->user(), $before, $this->snapshot($documentRequirement->fresh()));
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Status persyaratan dokumen gagal diperbarui.');
        }

        return back()->with('success', 'Status wajib/opsional berhasil diperbarui.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate(['admission_period_id' => ['required', 'integer', 'exists:admission_periods,id'], 'program_id' => ['nullable', 'integer', 'exists:programs,id'], 'code' => ['required', 'string', 'max:50', 'alpha_dash'], 'name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'is_required' => ['nullable', 'boolean'], 'allowed_mimes' => ['required', 'string', 'max:255', 'regex:/^(application\/pdf|image\/jpeg|image\/jpg|image\/png)(,(application\/pdf|image\/jpeg|image\/jpg|image\/png))*$/'], 'max_size_kb' => ['required', 'integer', 'min:1', 'max:5120'], 'sort_order' => ['required', 'integer', 'min:0', 'max:65535']]);
        $data['is_required'] = $request->boolean('is_required');

        return $data;
    }

    private function validateProgramScope(array $data): void
    {
        if (($data['program_id'] ?? null) === null) {
            return;
        }

        $linked = AdmissionPeriodProgram::query()
            ->where('admission_period_id', $data['admission_period_id'])
            ->where('program_id', $data['program_id'])
            ->where('is_active', true)
            ->exists();

        if (! $linked) {
            throw ValidationException::withMessages(['program_id' => 'Program tidak terhubung dengan periode yang dipilih.']);
        }
    }

    private function snapshot(DocumentRequirement $requirement): array
    {
        return ['admission_period_id' => $requirement->admission_period_id, 'program_id' => $requirement->program_id, 'code' => $requirement->code, 'name' => $requirement->name, 'is_required' => $requirement->is_required, 'allowed_mimes' => $requirement->allowed_mimes, 'max_size_kb' => $requirement->max_size_kb, 'sort_order' => $requirement->sort_order];
    }
}
