<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AdmissionPeriodProgramFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdmissionPeriodProgram extends Model
{
    /** @use HasFactory<AdmissionPeriodProgramFactory> */
    use HasFactory;

    protected $table = 'admission_period_programs';

    protected $fillable = [
        'admission_period_id',
        'program_id',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function admissionPeriod(): BelongsTo
    {
        return $this->belongsTo(AdmissionPeriod::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
