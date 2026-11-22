<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AdmissionPeriodStatus;
use App\Models\AdmissionPeriod;
use App\Services\AdminConfigurationAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class AdmissionPeriodController
{
    public function __construct(private readonly AdminConfigurationAuditService $audit) {}

    public function index(): View
    {
        return view('admin.admission-periods.index', ['periods' => AdmissionPeriod::query()->withCount('registrations')->latest('start_date')->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.admission-periods.create', ['period' => new AdmissionPeriod, 'statuses' => AdmissionPeriodStatus::cases()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        try {
            DB::transaction(function () use ($data, $request, &$period): void {
                $period = AdmissionPeriod::query()->create($data);
                $this->audit->record('admission_period', $period->id, 'created', $request->user(), null, $this->snapshot($period));
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Perubahan periode gagal disimpan.');
        }

        return redirect()->route('admin.admission-periods.index')->with('success', 'Periode pendaftaran berhasil dibuat.');
    }

    public function edit(AdmissionPeriod $admissionPeriod): View
    {
        return view('admin.admission-periods.edit', ['period' => $admissionPeriod, 'statuses' => AdmissionPeriodStatus::cases()]);
    }

    public function update(Request $request, AdmissionPeriod $admissionPeriod): RedirectResponse
    {
        $data = $this->validated($request);
        $before = $this->snapshot($admissionPeriod);
        if ($admissionPeriod->registrations()->exists() && ($this->calendarDate($data['start_date']) !== $admissionPeriod->start_date?->toDateString() || $this->calendarDate($data['end_date']) !== $admissionPeriod->end_date?->toDateString() || $data['academic_year'] !== $admissionPeriod->academic_year)) {
            return back()->withInput()->with('error', 'Periode yang sudah memiliki pendaftar tidak dapat mengubah tanggal atau tahun akademik.');
        }
        try {
            DB::transaction(function () use ($admissionPeriod, $data, $request, $before): void {
                $admissionPeriod->update($data);
                if ($admissionPeriod->is_active) {
                    AdmissionPeriod::query()->where('is_active', true)->where('id', '!=', $admissionPeriod->id)->lockForUpdate()->get();
                    AdmissionPeriod::query()->where('id', '!=', $admissionPeriod->id)->update(['is_active' => false]);
                }
                $this->audit->record('admission_period', $admissionPeriod->id, 'updated', $request->user(), $before, $this->snapshot($admissionPeriod->fresh()));
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Perubahan periode gagal disimpan.');
        }

        return redirect()->route('admin.admission-periods.index')->with('success', 'Periode pendaftaran berhasil diperbarui.');
    }

    public function toggle(Request $request, AdmissionPeriod $admissionPeriod): RedirectResponse
    {
        $before = $this->snapshot($admissionPeriod);
        try {
            DB::transaction(function () use ($admissionPeriod, $request, $before): void {
                AdmissionPeriod::query()->where('is_active', true)->lockForUpdate()->get();
                $period = AdmissionPeriod::query()->lockForUpdate()->findOrFail($admissionPeriod->id);
                if (! $period->is_active) {
                    AdmissionPeriod::query()->where('id', '!=', $period->id)->update(['is_active' => false]);
                    $period->forceFill(['is_active' => true, 'status' => AdmissionPeriodStatus::ACTIVE])->save();
                } else {
                    $period->forceFill(['is_active' => false, 'status' => AdmissionPeriodStatus::CLOSED])->save();
                }
                $this->audit->record('admission_period', $period->id, 'status_changed', $request->user(), $before, $this->snapshot($period->fresh()));
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Status periode gagal diperbarui.');
        }

        return back()->with('success', 'Status periode berhasil diperbarui.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:15'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'age_reference_date' => ['required', 'date'],
            'status' => ['required', 'in:draft,active,closed,archived'],
            'description' => ['nullable', 'string'],
        ]);
        $data['is_active'] = $data['status'] === AdmissionPeriodStatus::ACTIVE->value;

        return $data;
    }

    private function snapshot(AdmissionPeriod $period): array
    {
        return ['name' => $period->name, 'academic_year' => $period->academic_year, 'start_date' => $period->start_date?->toISOString(), 'end_date' => $period->end_date?->toISOString(), 'status' => $period->status?->value, 'is_active' => $period->is_active];
    }

    private function calendarDate(string $value): string
    {
        return Carbon::parse($value)->toDateString();
    }
}
