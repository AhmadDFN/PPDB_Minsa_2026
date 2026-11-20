<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AccountType;
use App\Enums\AdmissionPeriodStatus;
use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\AdmissionPeriod;
use App\Models\Registration;
use App\Models\RegistrationLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the administrative dashboard with real PPDB metrics.
     */
    public function index(Request $request): View
    {
        /** @var AdmissionPeriod|null $activePeriod */
        $activePeriod = AdmissionPeriod::query()
            ->where('is_active', true)
            ->where('status', AdmissionPeriodStatus::ACTIVE->value)
            ->with(['periodPrograms.program'])
            ->first();

        // Global metrics
        $totalAccounts = User::query()->where('account_type', AccountType::APPLICANT->value)->count();
        $totalDraftAll = Registration::query()->where('status', RegistrationStatus::DRAFT->value)->count();
        $totalSubmittedAll = Registration::query()->where('status', RegistrationStatus::SUBMITTED->value)->count();
        $totalRegistrationsAll = Registration::query()->count();

        $stats = [
            'total' => 0,
            'submitted' => 0,
            'draft' => 0,
            'reguler' => 0,
            'unggulan' => 0,
            'total_accounts' => $totalAccounts,
            'total_draft_all' => $totalDraftAll,
            'total_submitted_all' => $totalSubmittedAll,
            'total_registrations_all' => $totalRegistrationsAll,
        ];

        $latestSubmissions = collect();
        $periodTimeInfo = null;

        if ($activePeriod) {
            $periodId = $activePeriod->id;

            // Aggregation 1: Status counts for active period
            $statusCounts = Registration::query()
                ->where('admission_period_id', $periodId)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as submitted,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as draft
                ', [RegistrationStatus::SUBMITTED->value, RegistrationStatus::DRAFT->value])
                ->first();

            $stats['total'] = (int) ($statusCounts->total ?? 0);
            $stats['submitted'] = (int) ($statusCounts->submitted ?? 0);
            $stats['draft'] = (int) ($statusCounts->draft ?? 0);

            // Aggregation 2: Program breakdown for active period
            $programCounts = Registration::query()
                ->where('registrations.admission_period_id', $periodId)
                ->leftJoin('admission_period_programs', 'registrations.admission_period_program_id', '=', 'admission_period_programs.id')
                ->leftJoin('programs', 'admission_period_programs.program_id', '=', 'programs.id')
                ->selectRaw("
                    SUM(CASE WHEN UPPER(programs.code) = 'REGULER' THEN 1 ELSE 0 END) as reguler_count,
                    SUM(CASE WHEN UPPER(programs.code) = 'UNGGULAN' THEN 1 ELSE 0 END) as unggulan_count
                ")
                ->first();

            $stats['reguler'] = (int) ($programCounts->reguler_count ?? 0);
            $stats['unggulan'] = (int) ($programCounts->unggulan_count ?? 0);

            // Latest 5 officially submitted registrations for active period
            $latestSubmissions = Registration::query()
                ->where('admission_period_id', $periodId)
                ->where('status', RegistrationStatus::SUBMITTED)
                ->with([
                    'applicant:id,full_name',
                    'admissionPeriodProgram.program:id,name,code',
                ])
                ->orderByDesc('submitted_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get();

            // Calculate period time info in Asia/Jakarta
            $now = Carbon::now('Asia/Jakarta');
            $start = $activePeriod->start_date ? Carbon::parse($activePeriod->start_date)->setTimezone('Asia/Jakarta') : null;
            $end = $activePeriod->end_date ? Carbon::parse($activePeriod->end_date)->setTimezone('Asia/Jakarta') : null;

            $timeStatus = 'open';
            $countdownText = 'Pendaftaran Sedang Berlangsung';

            if ($start && $now->lt($start)) {
                $timeStatus = 'upcoming';
                $diffDays = (int) $now->diffInDays($start, false);
                $countdownText = $diffDays > 0
                    ? "Pendaftaran dimulai dalam {$diffDays} hari"
                    : 'Pendaftaran segera dimulai hari ini';
            } elseif ($end && $now->gt($end)) {
                $timeStatus = 'ended';
                $countdownText = 'Masa pendaftaran telah berakhir';
            } elseif ($end) {
                $timeStatus = 'open';
                $diffDays = (int) $now->diffInDays($end, false);
                $diffHours = (int) $now->diffInHours($end, false) % 24;

                if ($diffDays > 0) {
                    $countdownText = "Sisa waktu: {$diffDays} hari".($diffHours > 0 ? " {$diffHours} jam" : '');
                } else {
                    $diffTotalHours = (int) $now->diffInHours($end, false);
                    $diffMins = (int) $now->diffInMinutes($end, false) % 60;
                    $countdownText = $diffTotalHours > 0
                        ? "Sisa waktu: {$diffTotalHours} jam {$diffMins} menit"
                        : "Sisa waktu: {$diffMins} menit lagi";
                }
            }

            $periodTimeInfo = [
                'start_formatted' => $start ? $start->translatedFormat('d F Y, H:i').' WIB' : '-',
                'end_formatted' => $end ? $end->translatedFormat('d F Y, H:i').' WIB' : '-',
                'time_status' => $timeStatus,
                'countdown_text' => $countdownText,
            ];
        } else {
            // When no active period, show overall latest submitted registrations
            $latestSubmissions = Registration::query()
                ->where('status', RegistrationStatus::SUBMITTED)
                ->with([
                    'applicant:id,full_name',
                    'admissionPeriodProgram.program:id,name,code',
                ])
                ->orderByDesc('submitted_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get();
        }

        // Recent audit activity logs (5 latest activities)
        $recentActivities = RegistrationLog::query()
            ->with(['user:id,name,email', 'registration.applicant:id,full_name', 'registration:id,registration_number'])
            ->latest('id')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'activePeriod' => $activePeriod,
            'stats' => $stats,
            'latestSubmissions' => $latestSubmissions,
            'recentActivities' => $recentActivities,
            'periodTimeInfo' => $periodTimeInfo,
        ]);
    }
}
