<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Support\NikManager;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'nik_lookup_hash',
        'nik_encrypted',
        'phone',
        'account_type',
        'account_status',
        'email_verified_at',
        'phone_verified_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Sensitive attributes like encrypted NIK and lookup hash are always hidden.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'nik_encrypted',
        'nik_lookup_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'account_type' => AccountType::class,
            'account_status' => AccountStatus::class,
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * One user has exactly one applicant profile (for applicant accounts).
     */
    public function applicant(): HasOne
    {
        return $this->hasOne(Applicant::class);
    }

    /**
     * User can have multiple OTP tokens for activation and recovery.
     */
    public function otpTokens(): HasMany
    {
        return $this->hasMany(UserOtpToken::class);
    }

    /**
     * Check if user is an applicant account.
     */
    public function isApplicant(): bool
    {
        return $this->account_type === AccountType::APPLICANT;
    }

    /**
     * Check if user is an administrator account.
     */
    public function isAdmin(): bool
    {
        return $this->account_type === AccountType::ADMIN;
    }

    /**
     * Get decrypted NIK (use with extreme care, never serialize publicly).
     */
    public function getDecryptedNik(): ?string
    {
        if (empty($this->nik_encrypted)) {
            return null;
        }

        return NikManager::decrypt($this->nik_encrypted);
    }

    /**
     * Get masked NIK for safe display (e.g. 3573********0001).
     */
    public function getMaskedNik(): string
    {
        $nik = $this->getDecryptedNik();

        return $nik ? NikManager::mask($nik) : 'Belum diisi';
    }
}
