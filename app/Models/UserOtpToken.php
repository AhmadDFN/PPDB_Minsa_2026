<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OtpPurpose;
use App\Services\Authentication\OtpService;
use Carbon\Carbon;
use Database\Factories\UserOtpTokenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class UserOtpToken extends Model
{
    /** @use HasFactory<UserOtpTokenFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'purpose',
        'otp_hash',
        'attempts',
        'max_attempts',
        'expires_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'purpose' => OtpPurpose::class,
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    public function isExhausted(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }

    public function isValid(): bool
    {
        return ! $this->isVerified() && ! $this->isExpired() && ! $this->isExhausted();
    }

    /**
     * Verify plain OTP using HMAC-SHA256 with user ID, purpose, and phone binding.
     */
    public function verify(string $plainOtp): bool
    {
        if (! $this->isValid()) {
            return false;
        }

        $this->increment('attempts');

        $purposeValue = $this->purpose instanceof OtpPurpose ? $this->purpose->value : (string) $this->purpose;
        $expectedHash = OtpService::computeHash($this->user_id, $purposeValue, $plainOtp, $this->phone);

        if (hash_equals($this->otp_hash, $expectedHash)) {
            $this->update(['verified_at' => Carbon::now()]);

            return true;
        }

        // Backward compatibility fallback if legacy bcrypt hash is present
        if (str_starts_with($this->otp_hash, '$2y$') && Hash::check($plainOtp, $this->otp_hash)) {
            $this->update(['verified_at' => Carbon::now()]);

            return true;
        }

        return false;
    }
}
