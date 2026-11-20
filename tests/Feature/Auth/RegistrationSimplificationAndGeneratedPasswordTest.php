<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AdmissionPeriodStatus;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Program;
use App\Models\Registration;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Authentication\Delivery\ArrayOtpDelivery;
use App\Services\Registration\RegistrationNumberGeneratorService;
use App\Support\NikManager;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationSimplificationAndGeneratedPasswordTest extends TestCase
{
    use RefreshDatabase;

    private AdmissionPeriod $period;

    private Program $regulerProgram;

    private Program $unggulanProgram;

    private AdmissionPeriodProgram $regulerPeriodProgram;

    private AdmissionPeriodProgram $unggulanPeriodProgram;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        ArrayOtpDelivery::clear();

        $this->period = AdmissionPeriod::factory()->create([
            'academic_year' => '2026/2027',
            'start_date' => Carbon::now()->subDays(2),
            'end_date' => Carbon::now()->addDays(30),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);

        $this->regulerProgram = Program::factory()->create(['name' => 'Program Reguler', 'code' => 'REGULER', 'is_active' => true]);
        $this->unggulanProgram = Program::factory()->create(['name' => 'Program Unggulan', 'code' => 'UNGGULAN', 'is_active' => true]);

        $this->regulerPeriodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $this->period->id,
            'program_id' => $this->regulerProgram->id,
            'is_active' => true,
        ]);

        $this->unggulanPeriodProgram = AdmissionPeriodProgram::factory()->create([
            'admission_period_id' => $this->period->id,
            'program_id' => $this->unggulanProgram->id,
            'is_active' => true,
        ]);
    }

    public function test_simplified_registration_view_renders_only_four_inputs_without_password_or_biodata(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('nik');
        $response->assertSee('full_name');
        $response->assertSee('phone');
        $response->assertSee('consent');

        $response->assertDontSee('name="password"', false);
        $response->assertDontSee('name="password_confirmation"', false);
        $response->assertDontSee('name="gender"', false);
        $response->assertDontSee('name="birth_place"', false);
        $response->assertDontSee('name="birth_date"', false);
    }

    public function test_registration_validation_fails_if_required_inputs_are_missing(): void
    {
        $response = $this->post(route('register.store'), []);

        $response->assertSessionHasErrors(['nik', 'full_name', 'phone', 'consent']);
    }

    public function test_successful_registration_creates_pending_user_and_assigns_5_digit_registration_number(): void
    {
        $nik = '3573010101200001';
        $phone = '081234567890';

        $response = $this->post(route('register.store'), [
            'nik' => $nik,
            'full_name' => 'Ananda Pratama',
            'phone' => $phone,
            'consent' => '1',
        ]);

        $response->assertRedirect(route('activation.form'));

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();
        $this->assertNotNull($user);
        $this->assertSame(AccountStatus::PENDING_ACTIVATION, $user->account_status);
        $this->assertSame(AccountType::APPLICANT, $user->account_type);

        $applicant = $user->applicant;
        $this->assertNotNull($applicant);
        $this->assertSame('Ananda Pratama', $applicant->full_name);
        $this->assertNull($applicant->gender);
        $this->assertNull($applicant->birth_date);

        $registration = Registration::where('applicant_id', $applicant->id)->first();
        $this->assertNotNull($registration);
        $this->assertSame('27001', $registration->registration_number);
        $this->assertSame(RegistrationStatus::DRAFT, $registration->status);

        $otp = ArrayOtpDelivery::latestOtpFor($phone);
        $this->assertNotNull($otp);
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $otp);
        $this->assertNull(ArrayOtpDelivery::latestPasswordFor($phone));
    }

    public function test_5_digit_registration_number_increments_globally_across_programs(): void
    {
        $generator = app(RegistrationNumberGeneratorService::class);

        $num1 = $generator->generateForPeriod($this->period);
        $num2 = $generator->generateForPeriod($this->period);
        $num3 = $generator->generateForPeriod($this->period);

        $this->assertSame('27001', $num1);
        $this->assertSame('27002', $num2);
        $this->assertSame('27003', $num3);
    }

    public function test_5_digit_registration_number_throws_exception_when_exceeding_999(): void
    {
        $generator = app(RegistrationNumberGeneratorService::class);
        $intakeYear = $generator->extractIntakeYear($this->period);

        DB::table('yearly_registration_sequences')
            ->insert(['intake_year' => $intakeYear, 'last_sequence' => 999, 'created_at' => now(), 'updated_at' => now()]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Urutan nomor pendaftaran telah mencapai batas maksimal (999).');

        $generator->generateForPeriod($this->period);
    }

    public function test_otp_verification_activates_account_generates_temporary_password_and_dispatches_via_whatsapp(): void
    {
        $nik = '3573010101200002';
        $phone = '081234567891';

        $this->post(route('register.store'), [
            'nik' => $nik,
            'full_name' => 'Budi Santoso',
            'phone' => $phone,
            'consent' => '1',
        ]);

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->firstOrFail();
        $plainOtp = ArrayOtpDelivery::latestOtpFor($phone);
        $this->assertNotNull($plainOtp);

        $verifyResponse = $this->withSession(['activation_user_id' => $user->id])
            ->post(route('activation.verify'), ['otp' => $plainOtp]);

        $verifyResponse->assertRedirect(route('login'));
        $verifyResponse->assertSessionHas('success');

        $user->refresh();
        $this->assertSame(AccountStatus::ACTIVE, $user->account_status);
        $this->assertTrue($user->must_change_password);

        $tempPassword = ArrayOtpDelivery::latestPasswordFor($phone);
        $this->assertNotNull($tempPassword);
        $this->assertSame(10, strlen($tempPassword));
        $this->assertTrue(Hash::check($tempPassword, $user->password));

        // OTP list and password list must be separate
        $this->assertNotSame($plainOtp, $tempPassword);
    }

    public function test_first_login_with_temporary_password_forces_redirect_to_change_password(): void
    {
        $nik = '3573010101200003';
        $phone = '081234567892';

        $this->post(route('register.store'), [
            'nik' => $nik,
            'full_name' => 'Citra Lestari',
            'phone' => $phone,
            'consent' => '1',
        ]);

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->firstOrFail();
        $plainOtp = ArrayOtpDelivery::latestOtpFor($phone);
        $this->withSession(['activation_user_id' => $user->id])
            ->post(route('activation.verify'), ['otp' => $plainOtp]);

        $tempPassword = ArrayOtpDelivery::latestPasswordFor($phone);
        $user->refresh();

        // Login attempt
        $loginResponse = $this->post(route('login.store'), [
            'nik' => $nik,
            'password' => $tempPassword,
        ]);

        $loginResponse->assertRedirect(route('applicant.password.change'));
        $this->assertAuthenticatedAs($user);

        // Attempting to access dashboard directly is intercepted by middleware
        $dashboardResponse = $this->actingAs($user)->get(route('applicant.dashboard'));
        $dashboardResponse->assertRedirect(route('applicant.password.change'));
        $dashboardResponse->assertSessionHas('warning', 'Anda menggunakan kata sandi sementara. Demi keamanan akun, Anda wajib membuat kata sandi baru terlebih dahulu.');

        // Update password
        $changeResponse = $this->actingAs($user)->post(route('applicant.password.change.update'), [
            'current_password' => $tempPassword,
            'password' => 'KataSandiBaru2026!',
            'password_confirmation' => 'KataSandiBaru2026!',
        ]);

        $changeResponse->assertRedirect(route('applicant.dashboard'));
        $changeResponse->assertSessionHas('success', 'Kata sandi baru berhasil disimpan. Akun Anda kini sepenuhnya aman.');

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertNotNull($user->password_changed_at);

        // Subsequent dashboard access is permitted
        $this->actingAs($user)->get(route('applicant.dashboard'))->assertOk();
    }

    public function test_resend_temporary_password_rotates_password_and_delivers_new_one(): void
    {
        $nik = '3573010101200004';
        $phone = '081234567893';

        $this->post(route('register.store'), [
            'nik' => $nik,
            'full_name' => 'Dewi Sartika',
            'phone' => $phone,
            'consent' => '1',
        ]);

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->firstOrFail();
        $plainOtp = ArrayOtpDelivery::latestOtpFor($phone);
        $this->withSession(['activation_user_id' => $user->id])
            ->post(route('activation.verify'), ['otp' => $plainOtp]);

        $firstPassword = ArrayOtpDelivery::latestPasswordFor($phone);

        // Resend temporary password (redirects back to login page)
        $resendResponse = $this->withSession(['activated_user_id' => $user->id])
            ->from(route('login'))
            ->post(route('activation.resend_password'));

        $resendResponse->assertRedirect(route('login'));
        $resendResponse->assertSessionHas('success');

        $secondPassword = ArrayOtpDelivery::latestPasswordFor($phone);
        $this->assertNotNull($secondPassword);
        $this->assertNotSame($firstPassword, $secondPassword);

        $user->refresh();
        $this->assertTrue(Hash::check($secondPassword, $user->password));
        $this->assertFalse(Hash::check($firstPassword, $user->password));
    }

    public function test_superadmin_can_update_dynamic_branding_and_admin_cannot(): void
    {
        $superadmin = User::factory()->create([
            'email' => 'superadmin@minsatama.sch.id',
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $superadmin->assignRole('superadmin');

        $adminPpdb = User::factory()->create([
            'email' => 'admin@minsatama.sch.id',
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $adminPpdb->assignRole('admin_ppdb');

        // Admin PPDB forbidden (403)
        $this->actingAs($adminPpdb)->get(route('admin.settings.index'))->assertForbidden();
        $this->actingAs($adminPpdb)->post(route('admin.settings.update'), [
            'institution_display_name' => 'Hacked Name',
        ])->assertForbidden();

        // Superadmin authorized
        $this->actingAs($superadmin)->get(route('admin.settings.index'))->assertOk();

        $updateResponse = $this->actingAs($superadmin)->post(route('admin.settings.update'), [
            'institution_display_name' => 'PPDB MADRASAH IBTIDAIYAH NEGERI 1 KOTA MADIUN',
        ]);

        $updateResponse->assertRedirect(route('admin.settings.index'));
        $updateResponse->assertSessionHas('success');

        $this->assertSame('PPDB MADRASAH IBTIDAIYAH NEGERI 1 KOTA MADIUN', SystemSetting::getInstitutionName());

        $this->assertDatabaseHas('system_settings', [
            'key' => 'institution_display_name',
            'value' => 'PPDB MADRASAH IBTIDAIYAH NEGERI 1 KOTA MADIUN',
        ]);

        $this->assertDatabaseHas('system_setting_logs', [
            'setting_key' => 'institution_display_name',
            'user_id' => $superadmin->id,
            'new_value' => 'PPDB MADRASAH IBTIDAIYAH NEGERI 1 KOTA MADIUN',
        ]);
    }
}
