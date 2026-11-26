<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ApplicantAddressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantAddress extends Model
{
    /** @use HasFactory<ApplicantAddressFactory> */
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'address_line',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'city',
        'province',
        'postal_code',
        'distance_to_school_km',
        'province_code',
        'city_code',
        'district_code',
        'village_code',
    ];

    protected function casts(): array
    {
        return [
            'distance_to_school_km' => 'decimal:2',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    /**
     * Get full formatted address string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [
            $this->address_line,
            ($this->rt ? 'RT '.$this->rt : null),
            ($this->rw ? 'RW '.$this->rw : null),
            'Kel. '.$this->kelurahan,
            'Kec. '.$this->kecamatan,
            $this->city,
            $this->province,
            $this->postal_code,
        ];

        return implode(', ', array_filter($parts));
    }
}
