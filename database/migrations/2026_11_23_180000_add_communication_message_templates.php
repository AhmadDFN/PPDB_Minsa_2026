<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communication_settings', function (Blueprint $table): void {
            $table->text('otp_activation_template')->nullable();
            $table->text('otp_password_reset_template')->nullable();
            $table->text('temporary_password_template')->nullable();
            $table->text('temporary_password_resend_template')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('communication_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'otp_activation_template',
                'otp_password_reset_template',
                'temporary_password_template',
                'temporary_password_resend_template',
            ]);
        });
    }
};
