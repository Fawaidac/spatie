<div class="sidebar-wrapper" id="sidebar">
    {{-- Brand Logo / Identity & Mobile Close Button --}}
    <div class="sidebar-header-box d-flex align-items-center justify-content-between mb-4">
        <a href="{{ route('dashboard') }}" class="sidebar-brand m-0">
            <i class="bi bi-asterisk"></i>
            <span>Spark Admin</span>
        </a>
        <button type="button" class="sidebar-close-btn d-flex d-xl-none align-items-center justify-content-center" id="sidebar-close-btn" aria-label="Close Sidebar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    {{-- Navigation Menu --}}
    <div class="flex-grow-1 overflow-y-auto">
        {{-- Group: Menu --}}
        <div class="sidebar-menu-section">
            <div class="sidebar-menu-title">Menu</div>
            <ul class="sidebar-menu-list">
                <li class="sidebar-menu-item">
                    <a href="{{ route('dashboard') }}"
                        class="sidebar-menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        id="menu-overview" title="Overview">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Group: Components --}}
        @canany(['users.view', 'roles.view', 'permissions.view'])
        <div class="sidebar-menu-section">
            <div class="sidebar-menu-title">Management Users</div>
            <ul class="sidebar-menu-list">
                @can('users.view')
                <li class="sidebar-menu-item">
                    <a href="{{ route('users.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                        id="menu-users" title="Users">
                        <i class="bi bi-people"></i>
                        <span>Users</span>
                    </a>
                </li>
                @endcan
                @can('roles.view')
                <li class="sidebar-menu-item">
                    <a href="{{ route('roles.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                        id="menu-roles" title="Roles">
                        <i class="bi bi-shield"></i>
                        <span>Roles</span>
                    </a>
                </li>
                @endcan
                @can('permissions.view')
                <li class="sidebar-menu-item">
                    <a href="{{ route('permissions.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}"
                        id="menu-permissions" title="Permissions">
                        <i class="bi bi-shield-lock"></i>
                        <span>Permissions</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        @endcanany

        {{-- Group: Setting --}}
        @can('profile.view')
        <div class="sidebar-menu-section">
            <div class="sidebar-menu-title">Setting</div>
            <ul class="sidebar-menu-list">
                <li class="sidebar-menu-item">
                    <a href="{{ route('settings.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"
                        id="menu-setting" title="Setting">
                        <i class="bi bi-gear"></i>
                        <span>Setting</span>
                    </a>
                </li>
            </ul>
        </div>
        @endcan
    </div>

    {{-- Sidebar Profile Card --}}
    <div class="sidebar-profile">
        <img src="{{ auth()->user()?->avatar ? asset(auth()->user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()?->name ?? 'Admin') . '&background=e11d74&color=fff' }}"
            alt="{{ auth()->user()?->name ?? 'User' }}" class="sidebar-profile-img">
        <div class="sidebar-profile-info">
            <div class="sidebar-profile-name">{{ auth()->user()->name ?? 'Administrator' }}</div>
            <div class="sidebar-profile-email">
                <span class="badge" style="background: rgba(255,255,255,0.2); font-size: 0.68rem; font-weight: 700; color: #fff;">
                    {{ ucfirst(auth()->user()?->getRoleNames()->first() ?? 'User') }}
                </span>
            </div>
        </div>
    </div>
</div>

