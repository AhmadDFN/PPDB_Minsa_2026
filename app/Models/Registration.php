<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RegistrationStatus;
use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
    use HasFactory;

    protected $fillable = [
        'registration_number',
        'applicant_id',
        'admission_period_id',
        'admission_period_program_id',
        'status',
        'submitted_at',
        'data_revision',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'submitted_at' => 'datetime',
            'data_revision' => 'integer',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function admissionPeriod(): BelongsTo
    {
        return $this->belongsTo(AdmissionPeriod::class);
    }

    public function admissionPeriodProgram(): BelongsTo
    {
        return $this->belongsTo(AdmissionPeriodProgram::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RegistrationDocument::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(RegistrationLog::class);
    }

    public function pdfVersions(): HasMany
    {
        return $this->hasMany(RegistrationPdfVersion::class);
    }

    public function consent(): HasOne
    {
        return $this->hasOne(RegistrationConsent::class);
    }

    public function isDraft(): bool
    {
        return $this->status === RegistrationStatus::DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === RegistrationStatus::SUBMITTED;
    }

    /**
     * Scope only submitted registrations (counted in public stats).
     */
    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', RegistrationStatus::SUBMITTED->value);
    }

    /**
     * Scope draft registrations (never counted in public stats).
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', RegistrationStatus::DRAFT->value);
    }

    /**
     * Increment the data revision counter and save.
     */
    public function incrementRevision(): void
    {
        $this->increment('data_revision');
    }
}
