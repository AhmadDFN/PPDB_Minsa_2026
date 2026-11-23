<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use App\Services\AdminConfigurationAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminPasswordController
{
    public function __construct(private readonly AdminConfigurationAuditService $audit) {}

    public function show(Request $request, string $token): View|RedirectResponse
    {
        $email = (string) $request->query('email');
        $user = User::query()->where('email', $email)->where('account_type', AccountType::ADMIN)->first();
        if (! $user || ! Password::broker()->tokenExists($user, $token)) {
            return redirect()->route('admin.login')->with('error', 'Tautan password tidak valid atau sudah kedaluwarsa.');
        }

        return view('auth.admin-password-reset', compact('token', 'email', 'user'));
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate(['token' => ['required', 'string'], 'email' => ['required', 'email'], 'password' => ['required', 'string', 'min:8', 'confirmed']]);
        $status = Password::broker()->reset($data, function (User $user, string $password): void {
            if ($user->account_type !== AccountType::ADMIN || ! $user->hasAnyRole(['superadmin', 'admin_ppdb'])) {
                throw ValidationException::withMessages(['email' => 'Tautan password tidak valid.']);
            }
            $before = ['account_status' => $user->account_status?->value, 'must_change_password' => $user->must_change_password];
            DB::transaction(function () use ($user, $password, $before): void {
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60), 'must_change_password' => false, 'password_changed_at' => now(), 'account_status' => $user->account_status === AccountStatus::PENDING_ACTIVATION ? AccountStatus::ACTIVE : $user->account_status])->save();
                DB::table('sessions')->where('user_id', $user->id)->delete();
                $this->audit->record('admin_user', $user->id, 'password_reset_completed', $user, $before, ['account_status' => $user->fresh()->account_status?->value, 'must_change_password' => false]);
            });
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('admin.login')->with('success', 'Password berhasil diperbarui. Silakan masuk.');
        }

        return back()->withErrors(['email' => 'Tautan password tidak valid, sudah digunakan, atau sudah kedaluwarsa.']);
    }
}
