<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminActivityLogService
{
    private const PER_PAGE = 25;

    private const SOURCES = [
        'admin_configuration' => ['label' => 'Admin Configuration Audit', 'migration' => '2026_11_23_120000_create_admin_configuration_audit_logs_table.php'],
        'system_settings' => ['label' => 'System Settings Audit', 'migration' => '2026_11_20_080004_create_system_settings_tables.php'],
        'document_verification' => ['label' => 'Document Verification History', 'migration' => '2026_11_23_160000_add_document_verification_workflow.php'],
        'registration' => ['label' => 'Registration Activity', 'migration' => '2026_11_15_130013_create_registration_logs_table.php'],
    ];

    /** @param array<string, mixed> $filters */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $union = $this->union($filters);
        $page = Paginator::resolveCurrentPage('page');

        if ($union === null) {
            return new Paginator([], 0, self::PER_PAGE, $page, ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']);
        }

        return DB::query()->fromSub($union, 'activities')
            ->orderByDesc('occurred_at')
            ->orderBy('source')
            ->orderByDesc('event_id')
            ->paginate(self::PER_PAGE, ['*'], 'page', $page)
            ->through(fn (object $event): object => $this->present($event));
    }

    /** @param array<string, mixed> $filters @return array<string, int> */
    public function summary(array $filters): array
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $summaryFilters = [...$filters, 'date_from' => $today, 'date_to' => $today];
        $union = $this->union($summaryFilters);
        $counts = ['total' => 0, 'administrative' => 0, 'verification' => 0, 'registration' => 0];

        if ($union === null) {
            return $counts;
        }

        foreach (DB::query()->fromSub($union, 'activities')->select('source')->selectRaw('COUNT(*) AS total')->groupBy('source')->get() as $row) {
            $count = (int) $row->total;
            $counts['total'] += $count;
            if ($row->source === 'document_verification') {
                $counts['verification'] += $count;
            } elseif ($row->source === 'registration') {
                $counts['registration'] += $count;
            } else {
                $counts['administrative'] += $count;
            }
        }

        return $counts;
    }

    /** @return array<string, array{label: string, migration: string, available: bool}> */
    public function sourceHealth(): array
    {
        $tables = [
            'admin_configuration' => 'admin_configuration_audit_logs',
            'system_settings' => 'system_setting_logs',
            'document_verification' => 'registration_document_verifications',
            'registration' => 'registration_logs',
        ];

        $health = [];
        foreach (self::SOURCES as $source => $metadata) {
            $health[$source] = [...$metadata, 'available' => Schema::hasTable($tables[$source])];
        }

        return $health;
    }

    /** @return array<int, string> */
    public function availableActions(): array
    {
        $union = $this->union([]);

        return $union === null ? [] : DB::query()->fromSub($union, 'activities')->distinct()->orderBy('action')->pluck('action')->all();
    }

    /** @return array<int, object> */
    public function actors(): array
    {
        return DB::table('users')->where('account_type', 'ADMIN')->orderBy('name')->get(['id', 'name'])->all();
    }

    /** @param array<string, mixed> $filters */
    private function union(array $filters): ?Builder
    {
        $health = $this->sourceHealth();
        $queries = [];

        if ($health['admin_configuration']['available'] && (! isset($filters['source']) || $filters['source'] === null || $filters['source'] === 'admin_configuration')) {
            $query = DB::table('admin_configuration_audit_logs AS logs')
                ->leftJoin('users', 'users.id', '=', 'logs.user_id')
                ->selectRaw("'admin_configuration' AS source, logs.id AS event_id, logs.created_at AS occurred_at, logs.user_id AS actor_id, users.name AS actor_display, 'administrator' AS actor_type, logs.action, logs.entity_type AS subject_type, logs.entity_id AS subject_id, NULL AS registration_number, NULL AS from_status, NULL AS to_status")
                ->whereNot(function (Builder $query) use ($health): void {
                    if ($health['document_verification']['available']) {
                        $query->where('logs.entity_type', 'registration_document')->where('logs.action', 'like', 'verification_%');
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                });
            $queries[] = $this->applyFilters($query, $filters, 'logs.created_at', 'logs.action', 'logs.user_id', ['logs.entity_type']);
        }

        if ($health['system_settings']['available'] && (! isset($filters['source']) || $filters['source'] === null || $filters['source'] === 'system_settings')) {
            $query = DB::table('system_setting_logs AS logs')
                ->leftJoin('users', 'users.id', '=', 'logs.user_id')
                ->selectRaw("'system_settings' AS source, logs.id AS event_id, logs.created_at AS occurred_at, logs.user_id AS actor_id, users.name AS actor_display, 'administrator' AS actor_type, 'setting_updated' AS action, 'system_setting' AS subject_type, logs.id AS subject_id, NULL AS registration_number, NULL AS from_status, NULL AS to_status");
            $systemFilters = $filters;
            if (! empty($systemFilters['action']) && $systemFilters['action'] !== 'setting_updated') {
                $query->whereRaw('1 = 0');
            }
            $queries[] = $this->applyFilters($query, $systemFilters, 'logs.created_at', null, 'logs.user_id', ['logs.setting_key', 'users.name']);
        }

        if ($health['document_verification']['available'] && (! isset($filters['source']) || $filters['source'] === null || $filters['source'] === 'document_verification')) {
            $query = DB::table('registration_document_verifications AS logs')
                ->join('registration_documents AS documents', 'documents.id', '=', 'logs.registration_document_id')
                ->join('registrations', 'registrations.id', '=', 'documents.registration_id')
                ->leftJoin('users', 'users.id', '=', 'logs.actor_id')
                ->selectRaw("'document_verification' AS source, logs.id AS event_id, logs.created_at AS occurred_at, logs.actor_id AS actor_id, users.name AS actor_display, 'administrator' AS actor_type, CONCAT('verification_', logs.to_status) AS action, 'registration_document' AS subject_type, logs.registration_document_id AS subject_id, registrations.registration_number AS registration_number, logs.from_status AS from_status, logs.to_status AS to_status");
            $documentFilters = $filters;
            if (! empty($documentFilters['action'])) {
                $documentFilters['action'] = str_starts_with($documentFilters['action'], 'verification_')
                    ? substr($documentFilters['action'], strlen('verification_'))
                    : '__no_matching_action__';
            }
            $queries[] = $this->applyFilters($query, $documentFilters, 'logs.created_at', 'logs.to_status', 'logs.actor_id', ['registrations.registration_number', 'users.name']);
        }

        if ($health['registration']['available'] && (! isset($filters['source']) || $filters['source'] === null || $filters['source'] === 'registration')) {
            $query = DB::table('registration_logs AS logs')
                ->join('registrations', 'registrations.id', '=', 'logs.registration_id')
                ->leftJoin('users', 'users.id', '=', 'logs.user_id')
                ->selectRaw("'registration' AS source, logs.id AS event_id, logs.created_at AS occurred_at, logs.user_id AS actor_id, CASE WHEN users.account_type = 'admin' THEN users.name WHEN users.account_type = 'applicant' THEN 'Pendaftar' ELSE NULL END AS actor_display, CASE WHEN users.account_type = 'admin' THEN 'administrator' WHEN users.account_type = 'applicant' THEN 'applicant' ELSE 'unknown' END AS actor_type, logs.action, 'registration' AS subject_type, logs.registration_id AS subject_id, registrations.registration_number AS registration_number, NULL AS from_status, NULL AS to_status");
            $queries[] = $this->applyFilters($query, $filters, 'logs.created_at', 'logs.action', 'logs.user_id', ['registrations.registration_number', 'users.name']);
        }

        if ($queries === []) {
            return null;
        }

        $union = array_shift($queries);
        foreach ($queries as $query) {
            $union->unionAll($query);
        }

        return $union;
    }

    /** @param array<string, mixed> $filters @param array<int, string> $searchColumns */
    private function applyFilters(Builder $query, array $filters, string $dateColumn, ?string $actionColumn, string $actorColumn, array $searchColumns): Builder
    {
        if (! empty($filters['date_from'])) {
            $query->where($dateColumn, '>=', $filters['date_from'].' 00:00:00');
        }
        if (! empty($filters['date_to'])) {
            $query->where($dateColumn, '<=', $filters['date_to'].' 23:59:59');
        }
        if ($actionColumn !== null && ! empty($filters['action'])) {
            $query->where($actionColumn, $filters['action']);
        }
        if (! empty($filters['actor_id'])) {
            $query->where($actorColumn, (int) $filters['actor_id']);
        }
        if (! empty($filters['search'])) {
            $needle = '%'.addcslashes((string) $filters['search'], '%_\\').'%';
            $query->where(function (Builder $query) use ($searchColumns, $needle): void {
                foreach ($searchColumns as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}($column, 'like', $needle);
                }
            });
        }

        return $query;
    }

    private function present(object $event): object
    {
        $event->event_label = $this->label((string) $event->source, (string) $event->action, (string) $event->subject_type);
        $event->safe_summary = $this->summaryText((string) $event->source, (string) $event->action, $event->from_status, $event->to_status);
        $event->occurred_at = Carbon::parse($event->occurred_at, (string) config('app.timezone', 'UTC'))->setTimezone('Asia/Jakarta');
        $event->actor_display = $event->actor_display ?: 'Aktor tidak tersedia';

        return $event;
    }

    private function label(string $source, string $action, string $subject): string
    {
        return match (true) {
            $action === 'created' && $subject === 'admin_user' => 'Akun admin dibuat',
            $action === 'status_changed' && $subject === 'admin_user' => 'Status akun admin berubah',
            $action === 'permissions_updated' && $subject === 'role' => 'Permission role diperbarui',
            $action === 'admin_report_export_requested' => 'Ekspor laporan diminta',
            str_starts_with($action, 'verification_') && str_ends_with($action, 'APPROVED') => 'Dokumen disetujui',
            str_starts_with($action, 'verification_') && str_ends_with($action, 'REJECTED') => 'Dokumen ditolak',
            str_starts_with($action, 'verification_') => 'Status dokumen diperbarui',
            $action === 'reopen_window_opened' => 'Akses edit ulang dibuka',
            $action === 'reopen_window_closed' => 'Akses edit ulang ditutup',
            $action === 'submitted' => 'Pendaftaran dikirim',
            $action === 'pdf_generated' => 'PDF pendaftaran dibuat',
            $action === 'pdf_generation_failed' => 'Pembuatan PDF gagal',
            $action === 'setting_updated' => 'Pengaturan sistem diperbarui',
            $source === 'registration' => 'Aktivitas pendaftaran',
            default => 'Aktivitas administratif',
        };
    }

    private function summaryText(string $source, string $action, mixed $fromStatus, mixed $toStatus): string
    {
        if ($source === 'document_verification' && $fromStatus && $toStatus) {
            return 'Status dokumen: '.(string) $fromStatus.' → '.(string) $toStatus;
        }

        if ($source === 'system_settings') {
            return 'Nama institusi diperbarui';
        }

        return match ($action) {
            'submitted' => 'Pendaftaran masuk ke tahap verifikasi.',
            'reopen_window_opened' => 'Jendela edit ulang dibuka.',
            'reopen_window_closed' => 'Jendela edit ulang ditutup.',
            default => 'Aktivitas tercatat pada sistem.',
        };
    }
}
