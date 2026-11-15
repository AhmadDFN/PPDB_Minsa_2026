<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProgramFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    /** @use HasFactory<ProgramFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function periodPrograms(): HasMany
    {
        return $this->hasMany(AdmissionPeriodProgram::class);
    }

    public function admissionPeriods(): BelongsToMany
    {
        return $this->belongsToMany(AdmissionPeriod::class, 'admission_period_programs')
            ->withPivot(['id', 'is_active', 'notes'])
            ->withTimestamps();
    }

    public function documentRequirements(): HasMany
    {
        return $this->hasMany(DocumentRequirement::class);
    }
}
