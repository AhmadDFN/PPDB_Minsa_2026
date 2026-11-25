<?php

declare(strict_types=1);

namespace App\Actions\Interview;

use App\Models\AdmissionPeriod;
use App\Models\InterviewStage;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportInterviewRecapCsvAction
{
    public function __construct(
        private readonly InterviewRecapQueryBuilder $queryBuilder
    ) {}

    /**
     * Stream a CSV export of interview recaps with dynamic stage columns and anti-formula sanitization.
     *
     * @param  array<string, mixed>  $filters
     */
    public function execute(AdmissionPeriod $period, array $filters): StreamedResponse
    {
        $stages = InterviewStage::query()
            ->where('admission_period_id', $period->id)
            ->with(['criteria' => function ($query): void {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->orderBy('stage_order')
            ->get();

        $query = $this->queryBuilder->build($period, $filters);
        $filename = 'rekap-nilai-wawancara-'.now()->setTimezone('Asia/Jakarta')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query, $stages): void {
            $output = fopen('php://output', 'w');
            // UTF-8 BOM for proper Excel encoding
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, $this->buildHeaders($stages));

            foreach ($query->cursor() as $registration) {
                $programCode = strtoupper((string) ($registration->admissionPeriodProgram?->program?->code ?? ''));
                $isUnggulan = str_contains($programCode, 'UNGGULAN');
                $sessionsByStage = $registration->interviewSessions->keyBy('interview_stage_id');

                $row = [
                    $this->csvText($registration->registration_number ?? '-'),
                    $this->csvText($registration->applicant?->full_name ?? '-'),
                    $this->csvText($registration->admissionPeriodProgram?->program?->name ?? '-'),
                    $this->csvText($registration->interviewBatch?->title ?? 'Belum Terjadwal'),
                ];

                foreach ($stages as $stage) {
                    $session = $sessionsByStage->get($stage->id);
                    $scoresByCriterion = $session ? $session->scores->keyBy('interview_criterion_id') : collect();
                    $notRequired = $stage->program_scope === 'UNGGULAN' && ! $isUnggulan;

                    foreach ($stage->criteria as $criterion) {
                        if ($notRequired) {
                            $row[] = 'N/A';
                        } elseif ($session && $session->status === 'COMPLETED') {
                            $scoreRecord = $scoresByCriterion->get($criterion->id);
                            $row[] = $scoreRecord !== null ? (string) $scoreRecord->score : '-';
                        } else {
                            $row[] = '-';
                        }
                    }

                    if ($notRequired) {
                        $row[] = 'N/A';
                        $row[] = 'N/A';
                        $row[] = 'Tidak Wajib (Reguler)';
                        $row[] = '-';
                    } elseif ($session && $session->status === 'COMPLETED') {
                        $row[] = (string) ($session->total_score ?? 0);
                        $row[] = $session->average_score !== null ? number_format((float) $session->average_score, 2, '.', '') : '0.00';
                        $row[] = 'Selesai';
                        $row[] = $this->csvText($session->interviewer?->name ?? '-');
                    } elseif ($session && $session->status === 'IN_PROGRESS') {
                        $row[] = '-';
                        $row[] = '-';
                        $row[] = 'Sedang Berlangsung';
                        $row[] = $this->csvText($session->interviewer?->name ?? '-');
                    } else {
                        $row[] = '-';
                        $row[] = '-';
                        $row[] = 'Belum Dinilai';
                        $row[] = '-';
                    }
                }

                // Sanitize every data cell, including placeholders such as "-".
                $row = array_map(
                    fn (mixed $value): string => $this->csvText((string) $value),
                    $row
                );

                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * @param  Collection<int, InterviewStage>  $stages
     * @return array<int, string>
     */
    private function buildHeaders(Collection $stages): array
    {
        $headers = [
            'Nomor Urut',
            'Nama Calon Siswa',
            'Program',
            'Jadwal / Batch',
        ];

        foreach ($stages as $stage) {
            $prefix = "T{$stage->stage_order}";
            foreach ($stage->criteria as $criterion) {
                $headers[] = $this->csvText("{$prefix} - {$criterion->title}");
            }
            $headers[] = "{$prefix} - Jumlah";
            $headers[] = "{$prefix} - Rata-rata";
            $headers[] = "{$prefix} - Status";
            $headers[] = "{$prefix} - Penguji";
        }

        return $headers;
    }

    /**
     * Sanitize cell text to prevent CSV formula injection (=, +, -, @, tab, CR).
     */
    private function csvText(?string $value): string
    {
        $value ??= '';
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
