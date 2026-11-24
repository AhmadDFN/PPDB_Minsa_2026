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
        'queue_sequence',
        'applicant_id',
        'admission_period_id',
        'admission_period_program_id',
        'interview_batch_id',
        'status',
        'submitted_at',
        'verified_at',
        'verified_by',
        'data_revision',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'queue_sequence' => 'integer',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
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

    public function reopenWindows(): HasMany
    {
        return $this->hasMany(RegistrationReopenWindow::class);
    }

    public function activeReopenWindow(): HasOne
    {
        return $this->hasOne(RegistrationReopenWindow::class)
            ->whereNull('closed_at')
            ->where('expires_at', '>', now());
    }

    public function latestReopenWindow(): HasOne
    {
        return $this->hasOne(RegistrationReopenWindow::class)->latestOfMany();
    }

    public function interviewBatch(): BelongsTo
    {
        return $this->belongsTo(InterviewBatch::class, 'interview_batch_id');
    }

    public function verifiedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function interviewSessions(): HasMany
    {
        return $this->hasMany(InterviewSession::class);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
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
