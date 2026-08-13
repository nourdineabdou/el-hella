@php
$user = auth()->user();
@endphp

<aside class="eh-sidebar">
    <a href="{{ $user->isAdmin() ? route('admin.dashboard') : route('distributor.dashboard') }}" class="eh-brand">
        <img src="{{ asset('el-hila.png') }}" alt="EL HELLA">
    </a>

    <nav class="nav flex-column py-3">
        @if ($user->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>{{ __('menu.dashboard') }}</span>
            </a>
            <a href="{{ route('admin.visits.index') }}" class="nav-link {{ request()->routeIs('admin.visits.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i>
                <span>{{ __('menu.visits') }}</span>
            </a>
            <a href="{{ route('admin.shops.index') }}" class="nav-link {{ request()->routeIs('admin.shops.*') ? 'active' : '' }}">
                <i class="bi bi-shop"></i>
                <span>{{ __('menu.shops') }}</span>
            </a>
            <a href="{{ route('admin.map.index') }}" class="nav-link {{ request()->routeIs('admin.map.*') ? 'active' : '' }}">
                <i class="bi bi-map"></i>
                <span>{{ __('menu.map') }}</span>
            </a>
            <a href="{{ route('admin.gps-alerts.index') }}" class="nav-link {{ request()->routeIs('admin.gps-alerts.*') ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle"></i>
                <span>{{ __('menu.gps_alerts') }}</span>
            </a>
            <a href="{{ route('admin.goals.index') }}" class="nav-link {{ request()->routeIs('admin.goals.*') ? 'active' : '' }}">
                <i class="bi bi-bullseye"></i>
                <span>{{ __('menu.goals') }}</span>
            </a>
            <a href="{{ route('admin.distributors.index') }}" class="nav-link {{ request()->routeIs('admin.distributors.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>{{ __('menu.distributors') }}</span>
            </a>
            <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i>
                <span>{{ __('menu.products') }}</span>
            </a>
            <a href="{{ route('admin.products-sold.index') }}" class="nav-link {{ request()->routeIs('admin.products-sold.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i>
                <span>{{ __('menu.products_sold') }}</span>
            </a>
            <a href="{{ route('admin.admins.index') }}" class="nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i>
                <span>{{ __('menu.admins') }}</span>
            </a>
        @else
            <a href="{{ route('distributor.dashboard') }}" class="nav-link {{ request()->routeIs('distributor.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>{{ __('menu.dashboard') }}</span>
            </a>
            <a href="{{ route('distributor.visits.index') }}" class="nav-link {{ request()->routeIs('distributor.visits.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i>
                <span>{{ __('menu.visits') }}</span>
            </a>
            <a href="{{ route('distributor.shops.index') }}" class="nav-link {{ request()->routeIs('distributor.shops.*') ? 'active' : '' }}">
                <i class="bi bi-shop"></i>
                <span>{{ __('menu.shops') }}</span>
            </a>
            <a href="{{ route('distributor.goals.index') }}" class="nav-link {{ request()->routeIs('distributor.goals.*') ? 'active' : '' }}">
                <i class="bi bi-bullseye"></i>
                <span>{{ __('menu.goals') }}</span>
            </a>
        @endif

        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
            <i class="bi bi-person-circle"></i>
            <span>{{ __('menu.profile') }}</span>
        </a>
    </nav>
</aside>
