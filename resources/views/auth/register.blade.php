@extends('layouts.auth')

@section('title', 'Register - Spark Admin')
@section('meta_description', 'Pendaftaran Akun Baru - Spark Admin Premium Dashboard')

@section('content')

    {{-- ==========================================
         START: Authentication Container & Register Card
         ========================================== --}}
    <div class="login-wrapper">
        {{-- Glowing background shapes --}}
        <!-- <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div> -->

        {{-- Main centered register card --}}
        <div class="login-card register-card">

            {{-- Brand Identity --}}
            <a href="{{ route('dashboard') }}" class="login-brand text-decoration-none">
                <i class="bi bi-asterisk"></i>
                <span>Spark Admin</span>
            </a>

            <p class="login-subtitle">Lengkapi formulir berikut untuk membuat akun baru</p>

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

            {{-- Register Form --}}
            <form action="{{ route('auth.register.post') }}" method="POST" id="registerForm" class="needs-validation" novalidate>
                @csrf

                {{-- Full Name Input Group --}}
                <div class="login-form-group">
                    <label for="name" class="login-form-label">Nama</label>
                    <div class="login-input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" id="name" name="name"
                            class="login-input @error('name') is-invalid @enderror"
                            placeholder="Masukkan Nama Anda"
                            value="{{ old('name') }}"
                            required autofocus autocomplete="name">
                    </div>
                </div>

                {{-- Email Input Group --}}
                <div class="login-form-group">
                    <label for="email" class="login-form-label">Alamat Email</label>
                    <div class="login-input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" id="email" name="email"
                            class="login-input @error('email') is-invalid @enderror"
                            placeholder="Masukkan Alamat Email"
                            value="{{ old('email') }}"
                            required autocomplete="email">
                    </div>
                </div>

                {{-- Password Input Group --}}
                <div class="login-form-group">
                    <label for="password" class="login-form-label">Kata Sandi</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" id="password" name="password"
                            class="login-input login-input-password @error('password') is-invalid @enderror"
                            placeholder="Masukkan Kata Sandi"
                            required autocomplete="new-password">
                        <button type="button" class="password-toggle-btn" id="toggle-password" aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    {{-- Real-Time Password Strength Meter & Interactive Requirements --}}
                    <div class="password-strength-box">
                        <div class="password-strength-header">
                            <span>Kekuatan Password</span>
                            <span id="strengthText" class="password-strength-label text-muted">Belum diisi</span>
                        </div>
                        <div class="password-strength-meter">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>

                        {{-- 4 Interactive Checklist Items --}}
                        <ul class="password-requirements-list">
                            <li class="password-requirement-item" id="req-length">
                                <i class="bi bi-x-circle"></i>
                                <span>Min. 8 Karakter</span>
                            </li>
                            <li class="password-requirement-item" id="req-lowercase">
                                <i class="bi bi-x-circle"></i>
                                <span>Huruf Kecil (a-z)</span>
                            </li>
                            <li class="password-requirement-item" id="req-uppercase">
                                <i class="bi bi-x-circle"></i>
                                <span>Huruf Besar (A-Z)</span>
                            </li>
                            <li class="password-requirement-item" id="req-symbol">
                                <i class="bi bi-x-circle"></i>
                                <span>Simbol Khusus (!@#$)</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Password Confirmation Input Group --}}
                <div class="login-form-group mb-4">
                    <label for="password_confirmation" class="login-form-label">Konfirmasi Kata Sandi</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-check input-icon"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="login-input login-input-password"
                            placeholder="Masukkan Konfirmasi Kata Sandi"
                            required autocomplete="new-password">
                        <button type="button" class="password-toggle-btn" id="toggle-password-confirm" aria-label="Show password confirmation">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="matchBadge" class="password-match-badge d-none"></div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn-login" id="btn-submit">
                    <span>Daftar Akun Sekarang</span>
                    <i class="bi bi-arrow-right"></i>
                </button>

            </form>

            {{-- Footer Link --}}
            <p class="login-footer-text mt-4 mb-0">
                Sudah punya akun? <a href="{{ route('auth.login') }}" id="link-login">Masuk Sekarang</a>
            </p>

        </div>
    </div>
    {{-- END: Authentication Container --}}

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('password_confirmation');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const matchBadge = document.getElementById('matchBadge');

            const reqLength = document.getElementById('req-length');
            const reqLowercase = document.getElementById('req-lowercase');
            const reqUppercase = document.getElementById('req-uppercase');
            const reqSymbol = document.getElementById('req-symbol');

            // Password Toggle for Main Password
            const togglePassword = document.getElementById('toggle-password');
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function () {
                    const isPass = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPass ? 'text' : 'password');
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
                    }
                });
            }

            // Password Toggle for Confirm Password
            const toggleConfirm = document.getElementById('toggle-password-confirm');
            if (toggleConfirm && confirmInput) {
                toggleConfirm.addEventListener('click', function () {
                    const isPass = confirmInput.getAttribute('type') === 'password';
                    confirmInput.setAttribute('type', isPass ? 'text' : 'password');
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
                    }
                });
            }

            // Update Single Requirement Element UI
            function updateRequirement(el, isValid) {
                const icon = el.querySelector('i');
                if (isValid) {
                    el.classList.remove('invalid');
                    el.classList.add('valid');
                    if (icon) icon.className = 'bi bi-check-circle-fill';
                } else {
                    el.classList.remove('valid');
                    el.classList.add('invalid');
                    if (icon) icon.className = 'bi bi-x-circle';
                }
            }

            // Real-Time Password Strength & Checklist Evaluator
            function evaluatePassword() {
                const val = passwordInput.value;

                if (!val) {
                    // Reset all
                    [reqLength, reqLowercase, reqUppercase, reqSymbol].forEach(el => {
                        el.classList.remove('valid', 'invalid');
                        const icon = el.querySelector('i');
                        if (icon) icon.className = 'bi bi-x-circle';
                    });
                    strengthBar.style.width = '0%';
                    strengthBar.style.backgroundColor = 'transparent';
                    strengthText.textContent = 'Belum diisi';
                    strengthText.className = 'password-strength-label text-muted';
                    evaluateMatch();
                    return;
                }

                // Criteria validations
                const hasLength = val.length >= 8;
                const hasLower = /[a-z]/.test(val);
                const hasUpper = /[A-Z]/.test(val);
                const hasSymbol = /[^A-Za-z0-9]/.test(val);

                updateRequirement(reqLength, hasLength);
                updateRequirement(reqLowercase, hasLower);
                updateRequirement(reqUppercase, hasUpper);
                updateRequirement(reqSymbol, hasSymbol);

                // Calculate Strength Score (0 to 4)
                let score = 0;
                if (hasLength) score++;
                if (hasLower) score++;
                if (hasUpper) score++;
                if (hasSymbol) score++;

                // Update Progress Bar & Label
                switch (score) {
                    case 1:
                        strengthBar.style.width = '25%';
                        strengthBar.style.backgroundColor = '#ef4444'; // Red
                        strengthText.textContent = 'Sangat Lemah';
                        strengthText.className = 'password-strength-label text-danger';
                        break;
                    case 2:
                        strengthBar.style.width = '50%';
                        strengthBar.style.backgroundColor = '#f97316'; // Orange
                        strengthText.textContent = 'Lemah';
                        strengthText.className = 'password-strength-label text-warning';
                        break;
                    case 3:
                        strengthBar.style.width = '75%';
                        strengthBar.style.backgroundColor = '#ec4899'; // Pink/Rose
                        strengthText.textContent = 'Sedang';
                        strengthText.className = 'password-strength-label text-primary';
                        break;
                    case 4:
                        strengthBar.style.width = '100%';
                        strengthBar.style.backgroundColor = '#22c55e'; // Green
                        strengthText.textContent = 'Sangat Kuat';
                        strengthText.className = 'password-strength-label text-success';
                        break;
                    default:
                        strengthBar.style.width = '10%';
                        strengthBar.style.backgroundColor = '#ef4444';
                        strengthText.textContent = 'Terlalu Lemah';
                        strengthText.className = 'password-strength-label text-danger';
                        break;
                }

                evaluateMatch();
            }

            // Real-Time Confirm Password Match Evaluator
            function evaluateMatch() {
                const pass = passwordInput.value;
                const conf = confirmInput.value;

                if (!conf) {
                    matchBadge.className = 'password-match-badge d-none';
                    matchBadge.innerHTML = '';
                    return;
                }

                matchBadge.classList.remove('d-none');
                if (pass === conf) {
                    matchBadge.className = 'password-match-badge match';
                    matchBadge.innerHTML = '<i class="bi bi-check-circle-fill"></i><span>Kata sandi cocok</span>';
                } else {
                    matchBadge.className = 'password-match-badge mismatch';
                    matchBadge.innerHTML = '<i class="bi bi-x-circle-fill"></i><span>Kata sandi belum cocok</span>';
                }
            }

            // Attach event listeners
            passwordInput.addEventListener('input', evaluatePassword);
            confirmInput.addEventListener('input', evaluateMatch);
        });
    </script>
@endpush
