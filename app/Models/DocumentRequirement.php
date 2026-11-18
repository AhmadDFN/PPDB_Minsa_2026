<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DocumentRequirementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class DocumentRequirement extends Model
{
    /** @use HasFactory<DocumentRequirementFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $requirement): void {
            $maxSizeKb = $requirement->max_size_kb;
            $globalMaxSizeKb = min(5120, (int) config('ppdb.documents.global_max_size_kb', 5120));

            if ($maxSizeKb !== null && ((int) $maxSizeKb < 1 || (int) $maxSizeKb > $globalMaxSizeKb)) {
                throw ValidationException::withMessages([
                    'max_size_kb' => "Batas ukuran dokumen harus antara 1 KB dan {$globalMaxSizeKb} KB.",
                ]);
            }
        });
    }

    protected $fillable = [
        'admission_period_id',
        'program_id',
        'code',
        'name',
        'description',
        'is_required',
        'allowed_mimes',
        'max_size_kb',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'max_size_kb' => 'integer',
            'sort_order' => 'integer',
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

    public function registrationDocuments(): HasMany
    {
        return $this->hasMany(RegistrationDocument::class);
    }

    /**
     * Get allowed MIME types as an array.
     *
     * @return list<string>
     */
    public function getAllowedMimesArray(): array
    {
        return array_map('trim', explode(',', $this->allowed_mimes));
    }
}
