<x-app-layout>
    <x-slot name="header">{{ __('admin.shops_title') }}</x-slot>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-0 pt-3">
            <h2 class="h6 mb-0"><i class="bi bi-trophy text-warning me-2"></i>{{ __('admin.top_shops_title') }}</h2>
        </div>
        <div class="card-body">
            @if ($topShops->isEmpty())
                <p class="text-muted mb-0">{{ __('admin.top_shops_empty') }}</p>
            @else
                <div class="row g-3">
                    @foreach ($topShops as $index => $shop)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="d-flex align-items-center gap-3 border rounded-4 p-3 h-100">
                                <div class="fw-bold fs-5 text-primary" style="width: 2rem;">#{{ $index + 1 }}</div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $shop->name }}</div>
                                    <div class="text-muted small">{{ $shop->owner_name }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">{{ rtrim(rtrim(number_format((float) $shop->total_quantity, 3), '0'), '.') }}</div>
                                    <div class="text-muted small">{{ __('admin.table_total_quantity') }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.shops.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-8">
                    <label class="form-label small fw-semibold">{{ __('admin.search_shop_placeholder') }}</label>
                    <input type="search" name="q" value="{{ request('q') }}" inputmode="search" enterkeyhint="search" autocomplete="off" class="form-control" placeholder="{{ __('admin.search_shop_placeholder') }}">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">{{ __('admin.filter_apply') }}</button>
                    <a href="{{ route('admin.shops.index') }}" class="btn btn-outline-secondary" title="{{ __('admin.filter_reset') }}">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if ($shops->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-shop fs-1 d-block mb-2"></i>
                    {{ __('admin.no_shops_found') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-mobile-cards">
                        <thead>
                            <tr>
                                <th>{{ __('dashboard.table_shop') }}</th>
                                <th>{{ __('dashboard.table_owner') }}</th>
                                <th>{{ __('dashboard.table_phone') }}</th>
                                <th>{{ __('admin.table_visits_count') }}</th>
                                <th>{{ __('admin.table_total_quantity') }}</th>
                                <th>{{ __('dashboard.table_status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($shops as $shop)
                                <tr>
                                    <td class="fw-semibold" data-label="{{ __('dashboard.table_shop') }}">{{ $shop->name }}</td>
                                    <td data-label="{{ __('dashboard.table_owner') }}">{{ $shop->owner_name }}</td>
                                    <td class="text-muted small" data-label="{{ __('dashboard.table_phone') }}">{{ $shop->phone }}</td>
                                    <td data-label="{{ __('admin.table_visits_count') }}">{{ $shop->visits_count }}</td>
                                    <td data-label="{{ __('admin.table_total_quantity') }}">{{ $shop->total_quantity ? rtrim(rtrim(number_format((float) $shop->total_quantity, 3), '0'), '.') : '—' }}</td>
                                    <td data-label="{{ __('dashboard.table_status') }}">
                                        @if ($shop->is_active)
                                            <span class="badge bg-success-subtle text-success">{{ __('dashboard.active') }}</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ __('dashboard.inactive') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $shops->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
