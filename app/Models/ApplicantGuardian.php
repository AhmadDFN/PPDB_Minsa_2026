<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GuardianRelationship;
use Database\Factories\ApplicantGuardianFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected function casts(): array
    {
        return [
            'relationship_type' => GuardianRelationship::class,
            'birth_date' => 'date',
            'is_alive' => 'boolean',
        ];
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
