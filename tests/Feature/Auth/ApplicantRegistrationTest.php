<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\Gender;
use App\Models\Applicant;
use App\Models\User;
use App\Services\Authentication\Delivery\ArrayOtpDelivery;
use App\Support\NikManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ArrayOtpDelivery::clear();
    }

    public function test_can_view_registration_page(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Akun Baru');
        $response->assertSee('Nomor Induk Kependudukan (NIK) Calon Siswa');
    }

    public function test_registration_validates_required_fields(): void
    {
        $response = $this->post(route('register.store'), []);

        $response->assertSessionHasErrors([
            'nik',
            'full_name',
            'gender',
            'birth_place',
            'birth_date',
            'phone',
            'consent',
        ]);
    }

    public function test_registration_rejects_invalid_nik_format(): void
    {
        $payload = [
            'nik' => '12345', // Not 16 digits
            'full_name' => 'Ahmad Fulan',
            'gender' => 'L',
            'birth_place' => 'Madiun',
            'birth_date' => '2019-05-15',
            'phone' => '081234567890',
            'consent' => '1',
        ];

        $response = $this->post(route('register.store'), $payload);

        $response->assertSessionHasErrors(['nik']);
    }

    public function test_successful_registration_atomically_creates_user_applicant_and_dispatches_otp(): void
    {
        $nik = '3573010101190001';
        $payload = [
            'nik' => $nik,
            'full_name' => 'Fulan bin Fulan',
            'gender' => 'L',
            'birth_place' => 'Kota Madiun',
            'birth_date' => '2019-03-20',
            'phone' => '081234567890',
            'consent' => '1',
        ];

        $response = $this->post(route('register.store'), $payload);

        $response->assertRedirect(route('activation.form'));
        $response->assertSessionHas('success');

        // Verify User was created with PENDING_ACTIVATION
        $expectedHash = NikManager::hash($nik);
        $user = User::where('nik_lookup_hash', $expectedHash)->first();
        $this->assertNotNull($user);
        $this->assertSame(AccountStatus::PENDING_ACTIVATION, $user->account_status);
        $this->assertSame(AccountType::APPLICANT, $user->account_type);
        $this->assertSame('081234567890', $user->phone);
        $this->assertNull($user->phone_verified_at);
        $this->assertSame($nik, $user->getDecryptedNik());

        // Verify Applicant profile was created
        $applicant = Applicant::where('user_id', $user->id)->first();
        $this->assertNotNull($applicant);
        $this->assertSame('Fulan bin Fulan', $applicant->full_name);
        $this->assertSame(Gender::MALE, $applicant->gender);
        $this->assertSame('Kota Madiun', $applicant->birth_place);
        $this->assertSame('2019-03-20', $applicant->birth_date->format('Y-m-d'));

        // Verify OTP was dispatched via delivery abstraction
        $dispatchedOtp = ArrayOtpDelivery::latestOtpFor('081234567890');
        $this->assertNotNull($dispatchedOtp);
        $this->assertSame(6, strlen($dispatchedOtp));

        // Verify session contains activation user ID
        $this->assertSame($user->id, session('activation_user_id'));
    }

    public function test_rejects_duplicate_nik_registration_when_account_is_active(): void
    {
        $nik = '3573010101190002';
        $payload = [
            'nik' => $nik,
            'full_name' => 'Anak Pertama',
            'gender' => 'L',
            'birth_place' => 'Madiun',
            'birth_date' => '2019-01-10',
            'phone' => '081234567890',
            'consent' => '1',
        ];

        // First registration: success
        $this->post(route('register.store'), $payload)->assertRedirect(route('activation.form'));

        // Mark account as ACTIVE (completed activation)
        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();
        $user->update(['account_status' => AccountStatus::ACTIVE]);

        // Second registration with identical NIK when ACTIVE: must fail
        $response = $this->post(route('register.store'), $payload);
        $response->assertSessionHasErrors(['nik']);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('applicants', 1);
    }

    public function test_allows_pending_registration_recovery_with_same_nik(): void
    {
        $nik = '3573010101190003';
        $payload = [
            'nik' => $nik,
            'full_name' => 'Anak Pending',
            'gender' => 'L',
            'birth_place' => 'Madiun',
            'birth_date' => '2019-01-10',
            'phone' => '081234567890',
            'consent' => '1',
        ];

        // First registration
        $this->post(route('register.store'), $payload)->assertRedirect(route('activation.form'));

        // Second registration with same NIK but updated phone
        $payload['phone'] = '081299998888';
        $response = $this->post(route('register.store'), $payload);

        $response->assertRedirect(route('activation.form'));
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('applicants', 1);

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();
        $this->assertSame('081299998888', $user->phone);
    }

    public function test_allows_sibling_registration_with_same_phone_number(): void
    {
        // First sibling
        $sibling1 = [
            'nik' => '3573010101190011',
            'full_name' => 'Kakak Pertama',
            'gender' => 'L',
            'birth_place' => 'Madiun',
            'birth_date' => '2018-05-10',
            'phone' => '081234567890',
            'consent' => '1',
        ];
        $this->post(route('register.store'), $sibling1)->assertRedirect(route('activation.form'));

        // Second sibling with same parent phone
        $sibling2 = [
            'nik' => '3573010101190022',
            'full_name' => 'Adik Kedua',
            'gender' => 'P',
            'birth_place' => 'Madiun',
            'birth_date' => '2019-09-12',
            'phone' => '081234567890', // Identical phone
            'consent' => '1',
        ];
        $this->post(route('register.store'), $sibling2)->assertRedirect(route('activation.form'));

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('applicants', 2);
    }

    public function test_authenticated_applicant_is_redirected_away_from_registration(): void
    {
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
        ]);
        Applicant::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('register'));

        $response->assertRedirect(route('dashboard'));
    }
}
