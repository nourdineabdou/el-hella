<x-app-layout>
    <x-slot name="header">{{ __('menu.dashboard') }}</x-slot>

    <p class="text-muted mb-4">{{ __('dashboard.admin_welcome', ['name' => auth()->user()->name]) }}</p>

    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="card eh-stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="eh-stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="text-muted small">{{ __('dashboard.distributors') }}</div>
                        <div class="fs-4 fw-bold">{{ $distributorsCount }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <a href="{{ route('admin.shops.index') }}" class="card eh-stat-card h-100 text-reset text-decoration-none d-block">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="eh-stat-icon bg-success-subtle text-success">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div>
                        <div class="text-muted small">{{ __('dashboard.shops') }}</div>
                        <div class="fs-4 fw-bold">{{ $shopsCount }}</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="{{ route('admin.visits.index', ['date' => today()->toDateString()]) }}" class="card eh-stat-card h-100 text-reset text-decoration-none d-block">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="eh-stat-icon bg-info-subtle text-info">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <div>
                        <div class="text-muted small">{{ __('dashboard.visits_today') }}</div>
                        <div class="fs-4 fw-bold">{{ $visitsTodayCount }}</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-lg-3">
            <a href="{{ route('admin.gps-alerts.index') }}" class="card eh-stat-card h-100 text-reset text-decoration-none d-block">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="eh-stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="text-muted small">{{ __('dashboard.gps_alerts') }}</div>
                        <div class="fs-4 fw-bold">{{ $gpsAlertsCount }}</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h2 class="h6 mb-0">{{ __('dashboard.recent_visits') }}</h2>
                </div>
                <div class="card-body p-0">
                    @if ($recentVisits->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-geo-alt fs-1 d-block mb-2"></i>
                            {{ __('dashboard.no_visits_yet') }}
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('dashboard.table_shop') }}</th>
                                        <th>{{ __('dashboard.table_distributor') }}</th>
                                        <th>{{ __('dashboard.table_type') }}</th>
                                        <th>{{ __('dashboard.table_distance') }}</th>
                                        <th>{{ __('dashboard.table_date') }}</th>
                                        <th>{{ __('dashboard.table_status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentVisits as $visit)
                                        <tr class="eh-clickable-row" data-bs-toggle="modal" data-bs-target="#dash-visit-details-{{ $visit->id }}">
                                            <td class="fw-semibold">{{ $visit->shop?->name }}</td>
                                            <td>{{ $visit->distributor?->user?->name }}</td>
                                            <td>
                                                @if ($visit->visit_type === 'distribution')
                                                    <span class="badge bg-success-subtle text-success">{{ __('dashboard.visit_type_sale') }}</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary">{{ __('dashboard.visit_type_visit_only') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $visit->formatted_distance ?? '—' }}</td>
                                            <td class="text-muted small">{{ $visit->visited_at?->format('d/m/Y H:i') }}</td>
                                            <td>
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
                                                <dt class="col-5 text-muted fw-normal">{{ __('dashboard.table_distributor') }}</dt>
                                                <dd class="col-7 mb-1">{{ $visit->distributor?->user?->name }}</dd>

                                                <dt class="col-5 text-muted fw-normal">{{ __('dashboard.table_date') }}</dt>
                                                <dd class="col-7 mb-1">{{ $visit->visited_at?->format('d/m/Y H:i') }}</dd>

                                                <dt class="col-5 text-muted fw-normal">{{ __('dashboard.table_distance') }}</dt>
                                                <dd class="col-7 mb-1">{{ $visit->formatted_distance ?? '—' }}</dd>

                                                @if ($visit->zone)
                                                    <dt class="col-5 text-muted fw-normal">{{ __('admin.zone_label') }}</dt>
                                                    <dd class="col-7 mb-1">{{ $visit->zone }}</dd>
                                                @endif

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
                                            @if ($visit->latitude && $visit->longitude)
                                                @php
                                                    $mapsUrl = 'https://www.google.com/maps/dir/?api=1&destination='.$visit->latitude.','.$visit->longitude;
                                                    $shareText = __('admin.share_visit_location_text', ['shop' => $visit->shop?->name, 'date' => $visit->visited_at?->format('d/m/Y H:i')]);
                                                    if ($visit->zone) {
                                                        $shareText .= ' ('.__('admin.zone_label').': '.$visit->zone.')';
                                                    }
                                                    $shareText .= ' '.$mapsUrl;
                                                @endphp
                                                <a href="https://wa.me/?text={{ urlencode($shareText) }}" target="_blank" rel="noopener" class="btn btn-success">
                                                    <i class="bi bi-whatsapp"></i> {{ __('admin.share_whatsapp') }}
                                                </a>
                                            @endif
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h2 class="h6 mb-0">{{ __('dashboard.recent_shops') }}</h2>
                </div>
                <div class="card-body p-0">
                    @if ($recentShops->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-shop fs-1 d-block mb-2"></i>
                            {{ __('dashboard.no_shops_yet') }}
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('dashboard.table_shop') }}</th>
                                        <th>{{ __('dashboard.table_owner') }}</th>
                                        <th>{{ __('dashboard.table_phone') }}</th>
                                        <th>{{ __('dashboard.table_status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentShops as $shop)
                                        <tr>
                                            <td class="fw-semibold">{{ $shop->name }}</td>
                                            <td>{{ $shop->owner_name }}</td>
                                            <td class="text-muted small">{{ $shop->phone }}</td>
                                            <td>
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
