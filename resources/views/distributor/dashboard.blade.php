<x-app-layout>
    <x-slot name="header">{{ __('menu.dashboard') }}</x-slot>

    <p class="text-muted mb-4">{{ __('dashboard.distributor_welcome', ['name' => auth()->user()->name]) }}</p>

    @unless ($distributor)
        <div class="alert alert-warning">
            {{ __('dashboard.no_distributor_profile') }}
        </div>
    @endunless

    @php $todayFilterUrl = route('distributor.visits.index', ['date' => today()->toDateString()]); @endphp

    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <a href="{{ $todayFilterUrl }}" class="card eh-stat-card h-100 text-reset text-decoration-none d-block">
                <div class="card-body">
                    <div class="text-muted small">{{ __('dashboard.today_visits') }}</div>
                    <div class="fs-4 fw-bold">{{ $todayVisits }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ $todayFilterUrl }}" class="card eh-stat-card h-100 text-reset text-decoration-none d-block">
                <div class="card-body">
                    <div class="text-muted small">{{ __('dashboard.today_distributions') }}</div>
                    <div class="fs-4 fw-bold">{{ $todayDistributions }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ $todayFilterUrl }}" class="card eh-stat-card h-100 text-reset text-decoration-none d-block">
                <div class="card-body">
                    <div class="text-muted small">{{ __('dashboard.today_quantity') }}</div>
                    <div class="fs-4 fw-bold">{{ rtrim(rtrim(number_format($todayQuantity, 3), '0'), '.') ?: 0 }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('distributor.goals.index', ['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]) }}" class="card eh-stat-card h-100 text-reset text-decoration-none d-block">
                <div class="card-body">
                    <div class="text-muted small">{{ __('dashboard.completion_rate') }}</div>
                    <div class="fs-4 fw-bold">{{ $completionRate }}%</div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-md-6">
            <a href="{{ route('distributor.shops.index') }}" class="btn btn-primary w-100 eh-action-btn">
                <i class="bi bi-shop me-2"></i>{{ __('Shop management') }}
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="{{ route('distributor.shops.index') }}" class="btn btn-outline-primary w-100 eh-action-btn">
                <i class="bi bi-search me-2"></i>{{ __('Search a shop') }}
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-0 pt-3 d-flex align-items-center justify-content-between">
            <h2 class="h6 mb-0">{{ __('dashboard.recent_visits') }}</h2>
            <a href="{{ route('distributor.visits.index') }}" class="small fw-semibold text-decoration-none">{{ __('admin.view_all') }}</a>
        </div>
        <div class="card-body p-0">
            @if ($recentVisits->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-geo-alt fs-1 d-block mb-2"></i>
                    {{ __('dashboard.no_visits_yet') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-mobile-cards">
                        <thead>
                            <tr>
                                <th>{{ __('dashboard.table_shop') }}</th>
                                <th>{{ __('dashboard.table_type') }}</th>
                                <th>{{ __('admin.table_quantity') }}</th>
                                <th>{{ __('dashboard.table_date') }}</th>
                                <th>{{ __('dashboard.table_status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentVisits as $visit)
                                <tr class="eh-clickable-row" data-bs-toggle="modal" data-bs-target="#dash-visit-details-{{ $visit->id }}">
                                    <td class="fw-semibold" data-label="{{ __('dashboard.table_shop') }}">{{ $visit->shop?->name }}</td>
                                    <td data-label="{{ __('dashboard.table_type') }}">
                                        @if ($visit->visit_type === 'distribution')
                                            <span class="badge bg-success-subtle text-success">{{ __('dashboard.visit_type_sale') }}</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ __('dashboard.visit_type_visit_only') }}</span>
                                        @endif
                                    </td>
                                    <td data-label="{{ __('admin.table_quantity') }}">
                                        @if ($visit->distribution)
                                            {{ rtrim(rtrim(number_format((float) $visit->distribution->total_quantity, 3), '0'), '.') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-muted small" data-label="{{ __('dashboard.table_date') }}">{{ $visit->visited_at?->format('d/m/Y H:i') }}</td>
                                    <td data-label="{{ __('dashboard.table_status') }}">
                                        @if ($visit->is_within_allowed_distance)
                                            <span class="badge bg-success-subtle text-success">{{ __('dashboard.gps_ok') }}</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">{{ __('dashboard.gps_alert') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @foreach ($recentVisits as $visit)
                    <div class="modal fade" id="dash-visit-details-{{ $visit->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('admin.visit_details') }} — {{ $visit->shop?->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                </div>
                                <div class="modal-body">
                                    <dl class="row small mb-3">
                                        <dt class="col-5 text-muted fw-normal">{{ __('dashboard.table_date') }}</dt>
                                        <dd class="col-7 mb-1">{{ $visit->visited_at?->format('d/m/Y H:i') }}</dd>

                                        <dt class="col-5 text-muted fw-normal">{{ __('dashboard.table_status') }}</dt>
                                        <dd class="col-7 mb-1">
                                            @if ($visit->is_within_allowed_distance)
                                                <span class="badge bg-success-subtle text-success">{{ __('dashboard.gps_ok') }}</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">{{ __('dashboard.gps_alert') }}</span>
                                            @endif
                                        </dd>

                                        @if ($visit->visit_type !== 'distribution' && $visit->without_distribution_reason)
                                            <dt class="col-5 text-muted fw-normal">{{ __('admin.reason_label') }}</dt>
                                            <dd class="col-7 mb-1">{{ __('admin.reason_'.$visit->without_distribution_reason) }}</dd>
                                        @endif

                                        @if ($visit->observation)
                                            <dt class="col-5 text-muted fw-normal">{{ __('admin.observation_label') }}</dt>
                                            <dd class="col-7 mb-1">{{ $visit->observation }}</dd>
                                        @endif
                                    </dl>

                                    @if ($visit->distribution)
                                        <hr>
                                        <h6 class="small fw-bold mb-2">{{ __('admin.sale_details') }}</h6>
                                        @if ($visit->distribution->items->isEmpty())
                                            <p class="text-muted text-center mb-0">{{ __('admin.no_items') }}</p>
                                        @else
                                            <table class="table table-sm align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('admin.table_product') }}</th>
                                                        <th class="text-end">{{ __('admin.table_quantity') }}</th>
                                                        <th class="text-end">{{ __('admin.table_unit') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($visit->distribution->items as $item)
                                                        <tr>
                                                            <td>{{ app()->getLocale() === 'fr' && $item->product?->name_fr ? $item->product->name_fr : $item->product?->name_ar }}</td>
                                                            <td class="text-end">{{ rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.') }}</td>
                                                            <td class="text-end text-muted">{{ $item->unit }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>
