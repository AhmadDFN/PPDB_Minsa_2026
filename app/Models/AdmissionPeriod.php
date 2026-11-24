<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AdmissionPeriodStatus;
use Carbon\Carbon;
use Database\Factories\AdmissionPeriodFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdmissionPeriod extends Model
{
    /** @use HasFactory<AdmissionPeriodFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'academic_year',
        'start_date',
        'end_date',
        'age_reference_date',
        'status',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'age_reference_date' => 'date',
            'status' => AdmissionPeriodStatus::class,
            'is_active' => 'boolean',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function periodPrograms(): HasMany
    {
        return $this->hasMany(AdmissionPeriodProgram::class);
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'admission_period_programs')
            ->withPivot(['id', 'is_active', 'notes'])
            ->withTimestamps();
    }

    public function documentRequirements(): HasMany
    {
        return $this->hasMany(DocumentRequirement::class);
    }

    public function interviewBatches(): HasMany
    {
        return $this->hasMany(InterviewBatch::class);
    }

    public function interviewStages(): HasMany
    {
        return $this->hasMany(InterviewStage::class)->orderBy('stage_order');
    }

    /**
     * Scope for currently active admission period.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('status', AdmissionPeriodStatus::ACTIVE->value);
    }

    /**
     * Check if registration is currently open in wall-clock time.
     */
    public function isOpen(): bool
    {
        if (! $this->is_active || $this->status !== AdmissionPeriodStatus::ACTIVE) {
            return false;
        }

        $now = Carbon::now();

        return $now->between($this->start_date, $this->end_date);
    }
}
