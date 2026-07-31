<header class="eh-topbar">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-outline-secondary d-lg-none eh-sidebar-toggle" type="button" aria-label="{{ __('menu.toggle_sidebar') }}">
            <i class="bi bi-list fs-5"></i>
        </button>
    </div>

    <div class="d-flex align-items-center gap-2">
        <x-language-switcher />

        <div class="dropdown">
            <button class="btn btn-sm btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-5"></i>
                <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear me-2"></i>{{ __('menu.profile') }}</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="bi bi-box-arrow-right me-2"></i>{{ __('menu.logout') }}
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
