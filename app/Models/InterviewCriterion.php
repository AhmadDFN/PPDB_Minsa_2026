<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewCriterion extends Model
{
    use HasFactory;

    protected $table = 'interview_criteria';

    protected $fillable = [
        'interview_stage_id',
        'title',
        'description',
        'min_score',
        'max_score',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_score' => 'integer',
            'max_score' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(InterviewStage::class, 'interview_stage_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(InterviewScore::class, 'interview_criterion_id');
    }
}
