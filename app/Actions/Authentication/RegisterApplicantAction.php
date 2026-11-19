<?php

declare(strict_types=1);

namespace App\Actions\Authentication;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\Gender;
use App\Enums\OtpPurpose;
use App\Models\Applicant;
use App\Models\User;
use App\Services\Authentication\OtpService;
use App\Support\NikManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class RegisterApplicantAction
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Atomically register a new applicant user or recover a pending registration.
     *
     * @param  array{nik: string, full_name: string, gender: string|Gender, birth_place: string, birth_date: string, phone: string}  $data
     * @return array{user: User, applicant: Applicant, plain_otp: string, is_recovery: bool, otp_dispatched: bool}
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

                // Rule B.3: Verify identity fields match the initial registration
                if (! $this->verifyIdentityMatches($applicant, $data)) {
                    throw new InvalidArgumentException('Data identitas (nama, jenis kelamin, atau tanggal/tempat lahir) tidak sesuai dengan data awal yang didaftarkan untuk NIK ini.');
                }

                // Rule B.7-8: Update phone number and ensure phone_verified_at remains null
                $existingUser->update([
                    'name' => trim($data['full_name']),
                    'phone' => $normalizedPhone,
                    'phone_verified_at' => null,
                ]);

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
            ]);

            $gender = $data['gender'] instanceof Gender ? $data['gender']->value : $data['gender'];

            /** @var Applicant $applicant */
            $applicant = Applicant::create([
                'user_id' => $user->id,
                'full_name' => trim($data['full_name']),
                'gender' => $gender,
                'birth_place' => trim($data['birth_place']),
                'birth_date' => $data['birth_date'],
            ]);

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
            ];
        });
    }

    /**
     * Verify if re-submitted identity fields match the initial registration for this NIK.
     *
     * @param  array{full_name: string, gender: string|Gender, birth_place: string, birth_date: string}  $data
     */
    protected function verifyIdentityMatches(Applicant $applicant, array $data): bool
    {
        // 1. Compare Full Name (case-insensitive, trimmed, collapsed whitespace)
        $storedName = mb_strtoupper(preg_replace('/\s+/', ' ', trim($applicant->full_name)) ?? '');
        $inputName = mb_strtoupper(preg_replace('/\s+/', ' ', trim($data['full_name'])) ?? '');

        if ($storedName !== $inputName) {
            return false;
        }

        // 2. Compare Gender
        $inputGender = $data['gender'] instanceof Gender ? $data['gender']->value : (string) $data['gender'];
        $storedGender = $applicant->gender instanceof Gender ? $applicant->gender->value : (string) $applicant->gender;

        if ($storedGender !== $inputGender) {
            return false;
        }

        // 3. Compare Birth Place (case-insensitive, trimmed)
        $storedPlace = mb_strtoupper(trim($applicant->birth_place));
        $inputPlace = mb_strtoupper(trim($data['birth_place']));

        if ($storedPlace !== $inputPlace) {
            return false;
        }

        // 4. Compare Birth Date (normalized to Y-m-d)
        $storedDate = $applicant->birth_date instanceof \DateTimeInterface
            ? $applicant->birth_date->format('Y-m-d')
            : Carbon::parse((string) $applicant->birth_date)->format('Y-m-d');

        $inputDate = Carbon::parse((string) $data['birth_date'])->format('Y-m-d');

        return $storedDate === $inputDate;
    }
}
