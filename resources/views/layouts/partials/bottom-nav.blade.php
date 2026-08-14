@php $user = auth()->user(); @endphp

<nav class="eh-bottom-nav d-lg-none">
    @if ($user->isAdmin())
        <a href="{{ route('admin.dashboard') }}" class="eh-bottom-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>{{ __('menu.dashboard') }}</span>
        </a>
        <a href="{{ route('admin.visits.index') }}" class="eh-bottom-nav-item {{ request()->routeIs('admin.visits.*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt"></i>
            <span>{{ __('menu.visits') }}</span>
        </a>
        <a href="{{ route('admin.shops.index') }}" class="eh-bottom-nav-item {{ request()->routeIs('admin.shops.*') ? 'active' : '' }}">
            <i class="bi bi-shop"></i>
            <span>{{ __('menu.shops') }}</span>
        </a>
        <a href="{{ route('admin.map.index') }}" class="eh-bottom-nav-item {{ request()->routeIs('admin.map.*') ? 'active' : '' }}">
            <i class="bi bi-map"></i>
            <span>{{ __('menu.map') }}</span>
        </a>
        <a href="{{ route('profile.edit') }}" class="eh-bottom-nav-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
            <i class="bi bi-person-circle"></i>
            <span>{{ __('menu.profile') }}</span>
        </a>
    @else
        <a href="{{ route('distributor.dashboard') }}" class="eh-bottom-nav-item {{ request()->routeIs('distributor.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>{{ __('menu.dashboard') }}</span>
        </a>
        <a href="{{ route('distributor.visits.index') }}" class="eh-bottom-nav-item {{ request()->routeIs('distributor.visits.*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt"></i>
            <span>{{ __('menu.visits') }}</span>
        </a>
        <a href="{{ route('distributor.shops.index') }}" class="eh-bottom-nav-item {{ request()->routeIs('distributor.shops.*') ? 'active' : '' }}">
            <i class="bi bi-shop"></i>
            <span>{{ __('menu.shops') }}</span>
        </a>
        <a href="{{ route('distributor.stock.index') }}" class="eh-bottom-nav-item {{ request()->routeIs('distributor.stock.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i>
            <span>{{ __('admin.my_stock_title') }}</span>
        </a>
        <a href="{{ route('distributor.goals.index') }}" class="eh-bottom-nav-item {{ request()->routeIs('distributor.goals.*') ? 'active' : '' }}">
            <i class="bi bi-bullseye"></i>
            <span>{{ __('menu.goals') }}</span>
        </a>
        <a href="{{ route('profile.edit') }}" class="eh-bottom-nav-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
            <i class="bi bi-person-circle"></i>
            <span>{{ __('menu.profile') }}</span>
        </a>
    @endif
</nav>
