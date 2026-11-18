<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Enums\AdmissionPeriodStatus;
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
                $period = AdmissionPeriod::query()
                    ->where('is_active', true)
                    ->where('status', AdmissionPeriodStatus::ACTIVE)
                    ->lockForUpdate()
                    ->first();

                if ($period === null || ! $period->isOpen()) {
                    throw ValidationException::withMessages([
                        'registration' => 'Perubahan data hanya dapat dilakukan selama periode pendaftaran dibuka.',
                    ]);
                }

                $registration = Registration::query()
                    ->where('applicant_id', $applicant->id)
                    ->where('admission_period_id', $period->id)
                    ->lockForUpdate()
                    ->first();

                $before = $registration?->isSubmitted()
                    ? $this->snapshot($applicant, $registration)
                    : null;

                $result = $operation();

                if ($registration !== null && $before !== null) {
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
                            'action' => 'updated_after_submission',
                            'from_revision' => $fromRevision,
                            'to_revision' => $toRevision,
                            'changes' => ['fields' => $changedFields],
                            'ip_address' => $ipAddress,
                            'user_agent' => $userAgent,
                            'created_at' => now(),
                        ]);
                    }
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
