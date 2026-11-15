<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use App\Enums\GuardianRelationship;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Factories\ApplicantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Applicant extends Model
{
    /** @use HasFactory<ApplicantFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'gender',
        'birth_place',
        'birth_date',
        'nisn',
        'child_order',
        'siblings_count',
    ];

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'birth_date' => 'date',
            'child_order' => 'integer',
            'siblings_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(ApplicantAddress::class);
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(ApplicantGuardian::class);
    }

    public function father(): HasOne
    {
        return $this->hasOne(ApplicantGuardian::class)
            ->where('relationship_type', GuardianRelationship::FATHER->value);
    }

    public function mother(): HasOne
    {
        return $this->hasOne(ApplicantGuardian::class)
            ->where('relationship_type', GuardianRelationship::MOTHER->value);
    }

    public function guardian(): HasOne
    {
        return $this->hasOne(ApplicantGuardian::class)
            ->where('relationship_type', GuardianRelationship::GUARDIAN->value);
    }

    public function education(): HasOne
    {
        return $this->hasOne(ApplicantEducation::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Calculate age at a given reference date (e.g. from AdmissionPeriod).
     * Returns associative array: ['years', 'months', 'days', 'formatted'].
     * Age is NEVER persisted permanently to the database.
     *
     * @return array{years: int, months: int, days: int, formatted: string}
     */
    public function calculateAgeAt(CarbonInterface|string $referenceDate): array
    {
        if (! $this->birth_date) {
            return [
                'years' => 0,
                'months' => 0,
                'days' => 0,
                'formatted' => 'Belum ditentukan',
            ];
        }

        $ref = is_string($referenceDate) ? Carbon::parse($referenceDate) : $referenceDate;
        $birth = Carbon::parse($this->birth_date);

        if ($birth->isAfter($ref)) {
            return [
                'years' => 0,
                'months' => 0,
                'days' => 0,
                'formatted' => '0 tahun 0 bulan 0 hari',
            ];
        }

        $diff = $birth->diff($ref);

        return [
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
            'formatted' => sprintf('%d tahun %d bulan %d hari', $diff->y, $diff->m, $diff->d),
        ];
    }
}
