<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table): void {
            $table->text('family_card_number_encrypted')->nullable();
            $table->string('nisn_unavailable_reason', 100)->nullable();
            $table->string('religion', 30)->nullable();
            $table->string('hobby', 50)->nullable();
            $table->string('hobby_other', 100)->nullable();
            $table->string('aspiration', 80)->nullable();
            $table->string('aspiration_other', 100)->nullable();
            $table->string('residence_status', 40)->nullable();
            $table->string('travel_time', 30)->nullable();
            $table->string('transportation', 40)->nullable();
            $table->text('social_assistance_number_encrypted')->nullable();
        });

        Schema::table('applicant_addresses', function (Blueprint $table): void {
            $table->string('province_code', 10)->nullable();
            $table->string('city_code', 15)->nullable();
            $table->string('district_code', 20)->nullable();
            $table->string('village_code', 25)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
        });

        Schema::table('applicant_guardians', function (Blueprint $table): void {
            $table->text('nik_encrypted')->nullable();
            $table->string('nik_lookup_hash', 64)->nullable();
            $table->string('status_label', 30)->nullable();
            $table->string('residence', 255)->nullable();
            $table->text('address_line')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('province_code', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('city_code', 15)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('district_code', 20)->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('village_code', 25)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('residence_status', 40)->nullable();
        });

        Schema::table('applicant_educations', function (Blueprint $table): void {
            $table->boolean('nisn_unavailable')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('applicant_educations', fn (Blueprint $table) => $table->dropColumn('nisn_unavailable'));
        Schema::table('applicant_guardians', function (Blueprint $table): void {
            $table->dropColumn([
                'nik_encrypted', 'nik_lookup_hash', 'status_label', 'residence', 'address_line', 'rt', 'rw',
                'province', 'province_code', 'city', 'city_code', 'kecamatan', 'district_code', 'kelurahan',
                'village_code', 'postal_code', 'residence_status',
            ]);
        });
        Schema::table('applicant_addresses', fn (Blueprint $table) => $table->dropColumn([
            'province_code', 'city_code', 'district_code', 'village_code', 'latitude', 'longitude',
        ]));
        Schema::table('applicants', fn (Blueprint $table) => $table->dropColumn([
            'family_card_number_encrypted', 'nisn_unavailable_reason', 'religion', 'hobby', 'hobby_other',
            'aspiration', 'aspiration_other', 'residence_status', 'travel_time', 'transportation', 'social_assistance_number_encrypted',
        ]));
    }
};
