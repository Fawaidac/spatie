@extends('layouts.app')

@section('title', 'Settings - Spark Admin')
@section('meta_description', 'Account settings and profile - Spark Admin Dashboard')

@section('content')

    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Settings</h1>
            <p class="page-subtitle">Manage your profile, account preferences and security settings.</p>
        </div>
    </div>

    <div class="row g-4">

        {{-- Left Column: Profile Card --}}
        <div class="col-lg-4">
            <div class="card profile-card">
                <div class="profile-card-banner"></div>
                <div class="profile-card-body">
                    <div class="profile-avatar-wrapper">
                        <img src="{{ $user->avatar ? asset($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=e11d74&color=fff&size=128' }}"
                            alt="{{ $user->name }}" class="profile-avatar" id="profile-avatar-img">
                    </div>
                    <h3 class="profile-name" id="display-profile-name">{{ $user->name }}</h3>
                    <p class="profile-role">
                        @forelse($user->roles as $role)
                            <span class="badge" style="background: rgba(225, 29, 116, 0.1); color: #e11d74; font-weight: 700;">
                                {{ ucfirst($role->name) }}
                            </span>
                        @empty
                            <span class="badge" style="background: #f1f5f9; color: #64748b;">User</span>
                        @endforelse
                    </p>
                    <p class="profile-email" id="display-profile-email">{{ $user->email }}</p>

                    <div class="profile-stats">
                        <div class="profile-stat-item">
                            <div class="profile-stat-value">{{ $user->roles->count() }}</div>
                            <div class="profile-stat-label">Roles</div>
                        </div>
                        <div class="profile-stat-item">
                            <div class="profile-stat-value">{{ $user->getAllPermissions()->count() }}</div>
                            <div class="profile-stat-label">Permissions</div>
                        </div>
                        <div class="profile-stat-item">
                            <div class="profile-stat-value">
                                <span class="badge bg-success-soft text-success" style="font-size: 0.75rem;">
                                    {{ ucfirst($user->status ?? 'Active') }}
                                </span>
                            </div>
                            <div class="profile-stat-label">Status</div>
                        </div>
                    </div>

                    <div class="profile-info-list">
                        <div class="profile-info-item">
                            <i class="bi bi-telephone-fill"></i>
                            <span id="display-profile-phone">{{ $user->phone ?? 'Belum ada nomor telepon' }}</span>
                        </div>
                        <div class="profile-info-item">
                            <i class="bi bi-calendar3"></i>
                            <span>Joined {{ $user->created_at ? $user->created_at->format('F Y') : '-' }}</span>
                        </div>
                        <div class="profile-info-item">
                            <i class="bi bi-shield-check"></i>
                            <span>Guard: web</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Settings Tabs --}}
        <div class="col-lg-8">

            {{-- Settings Navigation Tabs --}}
            <div class="settings-tabs-wrapper">
                <ul class="nav nav-tabs settings-nav-tabs" id="settingsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab"
                            data-bs-target="#profile-pane" type="button" role="tab" aria-controls="profile-pane"
                            aria-selected="true">
                            <i class="bi bi-person"></i> Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="security-tab" data-bs-toggle="tab"
                            data-bs-target="#security-pane" type="button" role="tab" aria-controls="security-pane"
                            aria-selected="false">
                            <i class="bi bi-shield-lock"></i> Security
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="permissions-tab" data-bs-toggle="tab"
                            data-bs-target="#permissions-pane" type="button" role="tab" aria-controls="permissions-pane"
                            aria-selected="false">
                            <i class="bi bi-key"></i> My Permissions
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Tab Content --}}
            <div class="tab-content settings-tab-content" id="settingsTabContent">

                {{-- Profile Tab --}}
                <div class="tab-pane fade show active" id="profile-pane" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Personal Information</h2>
                            <span class="settings-badge"><i class="bi bi-info-circle"></i> Edit Profile</span>
                        </div>
                        <form id="form-profile">
                            @csrf
                            <div class="row g-3 p-3">
                                <div class="col-md-6">
                                    <label class="form-label-spark" for="profile-name">Full Name <span class="text-danger">*</span></label>
                                    <div class="input-icon-wrapper">
                                        <i class="bi bi-person input-icon"></i>
                                        <input type="text" class="form-control form-control-spark" id="profile-name"
                                            name="name" value="{{ $user->name }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-spark" for="profile-email">Email Address <span class="text-danger">*</span></label>
                                    <div class="input-icon-wrapper">
                                        <i class="bi bi-envelope input-icon"></i>
                                        <input type="email" class="form-control form-control-spark" id="profile-email"
                                            name="email" value="{{ $user->email }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-spark" for="profile-phone">Phone Number</label>
                                    <div class="input-icon-wrapper">
                                        <i class="bi bi-telephone input-icon"></i>
                                        <input type="tel" class="form-control form-control-spark" id="profile-phone"
                                            name="phone" value="{{ $user->phone }}" placeholder="+62 8xx xxxx xxxx">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-spark" for="profile-status">Account Status</label>
                                    <input type="text" class="form-control form-control-spark" id="profile-status"
                                        value="{{ ucfirst($user->status ?? 'Active') }}" disabled readonly
                                        style="background-color: #f8fafc !important;">
                                </div>
                                <div class="col-12">
                                    <label class="form-label-spark" for="profile-bio">Bio / About Me</label>
                                    <textarea class="form-control form-control-spark" id="profile-bio" name="bio" rows="3"
                                        placeholder="Ceritakan sedikit tentang Anda...">{{ $user->bio }}</textarea>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                                    <button type="button" class="btn-primary-spark" id="btn-save-profile"
                                        onclick="saveProfile()">
                                        <i class="bi bi-check-lg"></i> Save Profile
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Security Tab --}}
                <div class="tab-pane fade" id="security-pane" role="tabpanel" aria-labelledby="security-tab">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h2 class="card-title">Change Password</h2>
                        </div>
                        <form id="form-password">
                            @csrf
                            <div class="row g-3 p-3">
                                <div class="col-12">
                                    <label class="form-label-spark" for="current-password">Current Password <span class="text-danger">*</span></label>
                                    <div class="input-icon-wrapper has-toggle">
                                        <i class="bi bi-shield-lock input-icon"></i>
                                        <input type="password" class="form-control form-control-spark"
                                            id="current-password" name="current_password" placeholder="Enter current password" required autocomplete="current-password">
                                        <button type="button" class="password-toggle-btn-spark" id="toggle-current-password" aria-label="Show password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-spark" for="new-password">New Password <span class="text-danger">*</span></label>
                                    <div class="input-icon-wrapper has-toggle">
                                        <i class="bi bi-lock-fill input-icon"></i>
                                        <input type="password" class="form-control form-control-spark" id="new-password"
                                            name="password" placeholder="Min. 8 characters" required minlength="8" autocomplete="new-password">
                                        <button type="button" class="password-toggle-btn-spark" id="toggle-new-password" aria-label="Show password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>

                                    {{-- Real-Time Password Strength Meter & Interactive Requirements --}}
                                    <div class="password-strength-box">
                                        <div class="password-strength-header">
                                            <span>Kekuatan Password</span>
                                            <span id="setting-strengthText" class="password-strength-label text-muted">Belum diisi</span>
                                        </div>
                                        <div class="password-strength-meter">
                                            <div class="password-strength-bar" id="setting-strengthBar"></div>
                                        </div>

                                        <ul class="password-requirements-list">
                                            <li class="password-requirement-item" id="setting-req-length">
                                                <i class="bi bi-x-circle"></i>
                                                <span>Min. 8 Karakter</span>
                                            </li>
                                            <li class="password-requirement-item" id="setting-req-lowercase">
                                                <i class="bi bi-x-circle"></i>
                                                <span>Huruf Kecil (a-z)</span>
                                            </li>
                                            <li class="password-requirement-item" id="setting-req-uppercase">
                                                <i class="bi bi-x-circle"></i>
                                                <span>Huruf Besar (A-Z)</span>
                                            </li>
                                            <li class="password-requirement-item" id="setting-req-symbol">
                                                <i class="bi bi-x-circle"></i>
                                                <span>Simbol Khusus (!@#$)</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-spark" for="confirm-new-password">Confirm New Password <span class="text-danger">*</span></label>
                                    <div class="input-icon-wrapper has-toggle">
                                        <i class="bi bi-shield-check input-icon"></i>
                                        <input type="password" class="form-control form-control-spark"
                                            id="confirm-new-password" name="password_confirmation" placeholder="Confirm new password" required autocomplete="new-password">
                                        <button type="button" class="password-toggle-btn-spark" id="toggle-confirm-password" aria-label="Show password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div id="setting-matchBadge" class="password-match-badge d-none mt-2"></div>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                                    <button type="button" class="btn-primary-spark" id="btn-update-password"
                                        onclick="updatePassword()">
                                        <i class="bi bi-shield-check"></i> Update Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- My Permissions Tab --}}
                <div class="tab-pane fade" id="permissions-pane" role="tabpanel" aria-labelledby="permissions-tab">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Active Permissions</h2>
                            <span class="settings-badge"><i class="bi bi-shield-lock"></i> Granted via Spatie</span>
                        </div>
                        <div class="p-3">
                            <p class="text-muted" style="font-size: 0.85rem;">
                                Berikut adalah daftar permission yang aktif dan dapat Anda gunakan berdasarkan role Anda:
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse($permissions as $perm)
                                    <span class="permission-tag permission-tag-granted">
                                        <i class="bi bi-check-circle-fill me-1 text-success"></i>
                                        {{ $perm->name }}
                                    </span>
                                @empty
                                    <span class="text-muted" style="font-size: 0.85rem;">Belum ada permission khusus yang diberikan.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/crud.css') }}">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || '{{ csrf_token() }}';

        // 1. Update Profile Information
        async function saveProfile() {
            const btn = document.getElementById('btn-save-profile');
            const form = document.getElementById('form-profile');
            const formData = new FormData(form);
            formData.append('_method', 'PUT');

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

            try {
                const response = await fetch("{{ route('settings.updateProfile') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    let errorMsg = data.message || 'Gagal menyimpan profil.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        html: errorMsg,
                        confirmButtonColor: '#e11d74'
                    });
                } else {
                    document.getElementById('display-profile-name').innerText = data.user.name;
                    document.getElementById('display-profile-email').innerText = data.user.email;
                    if (data.user.phone) {
                        document.getElementById('display-profile-phone').innerText = data.user.phone;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Profil Diperbarui!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: err.message,
                    confirmButtonColor: '#e11d74'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Save Profile';
            }
        }

        // 2. Setup Password Toggles & Strength Evaluator
        const currentPassInput = document.getElementById('current-password');
        const newPassInput = document.getElementById('new-password');
        const confirmPassInput = document.getElementById('confirm-new-password');

        const toggleCurrent = document.getElementById('toggle-current-password');
        const toggleNew = document.getElementById('toggle-new-password');
        const toggleConfirm = document.getElementById('toggle-confirm-password');

        function setupToggle(btn, input) {
            if (!btn || !input) return;
            btn.addEventListener('click', function () {
                const isPass = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPass ? 'text' : 'password');
                const icon = this.querySelector('i');
                if (icon) icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }

        setupToggle(toggleCurrent, currentPassInput);
        setupToggle(toggleNew, newPassInput);
        setupToggle(toggleConfirm, confirmPassInput);

        const setStrengthBar = document.getElementById('setting-strengthBar');
        const setStrengthText = document.getElementById('setting-strengthText');
        const setMatchBadge = document.getElementById('setting-matchBadge');

        const setReqLength = document.getElementById('setting-req-length');
        const setReqLower = document.getElementById('setting-req-lowercase');
        const setReqUpper = document.getElementById('setting-req-uppercase');
        const setReqSymbol = document.getElementById('setting-req-symbol');

        function updateSettingReq(el, isValid) {
            if (!el) return;
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

        function evaluateSettingMatch() {
            if (!setMatchBadge || !confirmPassInput) return;
            const pVal = newPassInput?.value || '';
            const cVal = confirmPassInput.value;

            if (!cVal) {
                setMatchBadge.className = 'password-match-badge d-none';
                setMatchBadge.innerHTML = '';
                return;
            }

            if (pVal === cVal) {
                setMatchBadge.className = 'password-match-badge matched';
                setMatchBadge.innerHTML = '<i class="bi bi-check-circle-fill"></i> Password Cocok';
            } else {
                setMatchBadge.className = 'password-match-badge mismatched';
                setMatchBadge.innerHTML = '<i class="bi bi-x-circle-fill"></i> Password Belum Cocok';
            }
        }

        function evaluateSettingPassword() {
            if (!newPassInput || !setStrengthBar) return;
            const val = newPassInput.value;

            if (!val) {
                [setReqLength, setReqLower, setReqUpper, setReqSymbol].forEach(el => {
                    if (!el) return;
                    el.classList.remove('valid', 'invalid');
                    const icon = el.querySelector('i');
                    if (icon) icon.className = 'bi bi-x-circle';
                });
                setStrengthBar.style.width = '0%';
                setStrengthBar.style.backgroundColor = 'transparent';
                setStrengthText.textContent = 'Belum diisi';
                setStrengthText.className = 'password-strength-label text-muted';
                evaluateSettingMatch();
                return;
            }

            const hasLength = val.length >= 8;
            const hasLower = /[a-z]/.test(val);
            const hasUpper = /[A-Z]/.test(val);
            const hasSymbol = /[^A-Za-z0-9]/.test(val);

            updateSettingReq(setReqLength, hasLength);
            updateSettingReq(setReqLower, hasLower);
            updateSettingReq(setReqUpper, hasUpper);
            updateSettingReq(setReqSymbol, hasSymbol);

            let score = 0;
            if (hasLength) score++;
            if (hasLower) score++;
            if (hasUpper) score++;
            if (hasSymbol) score++;

            switch (score) {
                case 1:
                    setStrengthBar.style.width = '25%';
                    setStrengthBar.style.backgroundColor = '#ef4444';
                    setStrengthText.textContent = 'Sangat Lemah';
                    setStrengthText.className = 'password-strength-label text-danger';
                    break;
                case 2:
                    setStrengthBar.style.width = '50%';
                    setStrengthBar.style.backgroundColor = '#f97316';
                    setStrengthText.textContent = 'Cukup';
                    setStrengthText.className = 'password-strength-label text-warning';
                    break;
                case 3:
                    setStrengthBar.style.width = '75%';
                    setStrengthBar.style.backgroundColor = '#3b82f6';
                    setStrengthText.textContent = 'Bagus';
                    setStrengthText.className = 'password-strength-label text-info';
                    break;
                case 4:
                    setStrengthBar.style.width = '100%';
                    setStrengthBar.style.backgroundColor = '#10b981';
                    setStrengthText.textContent = 'Sangat Kuat';
                    setStrengthText.className = 'password-strength-label text-success';
                    break;
            }

            evaluateSettingMatch();
        }

        newPassInput?.addEventListener('input', evaluateSettingPassword);
        confirmPassInput?.addEventListener('input', evaluateSettingMatch);

        // 3. Update Password
        async function updatePassword() {
            const btn = document.getElementById('btn-update-password');
            const form = document.getElementById('form-password');
            const formData = new FormData(form);
            formData.append('_method', 'PUT');

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating...';

            try {
                const response = await fetch("{{ route('settings.updatePassword') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    let errorMsg = data.message || 'Gagal mengubah password.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: errorMsg,
                        confirmButtonColor: '#e11d74'
                    });
                } else {
                    form.reset();
                    evaluateSettingPassword();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: err.message,
                    confirmButtonColor: '#e11d74'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-shield-check"></i> Update Password';
            }
        }
    </script>
@endpush
