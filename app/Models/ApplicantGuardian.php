<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GuardianRelationship;
use Database\Factories\ApplicantGuardianFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class ApplicantGuardian extends Model
{
    /** @use HasFactory<ApplicantGuardianFactory> */
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'relationship_type',
        'name',
        'nik',
        'birth_place',
        'birth_date',
        'education',
        'occupation',
        'monthly_income',
        'phone',
        'is_alive',
        'nik_encrypted',
        'nik_lookup_hash',
        'status_label',
        'residence',
        'address_line',
        'rt',
        'rw',
        'province',
        'province_code',
        'city',
        'city_code',
        'kecamatan',
        'district_code',
        'kelurahan',
        'village_code',
        'postal_code',
        'residence_status',
    ];

    protected function casts(): array
    {
        return [
            'relationship_type' => GuardianRelationship::class,
            'birth_date' => 'date',
            'is_alive' => 'boolean',
        ];
    }

    protected function nik(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): ?string {
                if ($this->nik_encrypted) {
                    try {
                        return Crypt::decryptString($this->nik_encrypted);
                    } catch (\Throwable) {
                        return null;
                    }
                }

                return $value === null ? null : (string) $value;
            },
        );
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function isFather(): bool
    {
        return $this->relationship_type === GuardianRelationship::FATHER;
    }

    public function isMother(): bool
    {
        return $this->relationship_type === GuardianRelationship::MOTHER;
    }

    public function isGuardian(): bool
    {
        return $this->relationship_type === GuardianRelationship::GUARDIAN;
    }
}
