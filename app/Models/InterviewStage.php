<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_period_id',
        'stage_order',
        'code',
        'title',
        'description',
        'program_scope',
        'required_role',
        'is_confidential',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stage_order' => 'integer',
            'is_confidential' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function admissionPeriod(): BelongsTo
    {
        return $this->belongsTo(AdmissionPeriod::class);
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(InterviewCriterion::class)->orderBy('sort_order');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(InterviewSession::class);
    }
}
