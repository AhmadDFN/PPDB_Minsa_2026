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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RegisterApplicantAction
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Atomically register a new applicant user, profile, and issue activation OTP.
     *
     * @param  array{nik: string, full_name: string, gender: string|Gender, birth_place: string, birth_date: string, phone: string}  $data
     * @return array{user: User, applicant: Applicant, plain_otp: string}
     *
     * @throws InvalidArgumentException
     */
    public function execute(array $data): array
    {
        $normalizedNik = NikManager::normalize($data['nik']);
        $nikHash = NikManager::hash($normalizedNik);

        // Enforce strict uniqueness: one NIK can only have one account in the entire system
        if (User::where('nik_lookup_hash', $nikHash)->exists()) {
            throw new InvalidArgumentException('NIK sudah terdaftar dalam sistem PPDB MIN 1 Kota Madiun.');
        }

        $encryptedNik = NikManager::encrypt($normalizedNik);
        $normalizedPhone = preg_replace('/\D/', '', $data['phone']);

        return DB::transaction(function () use ($data, $nikHash, $encryptedNik, $normalizedPhone) {
            // 1. Create User in PENDING_ACTIVATION status
            /** @var User $user */
            $user = User::create([
                'name' => trim($data['full_name']),
                'nik_lookup_hash' => $nikHash,
                'nik_encrypted' => $encryptedNik,
                'phone' => $normalizedPhone,
                'account_type' => AccountType::APPLICANT,
                'account_status' => AccountStatus::PENDING_ACTIVATION,
                'password' => Hash::make(Str::random(40)), // Unusable random hash until password creation
            ]);

            // 2. Create linked Applicant profile satisfying existing database constraints
            $gender = $data['gender'] instanceof Gender ? $data['gender']->value : $data['gender'];

            /** @var Applicant $applicant */
            $applicant = Applicant::create([
                'user_id' => $user->id,
                'full_name' => trim($data['full_name']),
                'gender' => $gender,
                'birth_place' => trim($data['birth_place']),
                'birth_date' => $data['birth_date'],
            ]);

            // 3. Generate and send cryptographically secure 6-digit OTP
            $otpData = $this->otpService->generateAndSend($user, OtpPurpose::ACTIVATION, $normalizedPhone);

            return [
                'user' => $user,
                'applicant' => $applicant,
                'plain_otp' => $otpData['plain_otp'],
            ];
        });
    }
}
