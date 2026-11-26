<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AdmissionPeriodStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\DocumentRequirement;
use App\Models\Program;
use Illuminate\Database\Seeder;

class AdmissionPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $period = AdmissionPeriod::firstOrCreate(
            ['academic_year' => '2026/2027'],
            [
                'name' => 'PPDB MIN 1 Kota Madiun TP 2026/2027',
                'start_date' => now()->subDay(),
                'end_date' => now()->addDays(30),
                'age_reference_date' => '2026-07-01',
                'status' => AdmissionPeriodStatus::ACTIVE,
                'is_active' => true,
                'description' => 'Penerimaan Peserta Didik Baru MIN 1 Kota Madiun Tahun Pelajaran 2026/2027',
            ]
        );

        $programs = Program::all();
        foreach ($programs as $program) {
            AdmissionPeriodProgram::firstOrCreate([
                'admission_period_id' => $period->id,
                'program_id' => $program->id,
            ], [
                'is_active' => true,
            ]);
        }

        DocumentRequirement::firstOrCreate([
            'admission_period_id' => $period->id,
            'code' => 'KK',
        ], [
            'name' => 'Kartu Keluarga',
            'is_required' => true,
            'allowed_mimes' => 'application/pdf,image/jpeg,image/png',
            'max_size_kb' => 5120,
            'sort_order' => 1,
        ]);

        DocumentRequirement::firstOrCreate([
            'admission_period_id' => $period->id,
            'code' => 'AKTA',
        ], [
            'name' => 'Akta Kelahiran',
            'is_required' => true,
            'allowed_mimes' => 'application/pdf,image/jpeg,image/png',
            'max_size_kb' => 5120,
            'sort_order' => 2,
        ]);

        DocumentRequirement::firstOrCreate([
            'admission_period_id' => $period->id,
            'code' => 'FOTO',
        ], [
            'name' => 'Pas Foto Calon Siswa (3x4)',
            'is_required' => true,
            'allowed_mimes' => 'image/jpeg,image/png',
            'max_size_kb' => 5120,
            'sort_order' => 3,
        ]);

        foreach ([
            ['code' => 'SURAT_AKTIF_TK', 'name' => 'Surat Keterangan Aktif TK/RA', 'is_required' => true, 'sort_order' => 4],
            ['code' => 'NISN', 'name' => 'Bukti NISN dari TK/RA', 'is_required' => true, 'sort_order' => 5],
            ['code' => 'PIAGAM', 'name' => 'Sertifikat / Piagam Kejuaraan (opsional)', 'is_required' => false, 'sort_order' => 6],
        ] as $documentRequirement) {
            DocumentRequirement::firstOrCreate(
                ['admission_period_id' => $period->id, 'code' => $documentRequirement['code']],
                [...$documentRequirement, 'allowed_mimes' => 'application/pdf,image/jpeg,image/png', 'max_size_kb' => 5120],
            );
        }
    }
}
