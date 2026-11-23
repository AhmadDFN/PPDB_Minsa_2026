<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use App\Notifications\AdminAccountPasswordNotification;
use App\Services\AdminConfigurationAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Throwable;

class AdminUserController
{
    public function __construct(private readonly AdminConfigurationAuditService $audit) {}

    public function index(Request $request): View
    {
        $users = User::query()->where('account_type', AccountType::ADMIN)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['superadmin', 'admin_ppdb']))
            ->with('roles')->when($request->filled('q'), fn ($q) => $q->where(fn ($x) => $x->where('name', 'like', '%'.$request->string('q').'%')->orWhere('email', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('account_status', $request->string('status')))
            ->when($request->filled('role'), fn ($q) => $q->whereHas('roles', fn ($x) => $x->where('name', $request->string('role'))))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.admin-users.index', ['users' => $users, 'statuses' => AccountStatus::cases()]);
    }

    public function create(): View
    {
        return view('admin.admin-users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'phone' => ['nullable', 'string', 'max:30']]);
        try {
            DB::transaction(function () use ($data, $request, &$user): void {
                $user = User::query()->create($data + ['account_type' => AccountType::ADMIN, 'account_status' => AccountStatus::PENDING_ACTIVATION, 'password' => Str::random(64), 'must_change_password' => true]);
                $user->assignRole('admin_ppdb');
                $this->audit->record('admin_user', $user->id, 'created', $request->user(), null, $this->snapshot($user));
            });
        } catch (Throwable $e) {
            Log::error('Admin account provisioning transaction failed.', [
                'exception_class' => $e::class,
                'exception_code' => (string) $e->getCode(),
            ]);

            return back()->withInput()->with('error', 'Akun admin gagal dibuat.');
        }

        $delivery = $this->sendInvitation($user, $request->user());

        return redirect()->route('admin.admin-users.index')->with($delivery['ok'] ? 'success' : 'error', $delivery['message']);
    }

    public function edit(User $user): View
    {
        $this->ensureAdminTarget($user);

        return view('admin.admin-users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdminTarget($user);
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)], 'phone' => ['nullable', 'string', 'max:30']]);
        $before = $this->snapshot($user);
        try {
            DB::transaction(function () use ($user, $data, $request, $before): void {
                $user->update($data);
                $this->audit->record('admin_user', $user->id, 'updated', $request->user(), $before, $this->snapshot($user->fresh()));
            });
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Akun admin gagal diperbarui.');
        }

        return redirect()->route('admin.admin-users.index')->with('success', 'Profil admin diperbarui.');
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdminTarget($user);
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }
        try {
            DB::transaction(function () use ($user, $request): void {
                Role::query()->where('name', 'superadmin')->where('guard_name', 'web')->lockForUpdate()->firstOrFail();
                $target = User::query()->lockForUpdate()->findOrFail($user->id);
                if ($target->hasRole('superadmin') && $target->account_status === AccountStatus::ACTIVE && User::query()->where('account_type', AccountType::ADMIN)->where('account_status', AccountStatus::ACTIVE)->whereHas('roles', fn ($q) => $q->where('name', 'superadmin'))->count() <= 1) {
                    throw new \DomainException('Superadmin aktif terakhir tidak dapat dinonaktifkan.');
                }
                $before = $this->snapshot($target);
                $target->update(['account_status' => $target->account_status === AccountStatus::ACTIVE ? AccountStatus::SUSPENDED : AccountStatus::ACTIVE]);
                DB::table('sessions')->where('user_id', $target->id)->delete();
                $this->audit->record('admin_user', $target->id, 'status_changed', $request->user(), $before, $this->snapshot($target->fresh()));
            });
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', $e instanceof \DomainException ? $e->getMessage() : 'Status akun gagal diperbarui.');
        }

        return back()->with('success', 'Status akun admin diperbarui.');
    }

    public function passwordReset(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdminTarget($user);
        try {
            $status = Password::broker()->sendResetLink(['email' => $user->email], function (User $notifiable, string $token) use ($request): string {
                $notifiable->notify(new AdminAccountPasswordNotification($token, 'reset'));
                $this->audit->record('admin_user', $notifiable->id, 'password_reset_requested', $request->user(), null, ['email' => $notifiable->email]);

                return Password::RESET_LINK_SENT;
            });
        } catch (Throwable $e) {
            report($e);
            try {
                $this->audit->record('admin_user', $user->id, 'password_reset_notification_dispatch_failed', $request->user(), null, ['email' => $user->email]);
            } catch (Throwable $auditException) {
                report($auditException);
            }

            return back()->with('error', 'Reset password belum dapat dikirim. Akun tetap tidak berubah.');
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()->with('error', 'Permintaan reset password terlalu sering. Silakan coba lagi setelah jeda keamanan berakhir.');
        }

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->with('error', 'Reset password belum dapat diproses.');
        }

        return back()->with('success', 'Permintaan reset password diterima dan diteruskan ke kanal email yang dikonfigurasi.');
    }

    public function resendInvitation(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdminTarget($user);
        if ($user->account_status !== AccountStatus::PENDING_ACTIVATION) {
            return back()->with('error', 'Undangan hanya tersedia untuk akun yang belum diaktifkan.');
        }
        $key = 'admin-invitation:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            return back()->with('error', 'Undangan baru dapat dikirim ulang setelah satu menit.');
        }
        RateLimiter::hit($key, 60);
        $delivery = $this->sendInvitation($user, $request->user());

        return back()->with($delivery['ok'] ? 'success' : 'error', $delivery['message']);
    }

    private function sendInvitation(User $user, User $actor): array
    {
        try {
            $token = Password::broker()->createToken($user);
            $user->notify(new AdminAccountPasswordNotification($token));
            $this->audit->record('admin_user', $user->id, 'invitation_requested', $actor, null, ['email' => $user->email]);

            return ['ok' => true, 'message' => 'Undangan aktivasi diteruskan ke kanal email yang dikonfigurasi.'];
        } catch (Throwable $e) {
            report($e);
            try {
                $this->audit->record('admin_user', $user->id, 'invitation_notification_dispatch_failed', $actor, null, ['email' => $user->email]);
            } catch (Throwable $auditException) {
                report($auditException);
            }

            return ['ok' => false, 'message' => 'Undangan belum dapat dikirim. Akun tetap menunggu aktivasi.'];
        }
    }

    private function ensureAdminTarget(User $user): void
    {
        abort_unless($user->account_type === AccountType::ADMIN && $user->hasAnyRole(['superadmin', 'admin_ppdb']), 404);
    }

    private function snapshot(User $user): array
    {
        return ['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone, 'account_type' => $user->account_type?->value, 'account_status' => $user->account_status?->value, 'roles' => $user->getRoleNames()->values()->all()];
    }
}
