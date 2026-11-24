<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_period_id',
        'batch_number',
        'title',
        'interview_date',
        'start_time',
        'end_time',
        'quota',
        'sequence_start',
        'sequence_end',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'interview_date' => 'date',
            'quota' => 'integer',
            'sequence_start' => 'integer',
            'sequence_end' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function admissionPeriod(): BelongsTo
    {
        return $this->belongsTo(AdmissionPeriod::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
