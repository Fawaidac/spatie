@extends('layouts.app')

@section('title', 'Permissions Management - Spark Admin')
@section('meta_description', 'Manage permissions - Spark Admin Dashboard')

@section('content')

    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Permissions Management</h1>
            <p class="page-subtitle">Define granular permissions to control access across your application.</p>
        </div>
        <button class="btn-primary-spark" type="button" data-bs-toggle="modal" data-bs-target="#createPermissionModal"
            id="btn-create-permission">
            <i class="bi bi-plus-lg"></i>
            <span>Add Permission</span>
        </button>
    </div>

    {{-- Permissions Table Card --}}
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">All Permissions</h2>
            <form method="GET" action="{{ route('permissions.index') }}" class="d-flex flex-wrap gap-2 align-items-center" id="filter-perm-form">
                {{-- Search Input --}}
                <div class="table-search-wrapper">
                    <i class="bi bi-search table-search-icon"></i>
                    <input type="text" name="search" class="table-search-input" placeholder="Search permissions..."
                        value="{{ request('search') }}" id="search-permissions">
                </div>

                {{-- Retain active group when searching --}}
                @if(request('group') && request('group') !== 'all')
                    <input type="hidden" name="group" value="{{ request('group') }}">
                @endif

                {{-- Group Filter Dropdown --}}
                <div class="dropdown">
                    <button class="btn-filter-spark dropdown-toggle {{ request('group') && request('group') !== 'all' ? 'has-filter' : '' }}" 
                        type="button" data-bs-toggle="dropdown" aria-expanded="false" id="btn-filter-permissions">
                        <i class="bi bi-funnel"></i>
                        <span>Group: {{ request('group') && request('group') !== 'all' ? ucfirst(request('group')) : 'All' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                        <li><h6 class="dropdown-header text-uppercase text-muted" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em;">Filter By Group</h6></li>
                        <li><a class="dropdown-item {{ !request('group') || request('group') === 'all' ? 'active' : '' }}"
                                href="{{ request()->fullUrlWithQuery(['group' => 'all', 'page' => 1]) }}">All Groups</a></li>
                        @foreach($groups as $grp)
                            <li><a class="dropdown-item {{ request('group') === $grp ? 'active' : '' }}"
                                    href="{{ request()->fullUrlWithQuery(['group' => $grp, 'page' => 1]) }}">
                                    <span>{{ ucfirst($grp) }}</span>
                                    <span class="badge bg-primary-soft text-primary rounded-pill px-2">{{ $grp }}</span>
                                </a></li>
                        @endforeach
                    </ul>
                </div>

                {{-- Reset Filters Button --}}
                @if(request('search') || (request('group') && request('group') !== 'all'))
                    <a href="{{ route('permissions.index') }}" class="btn-filter-reset" title="Reset all filters">
                        <i class="bi bi-x-circle"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-spark" id="permissions-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="28%">Permission Name</th>
                        <th width="18%">Group</th>
                        <th width="12%">Guard</th>
                        <th width="18%">Assigned Roles</th>
                        <th width="11%">Created</th>
                        <th width="8%" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $index => $perm)
                        @php
                            $groupName = explode('.', $perm->name)[0] ?? 'general';
                        @endphp
                        <tr>
                            <td>{{ $permissions->firstItem() + $index }}</td>
                            <td>
                                <div class="permission-name-cell">
                                    <i class="bi bi-key-fill text-primary"></i>
                                    <code class="permission-code">{{ $perm->name }}</code>
                                </div>
                            </td>
                            <td>
                                <span class="group-badge group-badge-users">{{ ucfirst($groupName) }}</span>
                            </td>
                            <td><span class="guard-badge">{{ $perm->guard_name }}</span></td>
                            <td>
                                <div class="role-tags">
                                    @forelse($perm->roles as $role)
                                        <span class="role-tag {{ $role->name === 'admin' ? 'bg-primary text-white' : '' }}">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @empty
                                        <span class="text-muted" style="font-size: 0.75rem;">None</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>{{ $perm->created_at ? $perm->created_at->format('M d, Y') : '-' }}</td>
                            <td class="text-center">
                                <div class="action-btn-group">
                                    <button class="btn-action btn-action-edit btn-edit-permission"
                                        data-id="{{ $perm->id }}"
                                        data-name="{{ $perm->name }}"
                                        data-guard="{{ $perm->guard_name }}"
                                        data-roles='@json($perm->roles->pluck("name"))'
                                        title="Edit Permission">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn-action btn-action-delete"
                                        onclick="confirmDeletePermission({{ $perm->id }}, '{{ addslashes($perm->name) }}')"
                                        title="Delete Permission">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Tidak ada permission yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Custom Spark Pagination --}}
        {{ $permissions->links('partials.pagination') }}
    </div>

    {{-- Create Permission Modal --}}
    <div class="modal fade" id="createPermissionModal" tabindex="-1" aria-labelledby="createPermissionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-spark">
                <div class="modal-header modal-header-spark">
                    <div class="modal-header-icon">
                        <i class="bi bi-key-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="createPermissionModalLabel">Create Permission</h5>
                        <p class="modal-subtitle">Add a new granular permission to the system.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-spark" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-spark">
                    <form id="form-create-permission">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label-spark" for="create-permission-name">Permission Name <span class="text-danger">*</span></label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-key input-icon"></i>
                                    <input type="text" class="form-control form-control-spark"
                                        id="create-permission-name" name="name" placeholder="e.g. users.export, posts.delete" required>
                                </div>
                                <small class="form-text-spark">Gunakan dot notation: modul.aksi (contoh: users.create, roles.edit)</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label-spark" for="create-permission-guard">Guard Name</label>
                                <select class="form-select form-select-spark" id="create-permission-guard" name="guard_name">
                                    <option value="web" selected>web</option>
                                    <option value="api">api</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label-spark">Berikan Langsung ke Role (Opsional)</label>
                                <div class="d-flex gap-3">
                                    @foreach($roles as $role)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="roles[]"
                                                value="{{ $role->name }}" id="create-role-{{ $role->name }}"
                                                {{ $role->name === 'admin' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="create-role-{{ $role->name }}">
                                                {{ ucfirst($role->name) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-spark">
                    <button type="button" class="btn-secondary-spark" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-primary-spark" id="btn-save-permission">
                        <i class="bi bi-check-lg"></i> Create Permission
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Permission Modal --}}
    <div class="modal fade" id="editPermissionModal" tabindex="-1" aria-labelledby="editPermissionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-spark">
                <div class="modal-header modal-header-spark">
                    <div class="modal-header-icon">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="editPermissionModalLabel">Edit Permission</h5>
                        <p class="modal-subtitle">Modify the permission details and assigned roles.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-spark" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-spark">
                    <form id="form-edit-permission">
                        @csrf
                        <input type="hidden" id="edit-permission-id">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label-spark" for="edit-permission-name">Permission Name <span class="text-danger">*</span></label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-key input-icon"></i>
                                    <input type="text" class="form-control form-control-spark"
                                        id="edit-permission-name" name="name" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label-spark" for="edit-permission-guard">Guard Name</label>
                                <select class="form-select form-select-spark" id="edit-permission-guard" name="guard_name">
                                    <option value="web">web</option>
                                    <option value="api">api</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label-spark">Assigned Roles</label>
                                <div class="d-flex gap-3">
                                    @foreach($roles as $role)
                                        <div class="form-check">
                                            <input class="form-check-input edit-perm-role-cb" type="checkbox" name="roles[]"
                                                value="{{ $role->name }}" id="edit-role-{{ $role->name }}">
                                            <label class="form-check-label" for="edit-role-{{ $role->name }}">
                                                {{ ucfirst($role->name) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-spark">
                    <button type="button" class="btn-secondary-spark" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-primary-spark" id="btn-update-permission">
                        <i class="bi bi-check-lg"></i> Update Permission
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

        // 1. Submit Create Permission
        document.getElementById('btn-save-permission')?.addEventListener('click', async function () {
            const form = document.getElementById('form-create-permission');
            const formData = new FormData(form);

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

            try {
                const response = await fetch("{{ route('permissions.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    let errorMsg = data.message || 'Gagal menambahkan permission.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: errorMsg,
                        confirmButtonColor: '#DB7093'
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
                    confirmButtonColor: '#DB7093'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Create Permission';
            }
        });

        // 2. Open Edit Permission Modal (Event Delegation for Live Search)
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-edit-permission');
            if (!btn) return;

            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const guard = btn.getAttribute('data-guard');
            const roles = JSON.parse(btn.getAttribute('data-roles') || '[]');

            document.getElementById('edit-permission-id').value = id;
            document.getElementById('edit-permission-name').value = name;
            document.getElementById('edit-permission-guard').value = guard;

            document.querySelectorAll('.edit-perm-role-cb').forEach(cb => {
                cb.checked = roles.includes(cb.value);
            });

            const editModal = new bootstrap.Modal(document.getElementById('editPermissionModal'));
            editModal.show();
        });

        // 3. Submit Update Permission
        document.getElementById('btn-update-permission')?.addEventListener('click', async function () {
            const permId = document.getElementById('edit-permission-id').value;
            const form = document.getElementById('form-edit-permission');
            const formData = new FormData(form);
            formData.append('_method', 'PUT');

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating...';

            try {
                const response = await fetch(`/permissions/${permId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    let errorMsg = data.message || 'Gagal memperbarui permission.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: errorMsg,
                        confirmButtonColor: '#DB7093'
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
                    confirmButtonColor: '#DB7093'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Update Permission';
            }
        });

        // 4. Delete Permission Confirmation
        function confirmDeletePermission(permId, permName) {
            Swal.fire({
                title: 'Hapus Permission?',
                html: `Apakah Anda yakin ingin menghapus permission <code>${permName}</code>?<br>Permission ini akan dicabut dari seluruh role.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash3"></i> Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch(`/permissions/${permId}`, {
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
