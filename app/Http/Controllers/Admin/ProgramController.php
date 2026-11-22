<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\AdmissionPeriod;
use App\Models\Program;
use App\Services\AdminConfigurationAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class ProgramController
{
    public function __construct(private readonly AdminConfigurationAuditService $audit) {}

    public function index(): View
    {
        return view('admin.programs.index', ['programs' => Program::query()->withCount('periodPrograms')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.programs.create', ['program' => new Program, 'periods' => AdmissionPeriod::query()->latest('start_date')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $periodIds = $data['period_ids'] ?? [];
        unset($data['period_ids']);
        try {
            DB::transaction(function () use ($data, $periodIds, $request, &$program): void {
                $program = Program::query()->create($data);
                $program->periodPrograms()->createMany(array_map(fn (int $periodId): array => ['admission_period_id' => $periodId, 'is_active' => true], $periodIds));
                $this->audit->record('program', $program->id, 'created', $request->user(), null, $this->snapshot($program));
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Perubahan program gagal disimpan.');
        }

        return redirect()->route('admin.programs.index')->with('success', 'Program berhasil dibuat.');
    }

    public function edit(Program $program): View
    {
        return view('admin.programs.edit', ['program' => $program->load('periodPrograms'), 'periods' => AdmissionPeriod::query()->latest('start_date')->get()]);
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $data = $this->validated($request);
        $periodIds = $data['period_ids'] ?? [];
        unset($data['period_ids']);
        $before = $this->snapshot($program);
        try {
            DB::transaction(function () use ($program, $data, $periodIds, $request, $before): void {
                $program->update($data);
                $program->periodPrograms()->whereNotIn('admission_period_id', $periodIds)->update(['is_active' => false]);
                foreach ($periodIds as $periodId) {
                    $program->periodPrograms()->updateOrCreate(['admission_period_id' => $periodId], ['is_active' => true]);
                }
                $this->audit->record('program', $program->id, 'updated', $request->user(), $before, $this->snapshot($program->fresh()));
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Perubahan program gagal disimpan.');
        }

        return redirect()->route('admin.programs.index')->with('success', 'Program berhasil diperbarui.');
    }

    public function toggle(Request $request, Program $program): RedirectResponse
    {
        $before = $this->snapshot($program);
        try {
            DB::transaction(function () use ($program, $request, $before): void {
                $program->update(['is_active' => ! $program->is_active]);
                $this->audit->record('program', $program->id, 'status_changed', $request->user(), $before, $this->snapshot($program->fresh()));
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Status program gagal diperbarui.');
        }

        return back()->with('success', 'Status program berhasil diperbarui.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['code' => ['required', 'string', 'max:50', 'alpha_dash'], 'name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'is_active' => ['nullable', 'boolean'], 'period_ids' => ['nullable', 'array'], 'period_ids.*' => ['integer', 'exists:admission_periods,id']]);
    }

    private function snapshot(Program $program): array
    {
        return ['code' => $program->code, 'name' => $program->name, 'description' => $program->description, 'is_active' => $program->is_active];
    }
}
