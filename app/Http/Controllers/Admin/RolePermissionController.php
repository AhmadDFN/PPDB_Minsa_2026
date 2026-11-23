<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\AdminConfigurationAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class RolePermissionController
{
    private const ADMIN_ALLOWLIST = ['view_dashboard', 'view_applicants', 'export_reports', 'verify_documents', 'reopen_registration', 'manage_admission_periods', 'manage_programs', 'manage_document_requirements'];

    public function __construct(private readonly AdminConfigurationAuditService $audit) {}

    public function index(): View
    {
        return view('admin.roles.index', ['roles' => Role::query()->whereIn('name', ['superadmin', 'admin_ppdb'])->where('guard_name', 'web')->withCount(['users', 'permissions'])->get()]);
    }

    public function edit(Role $role): View
    {
        $this->ensureRole($role);
        abort_if($role->name === 'superadmin', 403, 'Role superadmin dilindungi.');

        return view('admin.roles.permissions', ['role' => $role->load('permissions'), 'permissions' => Permission::query()->where('guard_name', 'web')->whereIn('name', self::ADMIN_ALLOWLIST)->orderBy('name')->get()]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->ensureRole($role);
        abort_if($role->name === 'superadmin', 403, 'Role superadmin dilindungi.');
        $data = $request->validate(['permissions' => ['nullable', 'array'], 'permissions.*' => ['integer', 'exists:permissions,id']]);
        $permissions = Permission::query()->where('guard_name', 'web')->whereIn('name', self::ADMIN_ALLOWLIST)->whereIn('id', $data['permissions'] ?? [])->get();
        if ($permissions->count() !== count($data['permissions'] ?? [])) {
            return back()->withInput()->withErrors(['permissions' => 'Terdapat permission yang tidak diizinkan.']);
        }
        $before = $role->getPermissionNames()->values()->all();
        try {
            DB::transaction(function () use ($role, $permissions, $request, $before): void {
                $role->syncPermissions($permissions);
                app(PermissionRegistrar::class)->forgetCachedPermissions();
                $this->audit->record('role', $role->id, 'permissions_updated', $request->user(), ['permissions' => $before], ['permissions' => $permissions->pluck('name')->values()->all()]);
            });
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Permission gagal diperbarui.');
        }

        return redirect()->route('admin.roles.index')->with('success', 'Permission role diperbarui.');
    }

    private function ensureRole(Role $role): void
    {
        abort_unless(in_array($role->name, ['superadmin', 'admin_ppdb'], true) && $role->guard_name === 'web', 404);
    }
}
