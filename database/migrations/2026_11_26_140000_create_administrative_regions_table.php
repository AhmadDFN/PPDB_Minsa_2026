<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrative_regions', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10);
            $table->string('parent_code', 10)->nullable();
            $table->unsignedTinyInteger('level');
            $table->string('name', 100);
            $table->timestamps();
            $table->unique('code', 'ar_code_uq');
            $table->index(['level', 'parent_code'], 'ar_level_parent_ix');
            $table->foreign('parent_code', 'ar_parent_fk')->references('code')->on('administrative_regions')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrative_regions');
    }
};
