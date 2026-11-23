<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentVerificationStatus;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AdminReportService
{
    /** @param array<string, mixed> $filters */
    public function registrations(array $filters): Builder
    {
        return $this->applyFilters(
            Registration::query()
                ->submitted()
                ->select([
                    'registrations.id',
                    'registrations.registration_number',
                    'registrations.applicant_id',
                    'registrations.admission_period_id',
                    'registrations.admission_period_program_id',
                    'registrations.submitted_at',
                ])
                ->with([
                    'applicant:id,full_name',
                    'admissionPeriod:id,academic_year,name',
                    'admissionPeriodProgram.program:id,name',
                ])
                ->withCount([
                    'documents',
                    'documents as pending_documents_count' => fn (Builder $query) => $query->where('verification_status', DocumentVerificationStatus::PENDING->value),
                    'documents as approved_documents_count' => fn (Builder $query) => $query->where('verification_status', DocumentVerificationStatus::APPROVED->value),
                    'documents as revision_documents_count' => fn (Builder $query) => $query->where('verification_status', DocumentVerificationStatus::REVISION_REQUIRED->value),
                    'documents as rejected_documents_count' => fn (Builder $query) => $query->where('verification_status', DocumentVerificationStatus::REJECTED->value),
                ]),
            $filters,
        );
    }

    /** @param array<string, mixed> $filters */
    public function summary(array $filters): array
    {
        $base = $this->applyFilters(Registration::query()->submitted(), $filters);
        $total = (clone $base)->count('registrations.id');

        $byProgram = (clone $base)
            ->join('admission_period_programs', 'registrations.admission_period_program_id', '=', 'admission_period_programs.id')
            ->join('programs', 'admission_period_programs.program_id', '=', 'programs.id')
            ->select('programs.name', DB::raw('COUNT(registrations.id) AS total'))
            ->groupBy('programs.id', 'programs.name')
            ->orderBy('programs.name')
            ->get()
            ->map(fn ($row): array => ['label' => $row->name, 'total' => (int) $row->total])
            ->all();

        $byPeriod = (clone $base)
            ->join('admission_periods', 'registrations.admission_period_id', '=', 'admission_periods.id')
            ->select('admission_periods.name', 'admission_periods.academic_year', DB::raw('COUNT(registrations.id) AS total'))
            ->groupBy('admission_periods.id', 'admission_periods.name', 'admission_periods.academic_year')
            ->orderByDesc('admission_periods.academic_year')
            ->get()
            ->map(fn ($row): array => ['label' => $row->academic_year.' — '.$row->name, 'total' => (int) $row->total])
            ->all();

        $registrationIds = (clone $base)->select('registrations.id');
        $verification = [];
        foreach (DocumentVerificationStatus::cases() as $status) {
            $documents = RegistrationDocument::query()
                ->where('verification_status', $status->value)
                ->whereIn('registration_id', $registrationIds);
            $verification[$status->value] = $documents->count();
        }

        return [
            'total_submitted' => $total,
            'by_program' => $byProgram,
            'by_period' => $byPeriod,
            'verification' => $verification,
        ];
    }

    /** @param array<string, mixed> $filters */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['period_id'] ?? null, fn (Builder $builder, $periodId) => $builder->where('registrations.admission_period_id', $periodId))
            ->when($filters['program_id'] ?? null, fn (Builder $builder, $programId) => $builder->whereHas('admissionPeriodProgram', fn (Builder $pivot) => $pivot->where('program_id', $programId)))
            ->when($filters['date_from'] ?? null, fn (Builder $builder, $date) => $builder->where('registrations.submitted_at', '>=', CarbonImmutable::parse($date, 'Asia/Jakarta')->startOfDay()))
            ->when($filters['date_to'] ?? null, fn (Builder $builder, $date) => $builder->where('registrations.submitted_at', '<=', CarbonImmutable::parse($date, 'Asia/Jakarta')->endOfDay()))
            ->when($filters['verification_status'] ?? null, fn (Builder $builder, $status) => $builder->whereHas('documents', fn (Builder $documents) => $documents->where('verification_status', $status)));
    }
}
