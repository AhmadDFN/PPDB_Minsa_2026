<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdmissionPeriod;
use App\Models\InterviewBatch;
use App\Models\InterviewCriterion;
use App\Models\InterviewStage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InterviewMasterSeeder extends Seeder
{
    /**
     * Seed default interview stages, evaluation criteria, and batches for PPDB v2.
     */
    public function run(): void
    {
        $period = AdmissionPeriod::where('is_active', true)->first()
            ?? AdmissionPeriod::first();

        if (! $period) {
            return;
        }

        // 1. Seed Tahap Wawancara & Poin Penilaian
        $stages = [
            [
                'stage_order' => 1,
                'code' => 'STAGE_1',
                'title' => "Baca Tulis Al-Qur'an / Iqro'",
                'description' => "Penilaian kemampuan membaca, makhraj, dan kelancaran membaca Al-Qur'an atau Iqro'.",
                'program_scope' => 'ALL',
                'required_role' => 'interviewer_stage_1',
                'is_confidential' => false,
                'criteria' => [
                    ['title' => "Kelancaran Membaca Al-Qur'an / Iqro'", 'description' => 'Tingkat kelancaran dan pengenalan huruf hijaiyah.', 'sort_order' => 1],
                    ['title' => 'Makharijul Huruf & Ketepatan Bacaan', 'description' => 'Ketepatan pelafalan makhraj huruf dan harakat dasar.', 'sort_order' => 2],
                ],
            ],
            [
                'stage_order' => 2,
                'code' => 'STAGE_2',
                'title' => 'Calistung Sederhana',
                'description' => 'Penilaian kemampuan dasar membaca, menulis kata sederhana, berhitung dasar, serta konsentrasi anak.',
                'program_scope' => 'ALL',
                'required_role' => 'interviewer_stage_2',
                'is_confidential' => false,
                'criteria' => [
                    ['title' => 'Kemampuan Membaca Sederhana', 'description' => 'Mengenal huruf dan membaca kata/kalimat pendek sederhana.', 'sort_order' => 1],
                    ['title' => 'Kemampuan Menulis & Motorik Halus', 'description' => 'Cara memegang alat tulis dan menyalin huruf/angka.', 'sort_order' => 2],
                    ['title' => 'Kemampuan Berhitung Dasar', 'description' => 'Mengenal konsep bilangan dasar dan penjumlahan benda nyata.', 'sort_order' => 3],
                    ['title' => 'Pemahaman Instruksi & Fokus', 'description' => 'Konsentrasi, kesiapan mendengarkan instruksi, dan kemandirian.', 'sort_order' => 4],
                ],
            ],
            [
                'stage_order' => 3,
                'code' => 'STAGE_3',
                'title' => 'Wawancara Khusus Program Unggulan',
                'description' => 'Penilaian dan pemetaan potensi khusus calon peserta didik Program Unggulan (Tahfidz, Sains, Bahasa).',
                'program_scope' => 'UNGGULAN',
                'required_role' => 'interviewer_stage_3',
                'is_confidential' => true,
                'criteria' => [
                    ['title' => "Komitmen & Potensi Tahfidz Al-Qur'an", 'description' => 'Daya ingat hafalan surat pendek dan motivasi menghafal Al-Qur\'an.', 'sort_order' => 1],
                    ['title' => 'Ketertarikan Sains & Bahasa Asing', 'description' => 'Rasa ingin tahu terhadap sains alam dan kemampuan menirukan kosakata asing.', 'sort_order' => 2],
                    ['title' => 'Kemandirian & Kesiapan Mental Belajar', 'description' => 'Kesiapan mengikuti jam belajar intensif dan kedisiplinan madrasah.', 'sort_order' => 3],
                ],
            ],
        ];

        foreach ($stages as $stageData) {
            $criteriaList = $stageData['criteria'];
            unset($stageData['criteria']);

            $stage = InterviewStage::updateOrCreate(
                [
                    'admission_period_id' => $period->id,
                    'stage_order' => $stageData['stage_order'],
                ],
                $stageData
            );

            foreach ($criteriaList as $critData) {
                InterviewCriterion::firstOrCreate(
                    [
                        'interview_stage_id' => $stage->id,
                        'sort_order' => $critData['sort_order'],
                    ],
                    [
                        'title' => $critData['title'],
                        'description' => $critData['description'],
                        'min_score' => 0,
                        'max_score' => 100,
                        'is_active' => true,
                    ]
                );
            }
        }

        // 2. Seed Default Batch Jadwal Wawancara (70 nomor per hari/batch)
        $startDate = $period->start_date ? Carbon::parse($period->start_date)->addDays(7) : Carbon::today()->addDays(7);

        $defaultBatches = [
            ['batch_number' => 1, 'title' => 'Hari Ke-1 — Sesi Wawancara Pagi', 'days_offset' => 0, 'quota' => 70, 'start' => 1, 'end' => 70],
            ['batch_number' => 2, 'title' => 'Hari Ke-2 — Sesi Wawancara Pagi', 'days_offset' => 1, 'quota' => 70, 'start' => 71, 'end' => 140],
            ['batch_number' => 3, 'title' => 'Hari Ke-3 — Sesi Wawancara Pagi', 'days_offset' => 2, 'quota' => 70, 'start' => 141, 'end' => 210],
            ['batch_number' => 4, 'title' => 'Hari Ke-4 — Sesi Wawancara Pagi', 'days_offset' => 3, 'quota' => 70, 'start' => 211, 'end' => 280],
            ['batch_number' => 5, 'title' => 'Hari Ke-5 — Sesi Wawancara Pagi', 'days_offset' => 4, 'quota' => 70, 'start' => 281, 'end' => 350],
        ];

        foreach ($defaultBatches as $batchData) {
            InterviewBatch::updateOrCreate(
                [
                    'admission_period_id' => $period->id,
                    'batch_number' => $batchData['batch_number'],
                ],
                [
                    'title' => $batchData['title'],
                    'interview_date' => $startDate->copy()->addDays($batchData['days_offset'])->toDateString(),
                    'start_time' => '07:30:00',
                    'end_time' => '14:00:00',
                    'quota' => $batchData['quota'],
                    'sequence_start' => $batchData['start'],
                    'sequence_end' => $batchData['end'],
                    'is_active' => true,
                ]
            );
        }
    }
}
