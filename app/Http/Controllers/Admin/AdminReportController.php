<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AdminReportFilterRequest;
use App\Models\AdmissionPeriod;
use App\Models\Program;
use App\Services\AdminConfigurationAuditService;
use App\Services\AdminReportService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AdminReportController
{
    private const EXPORT_MAX_ATTEMPTS = 10;

    private const EXPORT_DECAY_SECONDS = 600;

    public function __construct(
        private readonly AdminReportService $reports,
        private readonly AdminConfigurationAuditService $audit,
    ) {}

    public function index(AdminReportFilterRequest $request): View
    {
        $filters = $this->filtersFor($request);
        $error = null;
        $summary = ['total_submitted' => 0, 'by_program' => [], 'by_period' => [], 'verification' => []];

        try {
            $summary = $this->reports->summary($filters);
        } catch (Throwable $exception) {
            report($exception);
            $error = 'Laporan belum dapat dimuat. Silakan coba lagi atau hubungi administrator sistem.';
        }

        return view('admin.reports.index', [
            'summary' => $summary,
            'filters' => $filters,
            'periods' => AdmissionPeriod::query()->orderByDesc('is_active')->orderByDesc('start_date')->get(['id', 'name', 'academic_year', 'is_active']),
            'programs' => Program::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'error' => $error,
        ]);
    }

    public function export(AdminReportFilterRequest $request): StreamedResponse|Response
    {
        $user = $request->user();
        $rateKey = 'admin-report-export:user:'.$user->id;
        if (RateLimiter::tooManyAttempts($rateKey, self::EXPORT_MAX_ATTEMPTS)) {
            $retryAfter = RateLimiter::availableIn($rateKey);

            return response('Batas ekspor tercapai. Silakan coba lagi dalam '.$retryAfter.' detik.', 429, ['Retry-After' => (string) $retryAfter]);
        }

        $filters = $this->filtersFor($request);
        try {
            $query = $this->reports->registrations($filters);
            $rowCount = (clone $query)->count('registrations.id');
        } catch (Throwable $exception) {
            report($exception);

            return response('Laporan tidak dapat diproses saat ini.', 500);
        }

        try {
            $this->audit->record('admin_report', $user->id, 'admin_report_export_requested', $user, null, [
                'format' => 'csv',
                'filters' => $filters,
                'row_count' => $rowCount,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response('Ekspor tidak dapat diproses karena pencatatan audit gagal.', 500);
        }

        RateLimiter::hit($rateKey, self::EXPORT_DECAY_SECONDS);
        $filename = 'ppdb-laporan-'.CarbonImmutable::now('Asia/Jakarta')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $this->headers());

            foreach ($query->cursor() as $registration) {
                $submittedAt = $registration->submitted_at?->copy()->setTimezone('Asia/Jakarta')->format('d-m-Y H:i:s').' WIB';
                fputcsv($output, [
                    $this->csvText($registration->registration_number),
                    $this->csvText($registration->applicant?->full_name),
                    $this->csvText($registration->admissionPeriod?->academic_year),
                    $this->csvText($registration->admissionPeriod?->name),
                    $this->csvText($registration->admissionPeriodProgram?->program?->name),
                    $this->csvText($submittedAt),
                    $registration->documents_count,
                    $registration->pending_documents_count,
                    $registration->approved_documents_count,
                    $registration->revision_documents_count,
                    $registration->rejected_documents_count,
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store, private', 'X-Content-Type-Options' => 'nosniff']);
    }

    /** @param array<string, mixed> $filters @return array<string, mixed> */
    private function normalizedFilters(array $filters): array
    {
        return [
            'period_id' => $filters['period_id'] ?? null,
            'program_id' => $filters['program_id'] ?? null,
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'verification_status' => $filters['verification_status'] ?? null,
        ];
    }

    /** @return array<string, mixed> */
    private function filtersFor(AdminReportFilterRequest $request): array
    {
        $filters = $this->normalizedFilters($request->validated());
        $filterKeys = ['period_id', 'program_id', 'date_from', 'date_to', 'verification_status'];
        if (count(array_intersect(array_keys($request->query()), $filterKeys)) === 0) {
            $filters['period_id'] = AdmissionPeriod::query()->active()->value('id');
        }

        return $filters;
    }

    /** @return array<int, string> */
    private function headers(): array
    {
        return ['Nomor Registrasi', 'Nama Calon Peserta Didik', 'Tahun Ajaran', 'Periode Pendaftaran', 'Program', 'Waktu Submit', 'Total Dokumen', 'Menunggu Verifikasi', 'Disetujui', 'Perlu Perbaikan', 'Ditolak'];
    }

    private function csvText(?string $value): string
    {
        $value ??= '';
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
