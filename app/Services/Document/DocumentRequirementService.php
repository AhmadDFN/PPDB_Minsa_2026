<?php

declare(strict_types=1);

namespace App\Services\Document;

use App\Enums\AdmissionPeriodStatus;
use App\Models\AdmissionPeriod;
use App\Models\Applicant;
use App\Models\DocumentRequirement;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DocumentRequirementService
{
    public function activeRegistrationFor(Applicant $applicant): ?Registration
    {
        $period = AdmissionPeriod::query()
            ->where('status', AdmissionPeriodStatus::ACTIVE)
            ->where('is_active', true)
            ->first();

        if (! $period) {
            return null;
        }

        return $applicant->registrations()
            ->where('admission_period_id', $period->id)
            ->with('admissionPeriodProgram.program')
            ->first();
    }

    /**
     * @return Collection<int, DocumentRequirement>
     */
    public function forRegistration(Registration $registration): Collection
    {
        $registration->loadMissing('admissionPeriodProgram');
        $programId = $registration->admissionPeriodProgram?->program_id;

        return DocumentRequirement::query()
            ->where('admission_period_id', $registration->admission_period_id)
            ->where(function ($query) use ($programId): void {
                $query->whereNull('program_id');

                if ($programId !== null) {
                    $query->orWhere('program_id', $programId);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function findForRegistrationOrFail(int|string $requirementId, Registration $registration): DocumentRequirement
    {
        $requirement = $this->forRegistration($registration)
            ->firstWhere('id', (int) $requirementId);

        if (! $requirement) {
            throw (new ModelNotFoundException)->setModel(DocumentRequirement::class, [$requirementId]);
        }

        return $requirement;
    }
}
