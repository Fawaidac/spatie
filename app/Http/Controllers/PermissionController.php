<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index(Request $request)
    {
        if (! Auth::user()->can('permissions.view')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat permission.');
        }
        $query = Permission::with('roles:id,name')
            ->select(['id', 'name', 'guard_name', 'created_at'])
            ->latest('id');

        // Search filter
        if ($search = trim((string) $request->input('search'))) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Group filter
        if ($group = $request->input('group')) {
            if ($group !== 'all') {
                $query->where('name', 'like', "{$group}.%");
            }
        }

        // Paginate 10 per page
        $permissions = $query->paginate(10)->withQueryString();

        // Optimized groups extraction without loading full models
        $groups = Permission::toBase()
            ->select('name')
            ->pluck('name')
            ->map(fn($n) => explode('.', $n)[0] ?? 'general')
            ->unique()
            ->values();

        $stats = [
            'total'         => Permission::count(),
            'groups_count'  => $groups->count(),
            'default_guard' => config('auth.defaults.guard', 'web'),
        ];

        $roles = Role::select(['id', 'name'])->get();

        return view('permissions.index', compact('permissions', 'stats', 'roles', 'groups'));
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request)
    {
        if (! Auth::user()->can('permissions.create')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat permission.');
        }

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100', 'unique:permissions,name'],
            'guard_name' => ['nullable', 'string', 'max:50'],
            'roles'      => ['nullable', 'array'],
            'roles.*'    => ['string', 'exists:roles,name'],
        ], [
            'name.required' => 'Nama permission wajib diisi.',
            'name.unique'   => 'Permission dengan nama ini sudah ada.',
        ]);

        $permission = Permission::create([
            'name'       => strtolower(trim($validated['name'])),
            'guard_name' => $validated['guard_name'] ?: 'web',
        ]);

        if (! empty($validated['roles'])) {
            $permission->syncRoles($validated['roles']);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'     => 'success',
                'message'    => "Permission '{$permission->name}' berhasil dibuat.",
                'permission' => $permission->load('roles'),
            ]);
        }

        return redirect()->route('permissions.index')->with('success', "Permission '{$permission->name}' berhasil dibuat.");
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        if (! Auth::user()->can('permissions.edit')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengedit permission.');
        }

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100', Rule::unique('permissions', 'name')->ignore($permission->id)],
            'guard_name' => ['nullable', 'string', 'max:50'],
            'roles'      => ['nullable', 'array'],
            'roles.*'    => ['string', 'exists:roles,name'],
        ], [
            'name.required' => 'Nama permission wajib diisi.',
            'name.unique'   => 'Permission dengan nama ini sudah ada.',
        ]);

        $permission->update([
            'name'       => strtolower(trim($validated['name'])),
            'guard_name' => $validated['guard_name'] ?: $permission->guard_name,
        ]);

        if (isset($validated['roles'])) {
            $permission->syncRoles($validated['roles']);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'     => 'success',
                'message'    => "Permission '{$permission->name}' berhasil diperbarui.",
                'permission' => $permission->fresh()->load('roles'),
            ]);
        }

        return redirect()->route('permissions.index')->with('success', "Permission '{$permission->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Request $request, Permission $permission)
    {
        if (! Auth::user()->can('permissions.delete')) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus permission.');
        }

        $permissionName = $permission->name;
        $permission->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Permission '{$permissionName}' berhasil dihapus.",
            ]);
        }

        return redirect()->route('permissions.index')->with('success', "Permission '{$permissionName}' berhasil dihapus.");
    }
}
