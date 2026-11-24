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
            if (! Schema::hasColumn('communication_settings', 'document_approved_schedule_template')) {
                $table->text('document_approved_schedule_template')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('communication_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('communication_settings', 'document_approved_schedule_template')) {
                $table->dropColumn('document_approved_schedule_template');
            }
        });
    }
};
