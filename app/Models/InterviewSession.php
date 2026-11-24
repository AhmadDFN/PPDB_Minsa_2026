<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'interview_stage_id',
        'interviewer_id',
        'status',
        'started_at',
        'completed_at',
        'total_score',
        'average_score',
        'notes',
        'last_edited_by',
        'last_edit_reason',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_score' => 'integer',
            'average_score' => 'decimal:2',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(InterviewStage::class, 'interview_stage_id');
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(InterviewScore::class);
    }
}
