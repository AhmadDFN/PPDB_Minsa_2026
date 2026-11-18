<?php

declare(strict_types=1);

namespace App\Actions\Registration;

use App\Models\AdmissionPeriod;
use App\Models\Registration;
use App\Models\RegistrationLog;
use App\Models\RegistrationReopenWindow;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CloseRegistrationReopenAction
{
    public function execute(
        RegistrationReopenWindow $window,
        User $actor,
        string $reason
    ): RegistrationReopenWindow {
        if (! $actor->can('reopen_registration')) {
            throw new HttpException(403);
        }

        $reason = trim($reason);

        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'Alasan penutupan akses wajib diisi.',
            ]);
        }

        return DB::transaction(function () use ($window, $actor, $reason): RegistrationReopenWindow {
            $period = AdmissionPeriod::query()
                ->whereKey($window->registration->admission_period_id)
                ->lockForUpdate()
                ->firstOrFail();

            $registration = Registration::query()
                ->whereKey($window->registration_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedWindow = RegistrationReopenWindow::query()
                ->whereKey($window->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedWindow->closed_at !== null || $lockedWindow->expires_at->lessThanOrEqualTo(now())) {
                throw ValidationException::withMessages([
                    'reopen_window' => 'Akses edit ulang sudah ditutup atau kedaluwarsa.',
                ]);
            }

            $closedAt = now();
            $lockedWindow->update([
                'closed_by' => $actor->id,
                'closed_at' => $closedAt,
                'close_reason' => $reason,
            ]);

            RegistrationLog::query()->create([
                'registration_id' => $registration->id,
                'user_id' => $actor->id,
                'action' => 'reopen_window_closed',
                'from_revision' => $registration->data_revision,
                'to_revision' => $registration->data_revision,
                'changes' => [
                    'reopen_window_id' => $lockedWindow->id,
                    'close_reason' => $reason,
                    'closed_at' => $closedAt->toISOString(),
                ],
                'created_at' => $closedAt,
            ]);

            return $lockedWindow->refresh();
        });
    }
}
