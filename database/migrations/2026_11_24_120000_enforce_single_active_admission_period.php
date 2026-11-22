<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $activeCount = DB::table('admission_periods')->where('is_active', true)->count();
        if ($activeCount > 1) {
            throw new RuntimeException("Cannot enforce single active admission period: {$activeCount} active records exist.");
        }

        Schema::table('admission_periods', function (Blueprint $table): void {
            $table->string('active_slot', 10)
                ->nullable()
                ->storedAs("CASE WHEN is_active = 1 THEN 'ACTIVE' ELSE NULL END");
        });

        Schema::table('admission_periods', function (Blueprint $table): void {
            $table->unique('active_slot', 'admission_periods_active_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::table('admission_periods', function (Blueprint $table): void {
            $table->dropUnique('admission_periods_active_slot_unique');
            $table->dropColumn('active_slot');
        });
    }
};
