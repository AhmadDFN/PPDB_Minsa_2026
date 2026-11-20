<?php

declare(strict_types=1);

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Applicant\ChangePasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ApplicantPasswordChangeController extends Controller
{
    /**
     * Show mandatory password change form for applicant on temporary password.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('applicant.auth.change-password', [
            'user' => $user,
        ]);
    }

    /**
     * Update temporary password to a new permanent password chosen by the applicant.
     */
    public function update(ChangePasswordRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);

        return redirect()->route('applicant.dashboard')
            ->with('success', 'Kata sandi baru berhasil disimpan. Akun Anda kini sepenuhnya aman.');
    }
}
