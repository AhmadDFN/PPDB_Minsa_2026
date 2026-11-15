<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Seed the default PPDB programs: UNGGULAN and REGULER.
     */
    public function run(): void
    {
        $programs = [
            [
                'code' => 'UNGGULAN',
                'name' => 'Program Unggulan',
                'description' => 'Program intensif tahfidz Al-Qur\'an, pembiasaan bahasa asing, dan penguatan sains/teknologi madrasah.',
                'is_active' => true,
            ],
            [
                'code' => 'REGULER',
                'name' => 'Program Reguler',
                'description' => 'Program pendidikan dasar Madrasah Ibtidaiyah terintegrasi nilai-nilai keislaman dan kurikulum nasional.',
                'is_active' => true,
            ],
        ];

        foreach ($programs as $data) {
            Program::firstOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
