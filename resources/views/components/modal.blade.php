@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
])

@php
$maxWidthClass = [
    'sm' => 'modal-sm',
    'md' => '',
    'lg' => 'modal-lg',
    'xl' => 'modal-xl',
    '2xl' => 'modal-xl',
][$maxWidth] ?? '';
@endphp

<div class="modal fade" id="{{ $name }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered {{ $maxWidthClass }}">
        <div class="modal-content">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            <div class="modal-body p-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

@if ($show)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById(@json($name));
            if (modalEl) {
                new bootstrap.Modal(modalEl).show();
            }
        });
    </script>
@endif
