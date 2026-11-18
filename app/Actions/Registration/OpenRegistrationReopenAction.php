<?php

declare(strict_types=1);

namespace App\Actions\Registration;

use App\Models\AdmissionPeriod;
use App\Models\Registration;
use App\Models\RegistrationLog;
use App\Models\RegistrationReopenWindow;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OpenRegistrationReopenAction
{
    public function execute(
        Registration $registration,
        User $actor,
        string $reason,
        string $expiresAt
    ): RegistrationReopenWindow {
        if (! $actor->can('reopen_registration')) {
            throw new HttpException(403);
        }

        $reason = trim($reason);
        $expiration = CarbonImmutable::parse($expiresAt);

        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'Alasan pembukaan akses wajib diisi.',
            ]);
        }

        if ($expiration->lessThanOrEqualTo(now())) {
            throw ValidationException::withMessages([
                'expires_at' => 'Waktu kedaluwarsa harus berada di masa depan.',
            ]);
        }

        return DB::transaction(function () use ($registration, $actor, $reason, $expiration): RegistrationReopenWindow {
            $period = AdmissionPeriod::query()
                ->whereKey($registration->admission_period_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedRegistration = Registration::query()
                ->whereKey($registration->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedRegistration->isSubmitted()) {
                throw ValidationException::withMessages([
                    'registration' => 'Akses edit ulang hanya dapat dibuka untuk pendaftaran yang telah dikirim.',
                ]);
            }

            if ($period->isOpen()) {
                throw ValidationException::withMessages([
                    'registration' => 'Akses edit ulang hanya dapat dibuka setelah periode pendaftaran ditutup.',
                ]);
            }

            $activeWindow = $lockedRegistration->reopenWindows()
                ->whereNull('closed_at')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->first();

            if ($activeWindow !== null) {
                throw ValidationException::withMessages([
                    'registration' => 'Pendaftaran ini masih memiliki akses edit ulang yang aktif.',
                ]);
            }

            $openedAt = now();
            $window = RegistrationReopenWindow::query()->create([
                'registration_id' => $lockedRegistration->id,
                'opened_by' => $actor->id,
                'reason' => $reason,
                'opened_at' => $openedAt,
                'expires_at' => $expiration,
            ]);

            RegistrationLog::query()->create([
                'registration_id' => $lockedRegistration->id,
                'user_id' => $actor->id,
                'action' => 'reopen_window_opened',
                'from_revision' => $lockedRegistration->data_revision,
                'to_revision' => $lockedRegistration->data_revision,
                'changes' => [
                    'reopen_window_id' => $window->id,
                    'reason' => $reason,
                    'opened_at' => $openedAt->toISOString(),
                    'expires_at' => $expiration->toISOString(),
                ],
                'created_at' => $openedAt,
            ]);

            return $window;
        });
    }
}
