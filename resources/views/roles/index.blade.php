@extends('layouts.app')

@section('title', 'Roles Management - Spark Admin')
@section('meta_description', 'Manage roles - Spark Admin Dashboard')

@section('content')

    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Roles Management</h1>
            <p class="page-subtitle">Define roles and assign permissions to control user access levels.</p>
        </div>
        <button class="btn-primary-spark" type="button" data-bs-toggle="modal" data-bs-target="#createRoleModal"
            id="btn-create-role">
            <i class="bi bi-plus-lg"></i>
            <span>Add Role</span>
        </button>
    </div>

    {{-- Roles Grid Cards --}}
    <div class="row g-4 mb-4">

        @foreach($roles as $role)
            @php
                $isSystemRole = in_array($role->name, ['admin', 'user']);
                $iconClass = match($role->name) {
                    'admin' => 'bi-shield-fill-check text-primary',
                    'user'  => 'bi-person-fill text-info',
                    default => 'bi-shield-fill text-warning',
                };
                $iconBg = match($role->name) {
                    'admin' => 'bg-primary-soft',
                    'user'  => 'bg-info-soft',
                    default => 'bg-warning-soft',
                };
            @endphp
            <div class="col-lg-4 col-md-6">
                <div class="card role-card h-100 d-flex flex-column">
                    <div class="role-card-header">
                        <div class="role-card-icon {{ $iconBg }}">
                            <i class="bi {{ $iconClass }}"></i>
                        </div>
                        <div class="role-card-actions">
                            <button class="btn-action btn-action-edit btn-edit-role"
                                data-id="{{ $role->id }}"
                                data-name="{{ $role->name }}"
                                data-permissions='@json($role->permissions->pluck("name"))'
                                title="Edit Role & Permissions">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            @if(! $isSystemRole)
                                <button class="btn-action btn-action-delete"
                                    onclick="confirmDeleteRole({{ $role->id }}, '{{ addslashes($role->name) }}')"
                                    title="Delete Role">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="role-card-title mb-0">{{ ucfirst($role->name) }}</h3>
                        @if($isSystemRole)
                            <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.7rem; font-weight: 700;">System</span>
                        @else
                            <span class="badge" style="background: #eef2ff; color: #4f46e5; font-size: 0.7rem; font-weight: 700;">Custom</span>
                        @endif
                    </div>

                    <p class="role-card-desc">
                        @if($role->name === 'admin')
                            Akses penuh sistem dengan seluruh permission aktif. Hak istimewa untuk menambah role, memberi dan mencabut permission.
                        @elseif($role->name === 'user')
                            Pengguna standar aplikasi dengan akses melihat dan memperbarui profil pribadi.
                        @else
                            Role kustom dengan permission yang dapat disesuaikan.
                        @endif
                    </p>

                    <div class="role-card-meta">
                        <span class="role-card-badge"><i class="bi bi-people-fill"></i> {{ $role->users_count }} Users</span>
                        <span class="role-card-badge"><i class="bi bi-key-fill"></i> {{ $role->permissions->count() }} Permissions</span>
                    </div>

                    <div class="role-card-permissions mt-auto">
                        <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                            <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Assigned Permissions:</span>
                            <button class="btn btn-sm btn-link p-0 text-decoration-none btn-quick-give"
                                data-id="{{ $role->id }}" data-name="{{ $role->name }}"
                                style="font-size: 0.75rem; color: #e11d74; font-weight: 700;">
                                <i class="bi bi-plus"></i> Give
                            </button>
                        </div>
                        <div class="d-flex flex-wrap gap-1" id="role-permissions-list-{{ $role->id }}">
                            @forelse($role->permissions->take(8) as $perm)
                                <span class="permission-tag permission-tag-granted d-inline-flex align-items-center gap-1">
                                    {{ $perm->name }}
                                    <button type="button" class="btn-close-perm"
                                        onclick="removePermission({{ $role->id }}, '{{ $perm->name }}', '{{ $role->name }}')"
                                        title="Cabut permission {{ $perm->name }}">
                                        &times;
                                    </button>
                                </span>
                            @empty
                                <span class="text-muted" style="font-size: 0.8rem;">Belum ada permission.</span>
                            @endforelse

                            @if($role->permissions->count() > 8)
                                <span class="permission-tag permission-tag-more"
                                    style="cursor: pointer;"
                                    onclick="openEditRoleModal({{ $role->id }})">
                                    +{{ $role->permissions->count() - 8 }} more
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Add New Role Card --}}
        <div class="col-lg-4 col-md-6">
            <div class="card role-card role-card-add h-100" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                <div class="role-card-add-content text-center py-5">
                    <div class="role-card-add-icon mb-3">
                        <i class="bi bi-plus-lg" style="font-size: 1.75rem; color: #94a3b8;"></i>
                    </div>
                    <h3 class="role-card-add-title" style="font-size: 1.1rem; font-weight: 800; color: #0f172a;">Add New Role</h3>
                    <p class="role-card-add-desc" style="font-size: 0.85rem; color: #64748b;">Create a custom role with specific permissions</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Create Role Modal --}}
    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-spark">
                <div class="modal-header modal-header-spark">
                    <div class="modal-header-icon">
                        <i class="bi bi-shield-plus"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="createRoleModalLabel">Create New Role</h5>
                        <p class="modal-subtitle">Define a new role and assign its initial permissions.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-spark" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-spark">
                    <form id="form-create-role">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label-spark" for="create-role-name">Role Name <span class="text-danger">*</span></label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-shield input-icon"></i>
                                    <input type="text" class="form-control form-control-spark" id="create-role-name"
                                        name="name" placeholder="e.g. editor, moderator" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label-spark">Assign Permissions</label>
                                <div class="permissions-checkbox-grid">
                                    @foreach($groupedPermissions as $groupName => $perms)
                                        <div class="permission-group">
                                            <h6 class="permission-group-title">{{ $groupName }}</h6>
                                            @foreach($perms as $p)
                                                <label class="permission-checkbox">
                                                    <input type="checkbox" name="permissions[]" value="{{ $p->name }}">
                                                    <span>{{ $p->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-spark">
                    <button type="button" class="btn-secondary-spark" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-primary-spark" id="btn-save-role">
                        <i class="bi bi-check-lg"></i> Create Role
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Role Modal --}}
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-spark">
                <div class="modal-header modal-header-spark">
                    <div class="modal-header-icon">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="editRoleModalLabel">Edit Role & Permissions</h5>
                        <p class="modal-subtitle">Modify role details and update assigned permissions.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-spark" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-spark">
                    <form id="form-edit-role">
                        @csrf
                        <input type="hidden" id="edit-role-id">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label-spark" for="edit-role-name">Role Name <span class="text-danger">*</span></label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-shield input-icon"></i>
                                    <input type="text" class="form-control form-control-spark" id="edit-role-name"
                                        name="name" placeholder="Role name" required>
                                </div>
                                <small class="text-muted" id="edit-role-notice"></small>
                            </div>
                            <div class="col-12">
                                <label class="form-label-spark">Manage Permissions (Check to Give, Uncheck to Remove)</label>
                                <div class="permissions-checkbox-grid">
                                    @foreach($groupedPermissions as $groupName => $perms)
                                        <div class="permission-group">
                                            <h6 class="permission-group-title">{{ $groupName }}</h6>
                                            @foreach($perms as $p)
                                                <label class="permission-checkbox">
                                                    <input type="checkbox" name="permissions[]" value="{{ $p->name }}"
                                                        id="edit-perm-{{ str_replace('.', '-', $p->name) }}">
                                                    <span>{{ $p->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-spark">
                    <button type="button" class="btn-secondary-spark" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-primary-spark" id="btn-update-role">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Give Permission Modal --}}
    <div class="modal fade" id="givePermissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-spark">
                <div class="modal-header modal-header-spark">
                    <div class="modal-header-icon">
                        <i class="bi bi-key-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title">Give Permission</h5>
                        <p class="modal-subtitle" id="give-perm-subtitle">Berikan permission ke role</p>
                    </div>
                    <button type="button" class="btn-close btn-close-spark" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-spark">
                    <form id="form-give-permission">
                        @csrf
                        <input type="hidden" id="give-role-id">
                        <div class="mb-3">
                            <label class="form-label-spark">Pilih Permission</label>
                            <select class="form-select form-select-spark" id="give-permission-select" required>
                                <option value="" selected disabled>-- Pilih Permission --</option>
                                @foreach($permissions as $p)
                                    <option value="{{ $p->name }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-spark">
                    <button type="button" class="btn-secondary-spark" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn-primary-spark" id="btn-confirm-give-perm">
                        <i class="bi bi-check-lg"></i> Berikan Permission
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/crud.css') }}">
    <style>
        .btn-close-perm {
            background: none;
            border: none;
            padding: 0 0 0 4px;
            font-size: 1rem;
            line-height: 1;
            color: #ef4444;
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.15s ease;
        }
        .btn-close-perm:hover {
            opacity: 1;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || '{{ csrf_token() }}';

        // 1. Submit Create Role
        document.getElementById('btn-save-role')?.addEventListener('click', async function () {
            const form = document.getElementById('form-create-role');
            const formData = new FormData(form);

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creating...';

            try {
                const response = await fetch("{{ route('roles.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    let errorMsg = data.message || 'Gagal membuat role.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menambah Role',
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
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Create Role';
            }
        });

        // 2. Open Edit Role Modal
        function openEditRoleModal(roleId) {
            const btn = document.querySelector(`.btn-edit-role[data-id="${roleId}"]`);
            if (btn) btn.click();
        }

        document.querySelectorAll('.btn-edit-role').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const permissions = JSON.parse(this.getAttribute('data-permissions') || '[]');

                document.getElementById('edit-role-id').value = id;
                document.getElementById('edit-role-name').value = name;

                const nameInput = document.getElementById('edit-role-name');
                const notice = document.getElementById('edit-role-notice');
                if (name === 'admin' || name === 'user') {
                    nameInput.readOnly = true;
                    notice.innerText = 'Nama role sistem bawaan tidak dapat diubah.';
                } else {
                    nameInput.readOnly = false;
                    notice.innerText = '';
                }

                // Reset all checkboxes
                document.querySelectorAll('#form-edit-role input[type="checkbox"]').forEach(cb => {
                    cb.checked = permissions.includes(cb.value);
                });

                const editModal = new bootstrap.Modal(document.getElementById('editRoleModal'));
                editModal.show();
            });
        });

        // 3. Submit Update Role
        document.getElementById('btn-update-role')?.addEventListener('click', async function () {
            const roleId = document.getElementById('edit-role-id').value;
            const form = document.getElementById('form-edit-role');
            const formData = new FormData(form);
            formData.append('_method', 'PUT');

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

            try {
                const response = await fetch(`/roles/${roleId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    let errorMsg = data.message || 'Gagal memperbarui role.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memperbarui Role',
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
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Save Changes';
            }
        });

        // 4. Quick Give Permission Modal Trigger
        document.querySelectorAll('.btn-quick-give').forEach(btn => {
            btn.addEventListener('click', function () {
                const roleId = this.getAttribute('data-id');
                const roleName = this.getAttribute('data-name');

                document.getElementById('give-role-id').value = roleId;
                document.getElementById('give-perm-subtitle').innerText = `Berikan permission baru untuk role: ${roleName}`;
                document.getElementById('give-permission-select').value = '';

                const giveModal = new bootstrap.Modal(document.getElementById('givePermissionModal'));
                giveModal.show();
            });
        });

        // 5. Submit Give Permission
        document.getElementById('btn-confirm-give-perm')?.addEventListener('click', async function () {
            const roleId = document.getElementById('give-role-id').value;
            const permission = document.getElementById('give-permission-select').value;

            if (!permission) {
                Swal.fire({ icon: 'warning', text: 'Silakan pilih permission terlebih dahulu.' });
                return;
            }

            try {
                const response = await fetch(`/roles/${roleId}/give-permission`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ permission })
                });

                const data = await response.json();

                if (!response.ok) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 1200,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Kesalahan Sistem', text: err.message });
            }
        });

        // 6. Remove Permission directly
        async function removePermission(roleId, permissionName, roleName) {
            Swal.fire({
                title: 'Cabut Permission?',
                html: `Apakah Anda yakin ingin mencabut permission <code>${permissionName}</code> dari role <strong>${roleName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-x-circle"></i> Ya, Cabut',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch(`/roles/${roleId}/remove-permission`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ permission: permissionName })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Dicabut!',
                                text: data.message,
                                timer: 1200,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    } catch (err) {
                        Swal.fire({ icon: 'error', title: 'Kesalahan Sistem', text: err.message });
                    }
                }
            });
        }

        // 7. Delete Role
        function confirmDeleteRole(roleId, roleName) {
            Swal.fire({
                title: 'Hapus Role?',
                html: `Apakah Anda yakin ingin menghapus role <strong>${roleName}</strong>?<br>Semua user dengan role ini akan kehilangan role tersebut.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash3"></i> Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch(`/roles/${roleId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: data.message,
                                timer: 1200,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    } catch (err) {
                        Swal.fire({ icon: 'error', title: 'Kesalahan Sistem', text: err.message });
                    }
                }
            });
        }
    </script>
@endpush
