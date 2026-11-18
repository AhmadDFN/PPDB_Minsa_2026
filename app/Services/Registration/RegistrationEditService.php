<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Enums\AdmissionPeriodStatus;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\Applicant;
use App\Models\Registration;
use App\Models\User;
use BackedEnum;
use Closure;
use DateTimeInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class RegistrationEditService
{
    public function registrationForApplicant(Applicant $applicant): ?Registration
    {
        $reopenedRegistration = Registration::query()
            ->where('applicant_id', $applicant->id)
            ->where('status', RegistrationStatus::SUBMITTED)
            ->whereHas('reopenWindows', fn ($query) => $query
                ->whereNull('closed_at')
                ->where('expires_at', '>', now()))
            ->with(['admissionPeriod', 'admissionPeriodProgram.program'])
            ->latest('id')
            ->first();

        if ($reopenedRegistration !== null) {
            return $reopenedRegistration;
        }

        $activePeriod = AdmissionPeriod::query()
            ->where('is_active', true)
            ->where('status', AdmissionPeriodStatus::ACTIVE)
            ->orderByDesc('id')
            ->first();

        if ($activePeriod?->isOpen()) {
            return $applicant->registrations()
                ->where('admission_period_id', $activePeriod->id)
                ->with(['admissionPeriod', 'admissionPeriodProgram.program'])
                ->first();
        }

        if ($activePeriod !== null) {
            return $applicant->registrations()
                ->where('admission_period_id', $activePeriod->id)
                ->with(['admissionPeriod', 'admissionPeriodProgram.program'])
                ->first();
        }

        return $applicant->registrations()
            ->with(['admissionPeriod', 'admissionPeriodProgram.program'])
            ->latest('id')
            ->first();
    }

    public function canEdit(?Registration $registration): bool
    {
        if ($registration === null) {
            return AdmissionPeriod::query()
                ->where('is_active', true)
                ->where('status', AdmissionPeriodStatus::ACTIVE)
                ->get()
                ->contains(fn (AdmissionPeriod $period): bool => $period->isOpen());
        }

        $registration->loadMissing('admissionPeriod');

        if ($registration->admissionPeriod?->isOpen()) {
            return true;
        }

        return $registration->isSubmitted()
            && $registration->reopenWindows()
                ->whereNull('closed_at')
                ->where('expires_at', '>', now())
                ->exists();
    }

    public function run(
        Applicant $applicant,
        User $user,
        ?string $ipAddress,
        ?string $userAgent,
        Closure $operation,
        ?Closure $cleanup = null
    ): mixed {
        if ($applicant->user_id !== $user->id) {
            throw (new ModelNotFoundException)->setModel(Applicant::class, [$applicant->id]);
        }

        $result = null;

        try {
            return DB::transaction(function () use (
                $applicant,
                $user,
                $ipAddress,
                $userAgent,
                $operation,
                &$result
            ): mixed {
                $candidate = $this->registrationForApplicant($applicant);

                if ($candidate === null) {
                    $period = AdmissionPeriod::query()
                        ->where('is_active', true)
                        ->where('status', AdmissionPeriodStatus::ACTIVE)
                        ->lockForUpdate()
                        ->first();

                    if ($period?->isOpen()) {
                        $result = $operation();

                        return $result;
                    }

                    throw ValidationException::withMessages([
                        'registration' => 'Perubahan data hanya dapat dilakukan selama periode pendaftaran dibuka.',
                    ]);
                }

                $period = AdmissionPeriod::query()
                    ->whereKey($candidate->admission_period_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $registration = Registration::query()
                    ->whereKey($candidate->id)
                    ->where('applicant_id', $applicant->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $reopenWindow = null;
                if (! $period->isOpen() && $registration->isSubmitted()) {
                    $reopenWindow = $registration->reopenWindows()
                        ->whereNull('closed_at')
                        ->where('expires_at', '>', now())
                        ->lockForUpdate()
                        ->first();
                }

                if (! $period->isOpen() && $reopenWindow === null) {
                    throw ValidationException::withMessages([
                        'registration' => 'Perubahan data hanya dapat dilakukan selama periode terbuka atau akses edit ulang masih berlaku.',
                    ]);
                }

                $before = $this->snapshot($applicant, $registration);

                $result = $operation();

                $registration->refresh();
                $changedFields = $this->changedFields(
                    $before,
                    $this->snapshot($applicant, $registration),
                );

                if ($changedFields !== []) {
                    $fromRevision = (int) $registration->data_revision;
                    $toRevision = $fromRevision + 1;
                    $registration->data_revision = $toRevision;
                    $registration->save();

                    $registration->logs()->create([
                        'user_id' => $user->id,
                        'action' => $registration->isSubmitted() ? 'updated_after_submission' : 'updated_draft',
                        'from_revision' => $fromRevision,
                        'to_revision' => $toRevision,
                        'changes' => ['fields' => $changedFields],
                        'ip_address' => $ipAddress,
                        'user_agent' => $userAgent,
                        'created_at' => now(),
                    ]);
                }

                return $result;
            });
        } catch (Throwable $exception) {
            if ($result !== null && $cleanup !== null) {
                try {
                    $cleanup($result);
                } catch (Throwable $cleanupFailure) {
                    report($cleanupFailure);

                    throw new RuntimeException(
                        'Perubahan data gagal dan berkas baru tidak dapat dibersihkan.',
                        previous: $exception,
                    );
                }
            }

            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Applicant $applicant, Registration $registration): array
    {
        $applicant = Applicant::query()
            ->with(['address', 'guardians', 'education'])
            ->findOrFail($applicant->id);

        $snapshot = [];
        foreach ([
            'full_name',
            'gender',
            'birth_place',
            'birth_date',
            'nisn',
            'child_order',
            'siblings_count',
        ] as $field) {
            $snapshot['biodata.'.$field] = $this->normalize($applicant->{$field});
        }

        foreach ([
            'address_line',
            'rt',
            'rw',
            'kelurahan',
            'kecamatan',
            'city',
            'province',
            'postal_code',
            'distance_to_school_km',
        ] as $field) {
            $snapshot['address.'.$field] = $this->normalize($applicant->address?->{$field});
        }

        foreach ($applicant->guardians as $guardian) {
            $relationship = $this->normalize($guardian->relationship_type);

            foreach ([
                'name',
                'nik',
                'birth_place',
                'birth_date',
                'education',
                'occupation',
                'monthly_income',
                'phone',
                'is_alive',
            ] as $field) {
                $snapshot['parents.'.$relationship.'.'.$field] = $this->normalize($guardian->{$field});
            }
        }

        foreach (['education_level', 'school_name', 'npsn', 'city'] as $field) {
            $snapshot['education.'.$field] = $this->normalize($applicant->education?->{$field});
        }

        $snapshot['program.admission_period_program_id'] = $registration->admission_period_program_id;
        $snapshot['documents'] = $registration->documents()
            ->orderBy('document_requirement_id')
            ->get(['document_requirement_id', 'file_hash'])
            ->map(fn ($document): array => [
                $document->document_requirement_id,
                $document->file_hash,
            ])
            ->all();

        return $snapshot;
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return list<string>
     */
    private function changedFields(array $before, array $after): array
    {
        $fields = array_unique([...array_keys($before), ...array_keys($after)]);
        $changed = [];

        foreach ($fields as $field) {
            if (($before[$field] ?? null) !== ($after[$field] ?? null)) {
                $changed[] = $field;
            }
        }

        return $changed;
    }

    private function normalize(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }
}
