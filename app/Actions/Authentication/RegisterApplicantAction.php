<?php

declare(strict_types=1);

namespace App\Actions\Authentication;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AdmissionPeriodStatus;
use App\Enums\Gender;
use App\Enums\OtpPurpose;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\Applicant;
use App\Models\Registration;
use App\Models\User;
use App\Services\Authentication\OtpService;
use App\Services\Registration\RegistrationNumberGeneratorService;
use App\Support\NikManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class RegisterApplicantAction
{
    public function __construct(
        protected OtpService $otpService,
        protected RegistrationNumberGeneratorService $numberGenerator
    ) {}

    /**
     * Atomically register a new applicant user or recover a pending registration.
     *
     * @param  array{nik: string, full_name: string, phone: string, consent?: mixed, gender?: string|Gender|null, birth_place?: string|null, birth_date?: string|null}  $data
     * @return array{user: User, applicant: Applicant, plain_otp: string, is_recovery: bool, otp_dispatched: bool, registration_number: ?string}
     *
     * @throws InvalidArgumentException
     */
    public function execute(array $data): array
    {
        $normalizedNik = NikManager::normalize($data['nik']);
        $nikHash = NikManager::hash($normalizedNik);
        $encryptedNik = NikManager::encrypt($normalizedNik);
        $normalizedPhone = preg_replace('/\D/', '', $data['phone']) ?? '';

        return DB::transaction(function () use ($data, $nikHash, $encryptedNik, $normalizedPhone) {
            /** @var User|null $existingUser */
            $existingUser = User::where('nik_lookup_hash', $nikHash)
                ->lockForUpdate()
                ->first();

            $activePeriod = AdmissionPeriod::query()
                ->where('status', AdmissionPeriodStatus::ACTIVE)
                ->where('is_active', true)
                ->first();

            if ($existingUser) {
                // Rule C: Active account is strictly rejected
                if ($existingUser->account_status === AccountStatus::ACTIVE) {
                    throw new InvalidArgumentException('NIK ini sudah memiliki akun aktif. Silakan masuk menggunakan NIK dan kata sandi yang telah dibuat.');
                }

                // Rule D: Status other than PENDING_ACTIVATION (e.g. SUSPENDED) cannot be recovered
                if ($existingUser->account_status !== AccountStatus::PENDING_ACTIVATION) {
                    throw new InvalidArgumentException('Akun dengan NIK ini berstatus ditangguhkan atau tidak dapat dipulihkan. Silakan hubungi panitia PPDB MIN 1 Kota Madiun.');
                }

                /** @var Applicant|null $applicant */
                $applicant = $existingUser->applicant()
                    ->lockForUpdate()
                    ->first();

                if (! $applicant) {
                    throw new InvalidArgumentException('Profil pendaftar untuk NIK ini tidak ditemukan. Silakan hubungi panitia PPDB.');
                }

                // Rule B.3: Verify identity matches initial registration
                if (! $this->verifyIdentityMatches($applicant, $data)) {
                    throw new InvalidArgumentException('Data nama lengkap tidak sesuai dengan data awal yang didaftarkan untuk NIK ini.');
                }

                // Rule B.7-8: Update phone number and ensure phone_verified_at remains null
                $existingUser->update([
                    'name' => trim($data['full_name']),
                    'phone' => $normalizedPhone,
                    'phone_verified_at' => null,
                ]);

                // Ensure registration number is preserved and exists
                $registration = null;
                $regNumber = null;
                if ($activePeriod) {
                    $registration = Registration::query()
                        ->where('applicant_id', $applicant->id)
                        ->where('admission_period_id', $activePeriod->id)
                        ->first();

                    if (! $registration) {
                        $regNumber = $this->numberGenerator->generateForPeriod($activePeriod);
                        $registration = Registration::create([
                            'applicant_id' => $applicant->id,
                            'admission_period_id' => $activePeriod->id,
                            'registration_number' => $regNumber,
                            'status' => RegistrationStatus::DRAFT,
                            'data_revision' => 1,
                        ]);
                    } else {
                        $regNumber = $registration->registration_number;
                    }

                    // Record or update initial consent
                    $registration->consent()->updateOrCreate(
                        ['registration_id' => $registration->id],
                        [
                            'statement_text' => config('ppdb.registration.consent_statement', 'Saya selaku orang tua/wali menyatakan bahwa seluruh data yang diisikan adalah benar dan dapat dipertanggungjawabkan.'),
                            'consent_version' => config('ppdb.registration.consent_version', 'v1.0'),
                            'accepted_by_name' => trim($data['full_name']),
                            'is_agreed' => true,
                            'agreed_at' => now(),
                            'ip_address' => request()?->ip() ?? '127.0.0.1',
                            'user_agent' => request()?->userAgent() ?? 'Registration Form',
                        ]
                    );
                }

                // Invalidate previous unconsumed activation OTP tokens
                $this->otpService->invalidatePrevious($existingUser, OtpPurpose::ACTIVATION->value);

                // Generate and dispatch fresh activation OTP
                $otpDispatched = true;
                $plainOtp = '';

                try {
                    $otpData = $this->otpService->generateAndSend($existingUser, OtpPurpose::ACTIVATION, $normalizedPhone);
                    $plainOtp = $otpData['plain_otp'];
                } catch (RuntimeException) {
                    $otpDispatched = false;
                }

                return [
                    'user' => $existingUser,
                    'applicant' => $applicant,
                    'plain_otp' => $plainOtp,
                    'is_recovery' => true,
                    'otp_dispatched' => $otpDispatched,
                    'registration_number' => $regNumber,
                ];
            }

            // Rule A: New Registration
            /** @var User $user */
            $user = User::create([
                'name' => trim($data['full_name']),
                'nik_lookup_hash' => $nikHash,
                'nik_encrypted' => $encryptedNik,
                'phone' => $normalizedPhone,
                'account_type' => AccountType::APPLICANT,
                'account_status' => AccountStatus::PENDING_ACTIVATION,
                'password' => Hash::make(Str::random(40)),
                'must_change_password' => false,
            ]);

            $gender = isset($data['gender'])
                ? ($data['gender'] instanceof Gender ? $data['gender']->value : $data['gender'])
                : null;

            /** @var Applicant $applicant */
            $applicant = Applicant::create([
                'user_id' => $user->id,
                'full_name' => trim($data['full_name']),
                'gender' => $gender,
                'birth_place' => isset($data['birth_place']) ? trim((string) $data['birth_place']) : null,
                'birth_date' => $data['birth_date'] ?? null,
            ]);

            $regNumber = null;
            if ($activePeriod) {
                $regNumber = $this->numberGenerator->generateForPeriod($activePeriod);
                $registration = Registration::create([
                    'applicant_id' => $applicant->id,
                    'admission_period_id' => $activePeriod->id,
                    'registration_number' => $regNumber,
                    'status' => RegistrationStatus::DRAFT,
                    'data_revision' => 1,
                ]);

                // Record initial consent
                $registration->consent()->create([
                    'statement_text' => config('ppdb.registration.consent_statement', 'Saya selaku orang tua/wali menyatakan bahwa seluruh data yang diisikan adalah benar dan dapat dipertanggungjawabkan.'),
                    'consent_version' => config('ppdb.registration.consent_version', 'v1.0'),
                    'accepted_by_name' => trim($data['full_name']),
                    'is_agreed' => true,
                    'agreed_at' => now(),
                    'ip_address' => request()?->ip() ?? '127.0.0.1',
                    'user_agent' => request()?->userAgent() ?? 'Registration Form',
                ]);
            }

            $otpDispatched = true;
            $plainOtp = '';

            try {
                $otpData = $this->otpService->generateAndSend($user, OtpPurpose::ACTIVATION, $normalizedPhone);
                $plainOtp = $otpData['plain_otp'];
            } catch (RuntimeException) {
                $otpDispatched = false;
            }

            return [
                'user' => $user,
                'applicant' => $applicant,
                'plain_otp' => $plainOtp,
                'is_recovery' => false,
                'otp_dispatched' => $otpDispatched,
                'registration_number' => $regNumber,
            ];
        });
    }

    /**
     * Check if submitted identity matches the applicant record on pending recovery.
     *
     * @param  array<string, mixed>  $data
     */
    private function verifyIdentityMatches(Applicant $applicant, array $data): bool
    {
        $normalize = fn (string $value): string => mb_strtoupper(preg_replace('/\s+/', ' ', trim($value)));

        $submittedName = $normalize((string) $data['full_name']);
        $existingName = $normalize((string) $applicant->full_name);

        if ($submittedName !== $existingName) {
            return false;
        }

        // If gender was optionally provided and existing has gender
        if (! empty($data['gender']) && ! empty($applicant->gender)) {
            $submittedGender = is_object($data['gender']) ? $data['gender']->value : (string) $data['gender'];
            $existingGender = is_object($applicant->gender) ? $applicant->gender->value : (string) $applicant->gender;
            if ($submittedGender !== $existingGender) {
                return false;
            }
        }

        // If birth place was optionally provided and existing has birth place
        if (! empty($data['birth_place']) && ! empty($applicant->birth_place)) {
            if ($normalize((string) $data['birth_place']) !== $normalize((string) $applicant->birth_place)) {
                return false;
            }
        }

        // If birth date was optionally provided and existing has birth date
        if (! empty($data['birth_date']) && ! empty($applicant->birth_date)) {
            $submittedBirthDate = $data['birth_date'] instanceof \DateTimeInterface
                ? $data['birth_date']->format('Y-m-d')
                : (string) $data['birth_date'];

            $existingBirthDate = $applicant->birth_date instanceof \DateTimeInterface
                ? $applicant->birth_date->format('Y-m-d')
                : (string) $applicant->birth_date;

            if ($submittedBirthDate !== $existingBirthDate) {
                return false;
            }
        }

        return true;
    }
}
