@extends('layouts.app')

@section('title', 'Users Management - Spark Admin')
@section('meta_description', 'Manage users - Spark Admin Dashboard')

@section('content')

    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Users Management</h1>
            <p class="page-subtitle">Manage all registered users, assign roles and permissions.</p>
        </div>
        <button class="btn-primary-spark" type="button" data-bs-toggle="modal" data-bs-target="#createUserModal"
            id="btn-create-user">
            <i class="bi bi-plus-lg"></i>
            <span>Add User</span>
        </button>
    </div>

    {{-- Users Table Card --}}
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">All Users</h2>
            <form method="GET" action="{{ route('users.index') }}" class="d-flex flex-wrap gap-2 align-items-center" id="filter-form">
                {{-- Search Input --}}
                <div class="table-search-wrapper">
                    <i class="bi bi-search table-search-icon"></i>
                    <input type="text" name="search" class="table-search-input" placeholder="Search name or email..."
                        value="{{ request('search') }}" id="search-users">
                </div>

                {{-- Retain active filters when searching --}}
                @if(request('status') && request('status') !== 'all')
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('role') && request('role') !== 'all')
                    <input type="hidden" name="role" value="{{ request('role') }}">
                @endif

                {{-- Status Filter Dropdown --}}
                <div class="dropdown">
                    <button class="btn-filter-spark dropdown-toggle {{ request('status') && request('status') !== 'all' ? 'has-filter' : '' }}" 
                        type="button" data-bs-toggle="dropdown" aria-expanded="false" id="btn-filter-users">
                        <i class="bi bi-funnel"></i>
                        <span>Status: {{ request('status') && request('status') !== 'all' ? ucfirst(request('status')) : 'All' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                        <li><h6 class="dropdown-header text-uppercase text-muted" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em;">Filter By Status</h6></li>
                        <li><a class="dropdown-item {{ !request('status') || request('status') === 'all' ? 'active' : '' }}"
                                href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}">All Statuses</a></li>
                        <li><a class="dropdown-item {{ request('status') === 'active' ? 'active' : '' }}"
                                href="{{ request()->fullUrlWithQuery(['status' => 'active', 'page' => 1]) }}">
                                <span>Active</span>
                                <span class="badge bg-success-soft text-success rounded-pill px-2">Active</span>
                            </a></li>
                        <li><a class="dropdown-item {{ request('status') === 'inactive' ? 'active' : '' }}"
                                href="{{ request()->fullUrlWithQuery(['status' => 'inactive', 'page' => 1]) }}">
                                <span>Inactive</span>
                                <span class="badge bg-warning-soft text-warning rounded-pill px-2">Inactive</span>
                            </a></li>
                        <li><a class="dropdown-item {{ request('status') === 'banned' ? 'active' : '' }}"
                                href="{{ request()->fullUrlWithQuery(['status' => 'banned', 'page' => 1]) }}">
                                <span>Banned</span>
                                <span class="badge bg-danger-soft text-danger rounded-pill px-2">Banned</span>
                            </a></li>
                    </ul>
                </div>

                {{-- Role Filter Dropdown --}}
                <div class="dropdown">
                    <button class="btn-filter-spark dropdown-toggle {{ request('role') && request('role') !== 'all' ? 'has-filter' : '' }}" 
                        type="button" data-bs-toggle="dropdown" aria-expanded="false" id="btn-filter-role">
                        <i class="bi bi-shield-lock"></i>
                        <span>Role: {{ request('role') && request('role') !== 'all' ? ucfirst(request('role')) : 'All' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                        <li><h6 class="dropdown-header text-uppercase text-muted" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em;">Filter By Role</h6></li>
                        <li><a class="dropdown-item {{ !request('role') || request('role') === 'all' ? 'active' : '' }}"
                                href="{{ request()->fullUrlWithQuery(['role' => 'all', 'page' => 1]) }}">All Roles</a></li>
                        @foreach($roles as $r)
                            <li><a class="dropdown-item {{ request('role') === $r->name ? 'active' : '' }}"
                                    href="{{ request()->fullUrlWithQuery(['role' => $r->name, 'page' => 1]) }}">
                                    <span>{{ ucfirst($r->name) }}</span>
                                </a></li>
                        @endforeach
                    </ul>
                </div>

                {{-- Reset Filters Button --}}
                @if(request('search') || (request('status') && request('status') !== 'all') || (request('role') && request('role') !== 'all'))
                    <a href="{{ route('users.index') }}" class="btn-filter-reset" title="Reset all filters">
                        <i class="bi bi-x-circle"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-spark" id="users-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="28%">User</th>
                        <th width="22%">Email</th>
                        <th width="15%">Role</th>
                        <th width="12%">Status</th>
                        <th width="10%">Joined</th>
                        <th width="8%" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr>
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td>
                                <div class="user-cell">
                                    <!-- <img src="{{ $user->avatar ? asset($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=e11d74&color=fff' }}"
                                        alt="{{ $user->name }}" class="user-cell-avatar"> -->
                                    <div>
                                        <div class="user-cell-name">{{ $user->name }}</div>
                                        <div class="user-cell-meta">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @forelse($user->roles as $role)
                                    <span class="badge-role {{ $role->name === 'admin' ? 'badge-admin' : 'badge-editor' }}">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @empty
                                    <span class="badge-role" style="background: #f1f5f9; color: #64748b;">No Role</span>
                                @endforelse
                            </td>
                            <td>
                                @php
                                    $statusClass = match ($user->status ?? 'active') {
                                        'active' => 'badge-active',
                                        'inactive' => 'badge-inactive',
                                        'banned' => 'badge-banned',
                                        default => 'badge-active',
                                    };
                                @endphp
                                <span class="badge-status {{ $statusClass }}">
                                    {{ ucfirst($user->status ?? 'active') }}
                                </span>
                            </td>
                            <td>{{ $user->created_at ? $user->created_at->format('M d, Y') : '-' }}</td>
                            <td class="text-center">
                                <div class="action-btn-group">
                                    <button class="btn-action btn-action-edit btn-edit-user"
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        data-phone="{{ $user->phone ?? '' }}"
                                        data-status="{{ $user->status ?? 'active' }}"
                                        data-role="{{ $user->roles->first()?->name ?? 'user' }}"
                                        title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    @if(Auth::id() !== $user->id)
                                        <button class="btn-action btn-action-delete"
                                            onclick="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                            title="Delete">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Tidak ada data user yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Custom Spark Pagination --}}
        {{ $users->links('partials.pagination') }}
    </div>

    {{-- Create User Modal --}}
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-spark">
                <div class="modal-header modal-header-spark">
                    <div class="modal-header-icon">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="createUserModalLabel">Create New User</h5>
                        <p class="modal-subtitle">Fill in the details to create a new user account.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-spark" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-spark">
                    <form id="form-create-user">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-spark" for="create-name">Full Name <span class="text-danger">*</span></label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-person input-icon"></i>
                                    <input type="text" class="form-control form-control-spark" id="create-name"
                                        name="name" placeholder="Enter full name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-spark" for="create-email">Email Address <span class="text-danger">*</span></label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-envelope input-icon"></i>
                                    <input type="email" class="form-control form-control-spark" id="create-email"
                                        name="email" placeholder="Enter email address" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label-spark" for="create-password">Password <span class="text-danger">*</span></label>
                                <div class="input-icon-wrapper has-toggle">
                                    <i class="bi bi-shield-lock input-icon"></i>
                                    <input type="password" class="form-control form-control-spark" id="create-password"
                                        name="password" placeholder="Masukkan password pengguna" required minlength="8" autocomplete="new-password">
                                    <button type="button" class="password-toggle-btn-spark" id="toggle-create-password" aria-label="Show password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>

                                {{-- Real-Time Password Strength Meter & Interactive Requirements --}}
                                <div class="password-strength-box">
                                    <div class="password-strength-header">
                                        <span>Kekuatan Password</span>
                                        <span id="create-strengthText" class="password-strength-label text-muted">Belum diisi</span>
                                    </div>
                                    <div class="password-strength-meter">
                                        <div class="password-strength-bar" id="create-strengthBar"></div>
                                    </div>

                                    <ul class="password-requirements-list">
                                        <li class="password-requirement-item" id="create-req-length">
                                            <i class="bi bi-x-circle"></i>
                                            <span>Min. 8 Karakter</span>
                                        </li>
                                        <li class="password-requirement-item" id="create-req-lowercase">
                                            <i class="bi bi-x-circle"></i>
                                            <span>Huruf Kecil (a-z)</span>
                                        </li>
                                        <li class="password-requirement-item" id="create-req-uppercase">
                                            <i class="bi bi-x-circle"></i>
                                            <span>Huruf Besar (A-Z)</span>
                                        </li>
                                        <li class="password-requirement-item" id="create-req-symbol">
                                            <i class="bi bi-x-circle"></i>
                                            <span>Simbol Khusus (!@#$)</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-spark" for="create-role">Assign Role <span class="text-danger">*</span></label>
                                <select class="form-select form-select-spark" id="create-role" name="role" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ $role->name === 'user' ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-spark" for="create-status">Status <span class="text-danger">*</span></label>
                                <select class="form-select form-select-spark" id="create-status" name="status" required>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="banned">Banned</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-spark" for="create-phone">Phone Number</label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-telephone input-icon"></i>
                                    <input type="text" class="form-control form-control-spark" id="create-phone"
                                        name="phone" placeholder="+62 8xx xxxx xxxx">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-spark">
                    <button type="button" class="btn-secondary-spark" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-primary-spark" id="btn-save-user">
                        <i class="bi bi-check-lg"></i> Create User
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit User Modal --}}
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-spark">
                <div class="modal-header modal-header-spark">
                    <div class="modal-header-icon">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <p class="modal-subtitle">Update the user account details and permissions.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-spark" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-spark">
                    <form id="form-edit-user">
                        @csrf
                        <input type="hidden" id="edit-user-id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-spark" for="edit-name">Full Name <span class="text-danger">*</span></label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-person input-icon"></i>
                                    <input type="text" class="form-control form-control-spark" id="edit-name"
                                        name="name" placeholder="Enter full name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-spark" for="edit-email">Email Address <span class="text-danger">*</span></label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-envelope input-icon"></i>
                                    <input type="email" class="form-control form-control-spark" id="edit-email"
                                        name="email" placeholder="Enter email address" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label-spark" for="edit-password">
                                    New Password <small class="text-muted fw-normal">(Kosongkan jika tidak ingin mengubah password)</small>
                                </label>
                                <div class="input-icon-wrapper has-toggle">
                                    <i class="bi bi-shield-lock input-icon"></i>
                                    <input type="password" class="form-control form-control-spark" id="edit-password"
                                        name="password" placeholder="Masukkan password baru jika ingin mengubah" autocomplete="new-password">
                                    <button type="button" class="password-toggle-btn-spark" id="toggle-edit-password" aria-label="Show password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>

                                {{-- Real-Time Password Strength Meter & Interactive Requirements --}}
                                <div class="password-strength-box" id="edit-password-strength-box">
                                    <div class="password-strength-header">
                                        <span>Kekuatan Password Baru</span>
                                        <span id="edit-strengthText" class="password-strength-label text-muted">Tidak diubah</span>
                                    </div>
                                    <div class="password-strength-meter">
                                        <div class="password-strength-bar" id="edit-strengthBar"></div>
                                    </div>

                                    <ul class="password-requirements-list">
                                        <li class="password-requirement-item" id="edit-req-length">
                                            <i class="bi bi-x-circle"></i>
                                            <span>Min. 8 Karakter</span>
                                        </li>
                                        <li class="password-requirement-item" id="edit-req-lowercase">
                                            <i class="bi bi-x-circle"></i>
                                            <span>Huruf Kecil (a-z)</span>
                                        </li>
                                        <li class="password-requirement-item" id="edit-req-uppercase">
                                            <i class="bi bi-x-circle"></i>
                                            <span>Huruf Besar (A-Z)</span>
                                        </li>
                                        <li class="password-requirement-item" id="edit-req-symbol">
                                            <i class="bi bi-x-circle"></i>
                                            <span>Simbol Khusus (!@#$)</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-spark" for="edit-role">Assign Role <span class="text-danger">*</span></label>
                                <select class="form-select form-select-spark" id="edit-role" name="role" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-spark" for="edit-status">Status <span class="text-danger">*</span></label>
                                <select class="form-select form-select-spark" id="edit-status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="banned">Banned</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-spark" for="edit-phone">Phone Number</label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-telephone input-icon"></i>
                                    <input type="text" class="form-control form-control-spark" id="edit-phone"
                                        name="phone" placeholder="+62 8xx xxxx xxxx">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-spark">
                    <button type="button" class="btn-secondary-spark" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-primary-spark" id="btn-update-user">
                        <i class="bi bi-check-lg"></i> Update User
                    </button>
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

        // 0. Password Strength & Requirement Checklist for Create User
        const createPassInput = document.getElementById('create-password');
        const createToggleBtn = document.getElementById('toggle-create-password');
        const createStrengthBar = document.getElementById('create-strengthBar');
        const createStrengthText = document.getElementById('create-strengthText');
        const createReqLength = document.getElementById('create-req-length');
        const createReqLower = document.getElementById('create-req-lowercase');
        const createReqUpper = document.getElementById('create-req-uppercase');
        const createReqSymbol = document.getElementById('create-req-symbol');

        if (createToggleBtn && createPassInput) {
            createToggleBtn.addEventListener('click', function () {
                const isPass = createPassInput.getAttribute('type') === 'password';
                createPassInput.setAttribute('type', isPass ? 'text' : 'password');
                const icon = this.querySelector('i');
                if (icon) icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }

        function updateReqItem(el, isValid) {
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

        function evaluateCreatePassword() {
            if (!createPassInput || !createStrengthBar) return;
            const val = createPassInput.value;

            if (!val) {
                [createReqLength, createReqLower, createReqUpper, createReqSymbol].forEach(el => {
                    if (!el) return;
                    el.classList.remove('valid', 'invalid');
                    const icon = el.querySelector('i');
                    if (icon) icon.className = 'bi bi-x-circle';
                });
                createStrengthBar.style.width = '0%';
                createStrengthBar.style.backgroundColor = 'transparent';
                createStrengthText.textContent = 'Belum diisi';
                createStrengthText.className = 'password-strength-label text-muted';
                return;
            }

            const hasLength = val.length >= 8;
            const hasLower = /[a-z]/.test(val);
            const hasUpper = /[A-Z]/.test(val);
            const hasSymbol = /[^A-Za-z0-9]/.test(val);

            updateReqItem(createReqLength, hasLength);
            updateReqItem(createReqLower, hasLower);
            updateReqItem(createReqUpper, hasUpper);
            updateReqItem(createReqSymbol, hasSymbol);

            let score = 0;
            if (hasLength) score++;
            if (hasLower) score++;
            if (hasUpper) score++;
            if (hasSymbol) score++;

            switch (score) {
                case 1:
                    createStrengthBar.style.width = '25%';
                    createStrengthBar.style.backgroundColor = '#ef4444';
                    createStrengthText.textContent = 'Sangat Lemah';
                    createStrengthText.className = 'password-strength-label text-danger';
                    break;
                case 2:
                    createStrengthBar.style.width = '50%';
                    createStrengthBar.style.backgroundColor = '#f97316';
                    createStrengthText.textContent = 'Cukup';
                    createStrengthText.className = 'password-strength-label text-warning';
                    break;
                case 3:
                    createStrengthBar.style.width = '75%';
                    createStrengthBar.style.backgroundColor = '#3b82f6';
                    createStrengthText.textContent = 'Bagus';
                    createStrengthText.className = 'password-strength-label text-info';
                    break;
                case 4:
                    createStrengthBar.style.width = '100%';
                    createStrengthBar.style.backgroundColor = '#10b981';
                    createStrengthText.textContent = 'Sangat Kuat';
                    createStrengthText.className = 'password-strength-label text-success';
                    break;
            }
        }

        createPassInput?.addEventListener('input', evaluateCreatePassword);

        document.getElementById('createUserModal')?.addEventListener('show.bs.modal', function () {
            if (createPassInput) {
                createPassInput.value = '';
                evaluateCreatePassword();
            }
        });

        // 1. Submit Create User Form
        document.getElementById('btn-save-user')?.addEventListener('click', async function () {
            const form = document.getElementById('form-create-user');
            const formData = new FormData(form);

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creating...';

            try {
                const response = await fetch("{{ route('users.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    let errorMsg = data.message || 'Terjadi kesalahan saat menambahkan user.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menambahkan',
                        html: errorMsg,
                        confirmButtonColor: '#e11d74'
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
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
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Create User';
            }
        });

        // 1.5 Setup Password Evaluator for Edit User Modal
        const editPassInput = document.getElementById('edit-password');
        const editToggleBtn = document.getElementById('toggle-edit-password');
        const editStrengthBar = document.getElementById('edit-strengthBar');
        const editStrengthText = document.getElementById('edit-strengthText');
        const editReqLength = document.getElementById('edit-req-length');
        const editReqLower = document.getElementById('edit-req-lowercase');
        const editReqUpper = document.getElementById('edit-req-uppercase');
        const editReqSymbol = document.getElementById('edit-req-symbol');

        if (editToggleBtn && editPassInput) {
            editToggleBtn.addEventListener('click', function () {
                const isPass = editPassInput.getAttribute('type') === 'password';
                editPassInput.setAttribute('type', isPass ? 'text' : 'password');
                const icon = this.querySelector('i');
                if (icon) icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }

        function evaluateEditPassword() {
            if (!editPassInput || !editStrengthBar) return;
            const val = editPassInput.value;

            if (!val) {
                [editReqLength, editReqLower, editReqUpper, editReqSymbol].forEach(el => {
                    if (!el) return;
                    el.classList.remove('valid', 'invalid');
                    const icon = el.querySelector('i');
                    if (icon) icon.className = 'bi bi-x-circle';
                });
                editStrengthBar.style.width = '0%';
                editStrengthBar.style.backgroundColor = 'transparent';
                editStrengthText.textContent = 'Tidak diubah';
                editStrengthText.className = 'password-strength-label text-muted';
                return;
            }

            const hasLength = val.length >= 8;
            const hasLower = /[a-z]/.test(val);
            const hasUpper = /[A-Z]/.test(val);
            const hasSymbol = /[^A-Za-z0-9]/.test(val);

            updateReqItem(editReqLength, hasLength);
            updateReqItem(editReqLower, hasLower);
            updateReqItem(editReqUpper, hasUpper);
            updateReqItem(editReqSymbol, hasSymbol);

            let score = 0;
            if (hasLength) score++;
            if (hasLower) score++;
            if (hasUpper) score++;
            if (hasSymbol) score++;

            switch (score) {
                case 1:
                    editStrengthBar.style.width = '25%';
                    editStrengthBar.style.backgroundColor = '#ef4444';
                    editStrengthText.textContent = 'Sangat Lemah';
                    editStrengthText.className = 'password-strength-label text-danger';
                    break;
                case 2:
                    editStrengthBar.style.width = '50%';
                    editStrengthBar.style.backgroundColor = '#f97316';
                    editStrengthText.textContent = 'Cukup';
                    editStrengthText.className = 'password-strength-label text-warning';
                    break;
                case 3:
                    editStrengthBar.style.width = '75%';
                    editStrengthBar.style.backgroundColor = '#3b82f6';
                    editStrengthText.textContent = 'Bagus';
                    editStrengthText.className = 'password-strength-label text-info';
                    break;
                case 4:
                    editStrengthBar.style.width = '100%';
                    editStrengthBar.style.backgroundColor = '#10b981';
                    editStrengthText.textContent = 'Sangat Kuat';
                    editStrengthText.className = 'password-strength-label text-success';
                    break;
            }
        }

        editPassInput?.addEventListener('input', evaluateEditPassword);

        // 2. Open Edit User Modal & Populate Data (Event Delegation for Live Search)
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-edit-user');
            if (!btn) return;

            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const email = btn.getAttribute('data-email');
            const phone = btn.getAttribute('data-phone');
            const role = btn.getAttribute('data-role');
            const status = btn.getAttribute('data-status');

            document.getElementById('edit-user-id').value = id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-phone').value = phone;
            document.getElementById('edit-password').value = '';
            document.getElementById('edit-role').value = role;
            document.getElementById('edit-status').value = status;

            evaluateEditPassword();

            const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editModal.show();
        });

        // 3. Submit Update User Form
        document.getElementById('btn-update-user')?.addEventListener('click', async function () {
            const userId = document.getElementById('edit-user-id').value;
            const form = document.getElementById('form-edit-user');
            const formData = new FormData(form);
            formData.append('_method', 'PUT');

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating...';

            try {
                const response = await fetch(`/users/${userId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    let errorMsg = data.message || 'Terjadi kesalahan saat memperbarui user.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memperbarui',
                        html: errorMsg,
                        confirmButtonColor: '#e11d74'
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
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
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Update User';
            }
        });

        // 4. Delete User Confirmation via SweetAlert2
        function confirmDelete(userId, userName) {
            Swal.fire({
                title: 'Hapus User?',
                html: `Apakah Anda yakin ingin menghapus user <strong>${userName}</strong>?<br>Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash3"></i> Ya, Hapus',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'swal-spark-popup',
                    title: 'swal-spark-title',
                    confirmButton: 'swal-spark-confirm',
                    cancelButton: 'swal-spark-cancel'
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch(`/users/${userId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Menghapus',
                                text: data.message || 'Gagal menghapus user.',
                                confirmButtonColor: '#e11d74'
                            });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    } catch (err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan Sistem',
                            text: err.message,
                            confirmButtonColor: '#e11d74'
                        });
                    }
                }
            });
        }
    </script>
@endpush
