<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users with stats and filter options.
     */
    public function index(Request $request)
    {
        if (! Auth::user()->can('users.view')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat data pengguna.');
        }

        $query = User::with('roles:id,name')
            ->select(['id', 'name', 'email', 'status', 'avatar', 'phone', 'created_at'])
            ->latest('id');

        // Search filter (Case-insensitive where applicable)
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Role filter
        if ($role = $request->input('role')) {
            if ($role !== 'all') {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            }
        }

        // Paginate exactly 10 per page
        $users = $query->paginate(10)->withQueryString();

        // Optimized single-pass aggregated stats calculation
        $statsRaw = User::toBase()
            ->selectRaw('count(*) as total')
            ->selectRaw("count(case when status = 'active' then 1 end) as active")
            ->selectRaw("count(case when status = 'inactive' then 1 end) as inactive")
            ->selectRaw("count(case when status = 'banned' then 1 end) as banned")
            ->first();

        $stats = [
            'total'    => $statsRaw->total ?? 0,
            'active'   => $statsRaw->active ?? 0,
            'inactive' => $statsRaw->inactive ?? 0,
            'banned'   => $statsRaw->banned ?? 0,
        ];

        $roles = Role::select(['id', 'name'])->get();

        return view('users.index', compact('users', 'stats', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        if (! Auth::user()->can('users.create')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat user baru.');
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[^A-Za-z0-9]/',
            ],
            'role'     => ['required', 'string', 'exists:roles,name'],
            'status'   => ['required', 'string', 'in:active,inactive,banned'],
            'phone'    => ['nullable', 'string', 'max:25'],
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'password.regex'    => 'Password harus mengandung huruf kecil, huruf besar, dan simbol khusus.',
            'role.required'     => 'Role wajib dipilih.',
            'role.exists'       => 'Role yang dipilih tidak valid.',
            'status.required'   => 'Status wajib dipilih.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status'   => $validated['status'],
            'phone'    => $validated['phone'] ?? null,
        ]);

        $user->assignRole($validated['role']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'User berhasil ditambahkan.',
                'user'    => $user->load('roles'),
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        if (! Auth::user()->can('users.edit')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah data user.');
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[^A-Za-z0-9]/',
            ],
            'role'     => ['required', 'string', 'exists:roles,name'],
            'status'   => ['required', 'string', 'in:active,inactive,banned'],
            'phone'    => ['nullable', 'string', 'max:25'],
        ], [
            'name.required'   => 'Nama lengkap wajib diisi.',
            'email.required'  => 'Alamat email wajib diisi.',
            'email.unique'    => 'Email sudah digunakan oleh user lain.',
            'password.min'    => 'Password baru minimal 8 karakter.',
            'password.regex'  => 'Password baru harus mengandung huruf kecil, huruf besar, dan simbol khusus.',
            'role.required'   => 'Role wajib dipilih.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        $userData = [
            'name'   => $validated['name'],
            'email'  => $validated['email'],
            'status' => $validated['status'],
            'phone'  => $validated['phone'] ?? $user->phone,
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);
        $user->syncRoles([$validated['role']]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'User berhasil diperbarui.',
                'user'    => $user->fresh()->load('roles'),
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user)
    {
        if (! Auth::user()->can('users.delete')) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus user.');
        }

        // Prevent deleting self
        if (Auth::id() === $user->id) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
                ], 422);
            }

            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $userName = $user->name;
        $user->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => "User {$userName} berhasil dihapus.",
            ]);
        }

        return redirect()->route('users.index')->with('success', "User {$userName} berhasil dihapus.");
    }
}
