<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of roles with assigned permissions and user counts.
     */
    public function index(Request $request)
    {
        if (! Auth::user()->can('roles.view')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat role.');
        }
        $roles = Role::with('permissions:id,name')->withCount('users')->get();
        $permissions = Permission::select(['id', 'name', 'guard_name'])->get();

        // Group permissions by prefix for easy categorization in UI
        $groupedPermissions = $permissions->groupBy(function ($item) {
            $prefix = explode('.', $item->name)[0] ?? 'other';
            return match ($prefix) {
                'users'       => 'User Management',
                'roles'       => 'Role Management',
                'permissions' => 'Permission Management',
                'profile'     => 'Profile & Settings',
                default       => 'General',
            };
        });

        return view('roles.index', compact('roles', 'permissions', 'groupedPermissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        if (! Auth::user()->can('roles.create')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat role baru.');
        }

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:50', 'unique:roles,name'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique'   => 'Role dengan nama ini sudah ada.',
        ]);

        $role = Role::create([
            'name'       => strtolower(trim($validated['name'])),
            'guard_name' => 'web',
        ]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Role '{$role->name}' berhasil dibuat.",
                'role'    => $role->load('permissions'),
            ]);
        }

        return redirect()->route('roles.index')->with('success', "Role '{$role->name}' berhasil dibuat.");
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        if (! Auth::user()->can('roles.edit')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengedit role.');
        }

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique'   => 'Role dengan nama ini sudah ada.',
        ]);

        // Prevent renaming protected system roles
        $oldName = $role->name;
        if (! in_array($oldName, ['admin', 'user'])) {
            $role->name = strtolower(trim($validated['name']));
            $role->save();
        }

        // Sync permissions
        $permissions = $validated['permissions'] ?? [];
        $role->syncPermissions($permissions);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Role '{$role->name}' berhasil diperbarui.",
                'role'    => $role->fresh()->load('permissions'),
            ]);
        }

        return redirect()->route('roles.index')->with('success', "Role '{$role->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Request $request, Role $role)
    {
        if (! Auth::user()->can('roles.delete')) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus role.');
        }

        // Prevent deleting core system roles
        if (in_array($role->name, ['admin', 'user'])) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Role sistem '{$role->name}' tidak boleh dihapus.",
                ], 422);
            }

            return redirect()->route('roles.index')->with('error', "Role sistem '{$role->name}' tidak boleh dihapus.");
        }

        $roleName = $role->name;
        $role->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Role '{$roleName}' berhasil dihapus.",
            ]);
        }

        return redirect()->route('roles.index')->with('success', "Role '{$roleName}' berhasil dihapus.");
    }

    /**
     * Give a specific permission to a role.
     */
    public function givePermission(Request $request, Role $role)
    {
        if (! Auth::user()->can('roles.give-permission')) {
            abort(403, 'Anda tidak memiliki hak akses untuk memberikan permission.');
        }

        $validated = $request->validate([
            'permission' => ['required', 'string', 'exists:permissions,name'],
        ]);

        $role->givePermissionTo($validated['permission']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Permission '{$validated['permission']}' berhasil diberikan ke role '{$role->name}'.",
                'role'    => $role->fresh()->load('permissions'),
            ]);
        }

        return redirect()->route('roles.index')->with('success', "Permission berhasil diberikan.");
    }

    /**
     * Remove a specific permission from a role.
     */
    public function removePermission(Request $request, Role $role)
    {
        if (! Auth::user()->can('roles.remove-permission')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencabut permission.');
        }

        $validated = $request->validate([
            'permission' => ['required', 'string', 'exists:permissions,name'],
        ]);

        // Prevent revoking all permissions from admin role
        if ($role->name === 'admin' && $validated['permission'] === 'roles.view') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Permission inti tidak dapat dicabut dari role Admin.',
            ], 422);
        }

        $role->revokePermissionTo($validated['permission']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Permission '{$validated['permission']}' berhasil dicabut dari role '{$role->name}'.",
                'role'    => $role->fresh()->load('permissions'),
            ]);
        }

        return redirect()->route('roles.index')->with('success', "Permission berhasil dicabut.");
    }
}
