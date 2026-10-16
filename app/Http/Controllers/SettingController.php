<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Display the settings and user profile view.
     */
    public function index()
    {
        if (! Auth::user()->can('profile.view')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat pengaturan profil.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load(['roles', 'permissions']);

        $roleNames = $user->getRoleNames();
        $permissions = $user->getAllPermissions();

        return view('settings.index', compact('user', 'roleNames', 'permissions'));
    }

    /**
     * Update the authenticated user's profile details.
     */
    public function updateProfile(Request $request)
    {
        if (! Auth::user()->can('profile.update')) {
            abort(403, 'Anda tidak memiliki hak akses untuk memperbarui profil.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'bio'   => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique'   => 'Alamat email sudah digunakan oleh akun lain.',
        ]);

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'bio'   => $validated['bio'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Profil berhasil diperbarui.',
                'user'    => $user->fresh(),
            ]);
        }

        return redirect()->route('settings.index')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        if (! Auth::user()->can('profile.update')) {
            abort(403, 'Anda tidak memiliki hak akses untuk memperbarui password.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'current_password'      => ['required', 'string'],
            'password'              => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[^A-Za-z0-9]/',
            ],
            'password_confirmation' => ['required', 'string'],
        ], [
            'current_password.required'      => 'Password saat ini wajib diisi.',
            'password.required'              => 'Password baru wajib diisi.',
            'password.min'                   => 'Password baru minimal 8 karakter.',
            'password.confirmed'             => 'Konfirmasi password baru tidak cocok.',
            'password.regex'                 => 'Password baru harus mengandung huruf kecil, huruf besar, dan simbol khusus.',
            'password_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Password saat ini yang Anda masukkan salah.',
                ], 422);
            }

            return back()->withErrors(['current_password' => 'Password saat ini tidak cocok.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Password berhasil diubah.',
            ]);
        }

        return redirect()->route('settings.index')->with('success', 'Password berhasil diubah.');
    }
}
