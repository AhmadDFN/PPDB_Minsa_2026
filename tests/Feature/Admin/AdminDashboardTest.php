<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AdmissionPeriodStatus;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Applicant;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use Carbon\CarbonInterface;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_admin_with_view_dashboard_permission_can_access_dashboard(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');
        $period = $this->createActivePeriod();

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Admin PPDB');
        $response->assertSee('Ruang Kerja Operasional');
        $response->assertSee($period->name);
        $response->assertSee('TP '.$period->academic_year);
        $response->assertSee('Statistik Pendaftaran Periode Ini');
    }

    public function test_superadmin_uses_the_same_admin_dashboard(): void
    {
        $superadmin = $this->createAdminUser('superadmin');
        $this->createActivePeriod();

        $response = $this->actingAs($superadmin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Admin PPDB');
        $response->assertSee('Panel Admin PPDB');
    }

    public function test_single_workspace_has_no_superadmin_dashboard_route(): void
    {
        $this->assertFalse(Route::has('superadmin.dashboard'));
        $this->get('/superadmin/dashboard')->assertNotFound();
    }

    public function test_superadmin_uses_operational_metrics_and_permissioned_settings(): void
    {
        $superadmin = $this->createAdminUser('superadmin');
        $this->createAdminUser('admin_ppdb');
        $period = $this->createActivePeriod();
        $program = Program::factory()->create(['code' => 'REGULER', 'name' => 'Program Reguler']);
        $periodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
            'is_active' => true,
        ]);

        $this->createRegistration($period, $periodProgram, RegistrationStatus::SUBMITTED, 'Global Submitted', now());
        $this->createRegistration($period, $periodProgram, RegistrationStatus::DRAFT, 'Global Draft');

        $response = $this->actingAs($superadmin)->get(route('admin.dashboard'));

        $response->assertOk();
        $stats = $response->viewData('stats');
        $this->assertSame(2, $stats['total']);
        $this->assertSame(1, $stats['submitted']);
        $this->assertSame(1, $stats['draft']);
        $response->assertSee(route('admin.settings.index'));
    }

    public function test_admin_dashboard_has_real_submission_trend(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');
        $period = $this->createActivePeriod();
        $program = Program::factory()->create(['code' => 'REGULER', 'name' => 'Program Reguler']);
        $periodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
            'is_active' => true,
        ]);
        $this->createRegistration($period, $periodProgram, RegistrationStatus::SUBMITTED, 'Trend Student', now());

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $trend = $response->viewData('submissionTrend');
        $this->assertCount(7, $trend);
        $this->assertSame(1, collect($trend)->sum('count'));
    }

    public function test_admin_without_view_dashboard_permission_is_forbidden(): void
    {
        /** @var User $adminWithoutPermission */
        $adminWithoutPermission = User::factory()->create([
            'email' => 'noperm@minsatama.sch.id',
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
        ]);
        // Do not assign any role or permission

        $response = $this->actingAs($adminWithoutPermission)
            ->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_applicant_cannot_access_admin_dashboard(): void
    {
        $applicantUser = User::factory()->applicant()->create();

        $response = $this->actingAs($applicantUser)
            ->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_unauthenticated_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_displays_accurate_aggregated_statistics_for_active_period(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');
        $period = $this->createActivePeriod();

        $programReguler = Program::firstOrCreate(
            ['code' => 'REGULER'],
            ['name' => 'Program Reguler', 'description' => 'Reguler', 'is_active' => true]
        );
        $programUnggulan = Program::firstOrCreate(
            ['code' => 'UNGGULAN'],
            ['name' => 'Program Unggulan', 'description' => 'Unggulan', 'is_active' => true]
        );

        $periodReguler = AdmissionPeriodProgram::create([
            'admission_period_id' => $period->id,
            'program_id' => $programReguler->id,
            'is_active' => true,
        ]);

        $periodUnggulan = AdmissionPeriodProgram::create([
            'admission_period_id' => $period->id,
            'program_id' => $programUnggulan->id,
            'is_active' => true,
        ]);

        // 2 Submitted Reguler
        $this->createRegistration($period, $periodReguler, RegistrationStatus::SUBMITTED, 'Ahmad Reguler 1', now()->subMinutes(10));
        $this->createRegistration($period, $periodReguler, RegistrationStatus::SUBMITTED, 'Budi Reguler 2', now()->subMinutes(8));

        // 1 Submitted Unggulan
        $this->createRegistration($period, $periodUnggulan, RegistrationStatus::SUBMITTED, 'Citra Unggulan 1', now()->subMinutes(5));

        // 2 Draft Reguler
        $this->createRegistration($period, $periodReguler, RegistrationStatus::DRAFT, 'Draft 1', null);
        $this->createRegistration($period, $periodReguler, RegistrationStatus::DRAFT, 'Draft 2', null);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertOk();

        // Total: 5, Submitted: 3, Draft: 2, Reguler: 4, Unggulan: 1
        $viewData = $response->viewData('stats');
        $this->assertSame(5, $viewData['total']);
        $this->assertSame(3, $viewData['submitted']);
        $this->assertSame(2, $viewData['draft']);
        $this->assertSame(4, $viewData['reguler']);
        $this->assertSame(1, $viewData['unggulan']);

        $response->assertSee('Ahmad Reguler 1');
        $response->assertSee('Budi Reguler 2');
        $response->assertSee('Citra Unggulan 1');
    }

    public function test_dashboard_excludes_registrations_from_inactive_periods(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');

        // Active period with 1 registration
        $activePeriod = $this->createActivePeriod();
        $program = Program::firstOrCreate(
            ['code' => 'REGULER'],
            ['name' => 'Program Reguler', 'description' => 'Reguler', 'is_active' => true]
        );
        $activePeriodProgram = AdmissionPeriodProgram::create([
            'admission_period_id' => $activePeriod->id,
            'program_id' => $program->id,
            'is_active' => true,
        ]);
        $this->createRegistration($activePeriod, $activePeriodProgram, RegistrationStatus::SUBMITTED, 'Active Student', now());

        // Inactive / closed period with 3 registrations
        $closedPeriod = AdmissionPeriod::create([
            'name' => 'PPDB Periode Lampau',
            'academic_year' => '2024/2025',
            'start_date' => now()->subYear(),
            'end_date' => now()->subMonths(10),
            'age_reference_date' => '2024-07-01',
            'status' => AdmissionPeriodStatus::CLOSED,
            'is_active' => false,
        ]);
        $closedPeriodProgram = AdmissionPeriodProgram::create([
            'admission_period_id' => $closedPeriod->id,
            'program_id' => $program->id,
            'is_active' => true,
        ]);
        $this->createRegistration($closedPeriod, $closedPeriodProgram, RegistrationStatus::SUBMITTED, 'Old Student 1', now()->subYear());
        $this->createRegistration($closedPeriod, $closedPeriodProgram, RegistrationStatus::SUBMITTED, 'Old Student 2', now()->subYear());

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertOk();

        $viewData = $response->viewData('stats');
        $this->assertSame(1, $viewData['total']);
        $this->assertSame(1, $viewData['submitted']);
        $this->assertSame(0, $viewData['draft']);

        $response->assertSee('Active Student');
        $response->assertDontSee('Old Student 1');
    }

    public function test_dashboard_displays_latest_five_submitted_registrations(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');
        $period = $this->createActivePeriod();
        $program = Program::firstOrCreate(
            ['code' => 'REGULER'],
            ['name' => 'Program Reguler', 'description' => 'Reguler', 'is_active' => true]
        );
        $periodProgram = AdmissionPeriodProgram::create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
            'is_active' => true,
        ]);

        // Create 6 submitted registrations with increasing submission times
        for ($i = 1; $i <= 6; $i++) {
            $this->createRegistration(
                $period,
                $periodProgram,
                RegistrationStatus::SUBMITTED,
                "Siswa Urut {$i}",
                now()->subMinutes(60 - ($i * 5))
            );
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertOk();

        $latest = $response->viewData('latestSubmissions');
        $this->assertCount(5, $latest);

        // Siswa Urut 6 should be first (most recent)
        $this->assertSame('Siswa Urut 6', $latest->first()->applicant->full_name);

        // Siswa Urut 1 should NOT be in the latest 5
        $this->assertFalse($latest->pluck('applicant.full_name')->contains('Siswa Urut 1'));
    }

    public function test_dashboard_handles_empty_active_period_gracefully(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');

        // Make sure no active period exists
        AdmissionPeriod::query()->delete();

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Tidak Ada Periode Pendaftaran Aktif');

        $viewData = $response->viewData('stats');
        $this->assertSame(0, $viewData['total']);
        $this->assertSame(0, $viewData['submitted']);
        $this->assertSame(0, $viewData['draft']);
    }

    public function test_dashboard_does_not_leak_applicant_nik(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');
        $period = $this->createActivePeriod();
        $program = Program::firstOrCreate(
            ['code' => 'REGULER'],
            ['name' => 'Program Reguler', 'description' => 'Reguler', 'is_active' => true]
        );
        $periodProgram = AdmissionPeriodProgram::create([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
            'is_active' => true,
        ]);

        $plainNik = '3573010101190001';
        $applicantUser = User::factory()->applicant()->create([
            'nik_encrypted' => Crypt::encryptString($plainNik),
        ]);
        $applicant = Applicant::factory()->for($applicantUser)->create([
            'full_name' => 'Calon Siswa Rahasia',
        ]);
        Registration::create([
            'registration_number' => 'PPDB/2026-2027/REGULER/00099',
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $periodProgram->id,
            'status' => RegistrationStatus::SUBMITTED,
            'submitted_at' => now(),
            'data_revision' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Calon Siswa Rahasia');
        $response->assertDontSee($plainNik);
    }

    public function test_admin_root_redirects_to_admin_dashboard(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');

        $response = $this->actingAs($admin)
            ->get('/admin');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_upcoming_modules_redirect_gracefully_with_warning(): void
    {
        $admin = $this->createAdminUser('admin_ppdb');

        $response = $this->actingAs($admin)
            ->get('/admin/pengguna');

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('warning', 'Modul Manajemen Pengguna dijadwalkan pada fase pengembangan berikutnya.');
    }

    private function createAdminUser(string $role): User
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'email' => "{$role}@minsatama.sch.id",
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $admin->assignRole($role);

        return $admin;
    }

    private function createActivePeriod(): AdmissionPeriod
    {
        return AdmissionPeriod::create([
            'name' => 'PPDB MIN 1 Kota Madiun TP 2026/2027',
            'academic_year' => '2026/2027',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(14),
            'age_reference_date' => '2026-07-01',
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
            'description' => 'Penerimaan Peserta Didik Baru Madrasah Ibtidaiyah Negeri 1 Kota Madiun.',
        ]);
    }

    private function createRegistration(
        AdmissionPeriod $period,
        AdmissionPeriodProgram $periodProgram,
        RegistrationStatus $status,
        string $applicantName,
        ?CarbonInterface $submittedAt = null
    ): Registration {
        $user = User::factory()->applicant()->create();
        $applicant = Applicant::factory()->for($user)->create([
            'full_name' => $applicantName,
        ]);

        return Registration::create([
            'registration_number' => $status === RegistrationStatus::SUBMITTED
                ? 'PPDB/2026-2027/'.($periodProgram->program?->code ?? 'REGULER').'/'.uniqid()
                : null,
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $periodProgram->id,
            'status' => $status,
            'submitted_at' => $submittedAt,
            'data_revision' => 1,
        ]);
    }
}
