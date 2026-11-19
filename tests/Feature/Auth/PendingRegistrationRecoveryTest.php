<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\Gender;
use App\Enums\OtpPurpose;
use App\Models\User;
use App\Models\UserOtpToken;
use App\Services\Authentication\Delivery\ArrayOtpDelivery;
use App\Services\Authentication\OtpDeliveryService;
use App\Services\Authentication\OtpService;
use App\Support\NikManager;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PendingRegistrationRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        ArrayOtpDelivery::clear();
    }

    public function test_1_new_nik_can_perform_normal_registration(): void
    {
        $nik = '3573010101190001';
        $payload = $this->validPayload($nik, '081234567890');

        $response = $this->post(route('register.store'), $payload);

        $response->assertRedirect(route('activation.form'));
        $response->assertSessionHas('success');

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();
        $this->assertNotNull($user);
        $this->assertSame(AccountStatus::PENDING_ACTIVATION, $user->account_status);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('applicants', 1);
    }

    public function test_2_pending_account_can_continue_registration_using_same_nik(): void
    {
        $nik = '3573010101190002';
        $payload = $this->validPayload($nik, '081111111111');

        // First registration attempt
        $this->post(route('register.store'), $payload)->assertRedirect(route('activation.form'));

        // Second registration attempt using same NIK with corrected phone
        $payload['phone'] = '082222222222';
        $response = $this->post(route('register.store'), $payload);

        $response->assertRedirect(route('activation.form'));
        $response->assertSessionHas('success');
        $this->assertStringContainsString('dipulihkan', session('success'));
    }

    public function test_3_no_duplicate_user_is_created_on_pending_recovery(): void
    {
        $nik = '3573010101190003';
        $payload = $this->validPayload($nik, '081111111111');

        $this->post(route('register.store'), $payload);
        $this->assertDatabaseCount('users', 1);

        $payload['phone'] = '082222222222';
        $this->post(route('register.store'), $payload);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_4_no_duplicate_applicant_is_created_on_pending_recovery(): void
    {
        $nik = '3573010101190004';
        $payload = $this->validPayload($nik, '081111111111');

        $this->post(route('register.store'), $payload);
        $this->assertDatabaseCount('applicants', 1);

        $payload['phone'] = '082222222222';
        $this->post(route('register.store'), $payload);

        $this->assertDatabaseCount('applicants', 1);
    }

    public function test_5_pending_registration_phone_number_can_be_corrected(): void
    {
        $nik = '3573010101190005';
        $payload = $this->validPayload($nik, '081111111111');

        $this->post(route('register.store'), $payload);

        $payload['phone'] = '089999888877';
        $this->post(route('register.store'), $payload);

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();
        $this->assertSame('089999888877', $user->phone);

        // Verify fresh OTP was dispatched to the corrected phone
        $this->assertNotNull(ArrayOtpDelivery::latestOtpFor('089999888877'));
    }

    public function test_6_matching_identity_permits_pending_recovery(): void
    {
        $nik = '3573010101190006';
        $payload = $this->validPayload($nik, '081111111111');
        $this->post(route('register.store'), $payload);

        // Same identity with slight formatting variation (e.g. lowercase name, extra whitespace)
        $payload['full_name'] = '  fulan bin fulan  ';
        $payload['phone'] = '082222222222';

        $response = $this->post(route('register.store'), $payload);
        $response->assertRedirect(route('activation.form'));
        $response->assertSessionHas('success');
    }

    public function test_7_mismatched_identity_is_rejected_on_pending_recovery(): void
    {
        $nik = '3573010101190007';
        $payload = $this->validPayload($nik, '081111111111');
        $this->post(route('register.store'), $payload);

        // Submitting with completely different name
        $tamperedPayload = $payload;
        $tamperedPayload['full_name'] = 'Orang Asing';
        $tamperedPayload['phone'] = '082222222222';

        $response = $this->post(route('register.store'), $tamperedPayload);
        $response->assertSessionHasErrors(['nik']);

        // Submitting with different birth date
        $tamperedBirthDate = $payload;
        $tamperedBirthDate['birth_date'] = '2018-01-01';
        $responseBirth = $this->post(route('register.store'), $tamperedBirthDate);
        $responseBirth->assertSessionHasErrors(['nik']);

        // Submitting with different gender
        $tamperedGender = $payload;
        $tamperedGender['gender'] = 'P';
        $responseGender = $this->post(route('register.store'), $tamperedGender);
        $responseGender->assertSessionHasErrors(['nik']);
    }

    public function test_8_active_account_nik_is_strictly_rejected_for_new_registration(): void
    {
        $nik = '3573010101190008';
        $payload = $this->validPayload($nik, '081111111111');

        $this->post(route('register.store'), $payload);

        // Simulate successful activation
        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();
        $user->update([
            'account_status' => AccountStatus::ACTIVE,
            'phone_verified_at' => now(),
        ]);

        // Attempt new registration with the active NIK
        $response = $this->post(route('register.store'), $payload);
        $response->assertSessionHasErrors(['nik']);
        $this->assertStringContainsString('sudah memiliki akun aktif', session('errors')->first('nik'));
    }

    public function test_9_suspended_account_cannot_be_recovered(): void
    {
        $nik = '3573010101190009';
        $payload = $this->validPayload($nik, '081111111111');

        $this->post(route('register.store'), $payload);

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();
        $user->update(['account_status' => AccountStatus::SUSPENDED]);

        $response = $this->post(route('register.store'), $payload);
        $response->assertSessionHasErrors(['nik']);
        $this->assertStringContainsString('ditangguhkan', session('errors')->first('nik'));
    }

    public function test_10_old_otp_is_invalidated_when_new_otp_is_issued(): void
    {
        $nik = '3573010101190010';
        $payload = $this->validPayload($nik, '081111111111');

        $this->post(route('register.store'), $payload);
        $firstOtp = ArrayOtpDelivery::latestOtpFor('081111111111');

        $payload['phone'] = '082222222222';
        $this->post(route('register.store'), $payload);
        $secondOtp = ArrayOtpDelivery::latestOtpFor('082222222222');

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();

        // Old OTP code must fail verification
        $otpService = new OtpService(new OtpDeliveryService(new ArrayOtpDelivery));
        $verifyOld = $otpService->verify($user, OtpPurpose::ACTIVATION, $firstOtp);
        $this->assertFalse($verifyOld['success']);
    }

    public function test_11_new_otp_can_be_successfully_verified(): void
    {
        $nik = '3573010101190011';
        $payload = $this->validPayload($nik, '081111111111');

        $this->post(route('register.store'), $payload);

        // Update to correct phone
        $payload['phone'] = '082222222222';
        $this->post(route('register.store'), $payload);

        $newOtp = ArrayOtpDelivery::latestOtpFor('082222222222');
        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();

        // Submit new OTP to verification endpoint
        $response = $this->withSession(['activation_user_id' => $user->id])
            ->post(route('activation.verify'), ['otp' => $newOtp]);

        $response->assertRedirect(route('activation.password.form'));
        $user->refresh();
        $this->assertNotNull($user->phone_verified_at);
    }

    public function test_12_account_remains_pending_before_otp_is_verified(): void
    {
        $nik = '3573010101190012';
        $payload = $this->validPayload($nik, '081111111111');

        $this->post(route('register.store'), $payload);

        $payload['phone'] = '082222222222';
        $this->post(route('register.store'), $payload);

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();
        $this->assertSame(AccountStatus::PENDING_ACTIVATION, $user->account_status);
    }

    public function test_13_phone_verified_at_remains_null_before_verification(): void
    {
        $nik = '3573010101190013';
        $payload = $this->validPayload($nik, '081111111111');

        $this->post(route('register.store'), $payload);

        $payload['phone'] = '082222222222';
        $this->post(route('register.store'), $payload);

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();
        $this->assertNull($user->phone_verified_at);
    }

    public function test_14_if_fonnte_rejects_request_account_does_not_become_active(): void
    {
        Config::set('ppdb.otp.delivery_driver', 'fonnte');
        Config::set('services.fonnte.token', 'test-token');

        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => false, 'reason' => 'invalid token'], 200),
        ]);

        $nik = '3573010101190014';
        $payload = $this->validPayload($nik, '081111111111');

        $response = $this->post(route('register.store'), $payload);

        $response->assertRedirect(route('activation.form'));
        $response->assertSessionHas('warning');

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();
        $this->assertNotNull($user);
        $this->assertSame(AccountStatus::PENDING_ACTIVATION, $user->account_status);
        $this->assertNull($user->phone_verified_at);
    }

    public function test_15_if_fonnte_times_out_no_duplicate_account_is_created(): void
    {
        Config::set('ppdb.otp.delivery_driver', 'fonnte');
        Config::set('services.fonnte.token', 'test-token');

        Http::fake([
            'https://api.fonnte.com/send' => fn () => throw new ConnectionException('Timeout'),
        ]);

        $nik = '3573010101190015';
        $payload = $this->validPayload($nik, '081111111111');

        // First attempt times out
        $this->post(route('register.store'), $payload)->assertRedirect(route('activation.form'));
        $this->assertDatabaseCount('users', 1);

        // Second attempt times out
        $payload['phone'] = '082222222222';
        $this->post(route('register.store'), $payload)->assertRedirect(route('activation.form'));
        $this->assertDatabaseCount('users', 1);
    }

    public function test_16_resend_rate_limit_remains_enforced(): void
    {
        $nik = '3573010101190016';
        $payload = $this->validPayload($nik, '081111111111');
        $this->post(route('register.store'), $payload);

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();

        // Request 3 resends to hit limit
        for ($i = 0; $i < 3; $i++) {
            $this->withSession(['activation_user_id' => $user->id])
                ->post(route('activation.resend'));
        }

        // 4th resend must be throttled
        $response = $this->withSession(['activation_user_id' => $user->id])
            ->post(route('activation.resend'));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Mohon tunggu', session('error'));
    }

    public function test_17_maximum_five_verification_attempts_remains_enforced(): void
    {
        $nik = '3573010101190017';
        $payload = $this->validPayload($nik, '081111111111');
        $this->post(route('register.store'), $payload);

        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();

        // 5 wrong attempts
        for ($i = 0; $i < 5; $i++) {
            $this->withSession(['activation_user_id' => $user->id])
                ->post(route('activation.verify'), ['otp' => '000000']);
        }

        $latestToken = UserOtpToken::where('user_id', $user->id)->latest('id')->first();
        $this->assertTrue($latestToken->isExhausted());
    }

    public function test_18_cross_purpose_otp_is_rejected(): void
    {
        $nik = '3573010101190018';
        $payload = $this->validPayload($nik, '081111111111');
        $this->post(route('register.store'), $payload);

        $otp = ArrayOtpDelivery::latestOtpFor('081111111111');
        $user = User::where('nik_lookup_hash', NikManager::hash($nik))->first();

        $otpService = new OtpService(new OtpDeliveryService(new ArrayOtpDelivery));
        $verifyCross = $otpService->verify($user, OtpPurpose::PASSWORD_RESET, $otp);
        $this->assertFalse($verifyCross['success']);
    }

    public function test_19_nik_remains_strictly_unique_in_database(): void
    {
        $nik = '3573010101190019';
        $hash = NikManager::hash($nik);

        User::factory()->create([
            'nik_lookup_hash' => $hash,
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        // Attempt direct database insert with same hash must violate unique constraint
        User::factory()->create([
            'nik_lookup_hash' => $hash,
            'account_status' => AccountStatus::PENDING_ACTIVATION,
        ]);
    }

    public function test_20_concurrency_protection_prevents_duplicate_accounts(): void
    {
        $nik = '3573010101190020';
        $payload = $this->validPayload($nik, '081111111111');

        // Simulate two sequential executions within separate transactions
        DB::transaction(function () use ($payload) {
            $this->post(route('register.store'), $payload);
        });

        DB::transaction(function () use ($payload) {
            $payload['phone'] = '082222222222';
            $this->post(route('register.store'), $payload);
        });

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('applicants', 1);
    }

    private function validPayload(string $nik, string $phone): array
    {
        return [
            'nik' => $nik,
            'full_name' => 'Fulan bin Fulan',
            'gender' => 'L',
            'birth_place' => 'Kota Madiun',
            'birth_date' => '2019-03-20',
            'phone' => $phone,
            'consent' => '1',
        ];
    }
}
