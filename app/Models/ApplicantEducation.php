<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PreviousEducationLevel;
use Database\Factories\ApplicantEducationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantEducation extends Model
{
    /** @use HasFactory<ApplicantEducationFactory> */
    use HasFactory;

    protected $table = 'applicant_educations';

    protected $fillable = [
        'applicant_id',
        'education_level',
        'school_name',
        'npsn',
        'city',
    ];

    protected function casts(): array
    {
        return [
            'education_level' => PreviousEducationLevel::class,
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }
}
