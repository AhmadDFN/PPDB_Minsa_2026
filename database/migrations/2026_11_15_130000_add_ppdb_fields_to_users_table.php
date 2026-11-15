<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Make email nullable for applicant accounts (they authenticate via NIK)
            $table->string('email')->nullable()->change();

            // Privacy-preserving NIK storage
            // Deterministic HMAC-SHA256 hash for fast unique lookups without exposing plaintext NIK
            $table->string('nik_lookup_hash', 64)->nullable()->unique()->after('id');
            // Encrypted NIK via Laravel Crypt
            $table->text('nik_encrypted')->nullable()->after('nik_lookup_hash');

            // Phone is shared across sibling accounts, NOT unique
            $table->string('phone', 25)->nullable()->after('email');
            $table->string('account_type', 20)->default('applicant')->after('phone');
            $table->string('account_status', 30)->default('pending_activation')->after('account_type');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');

            // Index for account filtering
            $table->index(['account_type', 'account_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['account_type', 'account_status']);
            $table->dropColumn([
                'nik_lookup_hash',
                'nik_encrypted',
                'phone',
                'account_type',
                'account_status',
                'phone_verified_at',
            ]);
            $table->string('email')->nullable(false)->change();
        });
    }
};
