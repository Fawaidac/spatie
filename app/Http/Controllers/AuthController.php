<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        protected JwtService $jwtService
    ) {}

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login form submission with JWT and Captcha verification.
     */
    public function login(Request $request)
    {
        // 1. Validate Form Input
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'captcha'  => ['required', 'string'],
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'captcha.required'  => 'Kode captcha wajib diisi.',
        ]);

        // 2. Validate Captcha
        $storedCaptcha = session('captcha_code');
        $inputCaptcha = strtolower(trim($request->input('captcha')));

        // Clear stored captcha to prevent replay attacks
        session()->forget('captcha_code');

        if (! $storedCaptcha || $inputCaptcha !== $storedCaptcha) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'captcha' => 'Kode captcha yang Anda masukkan salah. Silakan coba lagi.',
                ]);
        }

        // 3. Find User & Verify Password
        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Kombinasi email atau password tidak sesuai.',
                ]);
        }

        // 4. Generate JWT Token
        $remember = $request->boolean('remember');
        $token = $this->jwtService->generateToken($user, $remember);

        // 5. Save Token to Session & Queue HTTP-Only Secure Cookie
        $cookieMinutes = $remember ? 60 * 24 * 7 : 60 * 24; // 7 days or 24 hours
        $jwtCookie = cookie(
            name: 'jwt_token',
            value: $token,
            minutes: $cookieMinutes,
            path: '/',
            domain: null,
            secure: $request->isSecure(),
            httpOnly: true,
            raw: false,
            sameSite: 'lax'
        );

        session(['jwt_token' => $token]);
        Auth::setUser($user);

        // Redirect to dashboard with the JWT cookie
        return redirect()->route('dashboard')
            ->withCookie($jwtCookie)
            ->with('status', 'Selamat datang kembali, ' . $user->name . '!');
    }

    /**
     * Log the user out and invalidate the JWT token cookie & session.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        session()->forget('jwt_token');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $forgetCookie = cookie()->forget('jwt_token');

        return redirect()->route('auth.login')
            ->withCookie($forgetCookie)
            ->with('status', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
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
            'name.required'        => 'Nama lengkap wajib diisi.',
            'email.required'       => 'Alamat email wajib diisi.',
            'email.email'          => 'Format email tidak valid.',
            'email.unique'         => 'Alamat email sudah terdaftar, silakan gunakan email lain.',
            'password.required'    => 'Kata sandi wajib diisi.',
            'password.min'         => 'Kata sandi minimal harus 8 karakter.',
            'password.confirmed'   => 'Konfirmasi kata sandi tidak cocok.',
            'password.regex'       => 'Kata sandi harus mengandung huruf kecil, huruf besar, dan simbol khusus.',
            'password_confirmation.required' => 'Konfirmasi kata sandi wajib diisi.',
        ]);

        User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()->route('auth.login')
            ->with('status', 'Pendaftaran berhasil! Akun Anda telah siap, silakan masuk.');
    }
}
