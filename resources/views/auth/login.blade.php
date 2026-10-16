@extends('layouts.auth')

@section('title', 'Login - Spark Admin')
@section('meta_description', 'Login Screen - Spark Admin Premium Bootstrap 5 Admin Dashboard Template')

@section('content')

    {{-- ==========================================
         START: Authentication Container & Login Card
         ========================================== --}}
    <div class="login-wrapper">
        {{-- Glowing background shapes for modern visual appearance --}}
        <!-- <div class="login-bg-shape login-bg-shape-1"></div> -->
        <!-- <div class="login-bg-shape login-bg-shape-2"></div> -->

        {{-- Main centered login card --}}
        <div class="login-card">

            {{-- Brand Identity --}}
            <a href="{{ route('dashboard') }}" class="login-brand text-decoration-none">
                <i class="bi bi-asterisk"></i>
                <span>Spark Admin</span>
            </a>

            <p class="login-subtitle">Silakan masuk menggunakan akun Anda</p>

            {{-- Flash Status Message --}}
            @if (session('status'))
                <div class="alert alert-success d-flex align-items-center mb-3 py-2 px-3" role="alert" style="border-radius: var(--radius-md); font-size: 0.85rem;">
                    <i class="bi bi-check-circle-fill me-2 fs-6"></i>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            {{-- Error Alerts --}}
            @if ($errors->any())
                <div class="alert alert-danger mb-3 py-2 px-3" role="alert" style="border-radius: var(--radius-md); font-size: 0.85rem;">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Login Form --}}
            <form action="{{ route('auth.login.post') }}" method="POST" id="loginForm" class="needs-validation" novalidate>
                @csrf

                {{-- Email Input Group --}}
                <div class="login-form-group">
                    <label for="email" class="login-form-label">Alamat Email</label>
                    <div class="login-input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" id="email" name="email"
                            class="login-input @error('email') is-invalid @enderror"
                            placeholder="admin@email.com"
                            value="{{ old('email') }}"
                            required autocomplete="email" autofocus>
                    </div>
                </div>

                {{-- Password Input Group --}}
                <div class="login-form-group">
                    <label for="password" class="login-form-label">Kata Sandi</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" id="password" name="password"
                            class="login-input login-input-password @error('password') is-invalid @enderror"
                            placeholder="••••••••"
                            required autocomplete="current-password">
                        <button type="button" class="password-toggle-btn" id="toggle-password" aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                {{-- Captcha Input Group --}}
                <div class="login-form-group">
                    <label for="captcha" class="login-form-label">Kode Verifikasi (Captcha)</label>
                    
                    {{-- Captcha Image & Refresh Button --}}
                    <div class="captcha-container">
                        <div class="captcha-image-wrapper" title="Klik untuk refresh captcha">
                            <img src="{{ route('captcha') }}" id="captchaImage" alt="Captcha" onclick="refreshCaptcha()">
                        </div>
                        <button type="button" class="btn-captcha-refresh" id="btnRefreshCaptcha" title="Ganti kode captcha" onclick="refreshCaptcha()">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>

                    {{-- Captcha Text Input --}}
                    <div class="login-input-group">
                        <i class="bi bi-shield-check input-icon"></i>
                        <input type="text" id="captcha" name="captcha"
                            class="login-input @error('captcha') is-invalid @enderror"
                            placeholder="Ketik kode di atas..."
                            maxlength="6"
                            autocomplete="off"
                            required>
                    </div>
                </div>

                {{-- Options (Remember me & Forgot Password) --}}
                <div class="login-options">
                    <label class="custom-control-label">
                        <input type="checkbox" class="custom-checkbox-input" name="remember" id="rememberMe"
                            {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember Me</span>
                    </label>
                    <a href="#" class="forgot-password-link">Forgot Password?</a>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn-login" id="btn-submit">
                    <span>Masuk ke Dashboard</span>
                    <i class="bi bi-arrow-right"></i>
                </button>

            </form>

            {{-- Divider --}}
            <!-- <div class="login-divider">Or sign in with</div> -->

            {{-- Social Logins --}}
                <!-- <div class="social-login-grid">
                    <button class="btn-social" type="button" id="btn-google">
                        <i class="bi bi-google text-danger"></i>
                        <span>Google</span>
                    </button>
                    <button class="btn-social" type="button" id="btn-github">
                        <i class="bi bi-github"></i>
                        <span>GitHub</span>
                    </button>
                </div> -->

            {{-- Footer Link --}}
            <p class="login-footer-text mt-4">
                Belum punya akun? <a href="{{ route('auth.register') }}" id="link-register">Daftar Sekarang</a>
            </p>

        </div>
    </div>
    {{-- END: Authentication Container --}}

@endsection

@push('scripts')
    <script src="{{ asset('assets/js/auth.js') }}"></script>
    <script>
        function refreshCaptcha() {
            const img = document.getElementById('captchaImage');
            if (img) {
                img.src = '{{ route('captcha') }}?' + Date.now();
            }
            const input = document.getElementById('captcha');
            if (input) {
                input.value = '';
                input.focus();
            }
        }
    </script>
@endpush
