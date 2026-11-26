<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use SplFileObject;

class AdministrativeRegionSeeder extends Seeder
{
    public function run(): void
    {
        $directory = database_path('data/indonesia-regions');
        $sources = [
            ['file' => 'provinces.csv', 'level' => 1, 'parent_column' => null],
            ['file' => 'regencies.csv', 'level' => 2, 'parent_column' => 'province_id'],
            ['file' => 'districts.csv', 'level' => 3, 'parent_column' => 'regency_id'],
            ['file' => 'villages.csv', 'level' => 4, 'parent_column' => 'district_id'],
        ];

        foreach ($sources as $source) {
            $path = $directory.'/'.$source['file'];
            if (! is_file($path)) {
                throw new RuntimeException('Sumber data wilayah tidak ditemukan: '.$source['file']);
            }

            $csv = new SplFileObject($path, 'r');
            $csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
            $header = null;
            $batch = [];

            foreach ($csv as $row) {
                if (! is_array($row) || $row === [null]) {
                    continue;
                }
                if ($header === null) {
                    $header = array_map(static fn (string $field): string => preg_replace('/^\xEF\xBB\xBF/', '', trim($field)), $row);

                    continue;
                }

                $values = array_combine($header, array_map(static fn (mixed $value): string => trim((string) $value), $row));
                if (! is_array($values) || empty($values['id']) || empty($values['name'])) {
                    continue;
                }

                $batch[] = [
                    'code' => $values['id'],
                    'parent_code' => $source['parent_column'] === null ? null : ($values[$source['parent_column']] ?? null),
                    'level' => $source['level'],
                    'name' => $values['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) === 1000) {
                    $this->upsert($batch);
                    $batch = [];
                }
            }

            if ($batch !== []) {
                $this->upsert($batch);
            }
        }
    }

    /** @param list<array<string, mixed>> $rows */
    private function upsert(array $rows): void
    {
        DB::table('administrative_regions')->upsert(
            $rows,
            ['code'],
            ['parent_code', 'level', 'name', 'updated_at'],
        );
    }
}
