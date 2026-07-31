@php
$locales = ['ar' => 'العربية', 'fr' => 'Français'];
$current = app()->getLocale();
@endphp

<div class="dropdown">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-translate"></i>
        <span>{{ $locales[$current] ?? $current }}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @foreach ($locales as $code => $label)
            <li>
                <a class="dropdown-item {{ $current === $code ? 'active' : '' }}" href="{{ route('lang.switch', $code) }}">
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
