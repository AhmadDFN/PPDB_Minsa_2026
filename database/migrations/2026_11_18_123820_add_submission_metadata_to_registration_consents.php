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
        Schema::table('registration_consents', function (Blueprint $table) {
            $table->string('consent_version', 50)->nullable()->after('statement_text');
            $table->string('accepted_by_name')->nullable()->after('consent_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_consents', function (Blueprint $table) {
            $table->dropColumn(['consent_version', 'accepted_by_name']);
        });
    }
};
