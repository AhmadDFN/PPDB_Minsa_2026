<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class CommunicationSetting extends Model
{
    protected $fillable = [
        'active_channel_id',
        'database_provider_mode_enabled',
        'otp_activation_template',
        'otp_password_reset_template',
        'temporary_password_template',
        'temporary_password_resend_template',
    ];

    protected $casts = ['database_provider_mode_enabled' => 'boolean'];

    public function activeChannel(): BelongsTo
    {
        return $this->belongsTo(CommunicationChannel::class, 'active_channel_id');
    }

    public static function singleton(): self
    {
        $now = now();
        DB::table('communication_settings')->insertOrIgnore([
            'id' => 1,
            'database_provider_mode_enabled' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return static::query()->findOrFail(1);
    }
}
