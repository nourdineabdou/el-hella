<x-app-layout>
    <x-slot name="header">{{ __('admin.map_title') }}</x-slot>

    @vite(['resources/js/admin-map.js'])

    <script>
        window.adminMapData = {
            shops: @json($shops),
            distributors: @json($distributors),
            labels: {
                shops: @json(__('admin.shops_title')),
                distributors: @json(__('menu.distributors')),
                lastSeen: @json(__('admin.last_seen_at')),
                lastVisitAt: @json(__('admin.last_visit_at')),
            },
        };
    </script>

    <div class="card border-0 shadow-sm p-2 p-md-3">
        @if ($shops->isEmpty() && $distributors->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-map fs-1 d-block mb-2"></i>
                {{ __('admin.map_empty') }}
            </div>
        @else
            <div id="admin-map" class="eh-map"></div>
        @endif
    </div>

    <div class="d-flex flex-wrap gap-3 mt-3 small text-muted">
        <div class="d-flex align-items-center gap-2">
            <span class="eh-map-legend-dot eh-map-legend-shop"></span>
            {{ __('admin.shops_title') }} ({{ $shops->count() }})
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="eh-map-legend-dot eh-map-legend-distributor"></span>
            {{ __('menu.distributors') }} ({{ $distributors->count() }})
        </div>
    </div>
</x-app-layout>
