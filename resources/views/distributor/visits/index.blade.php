<x-app-layout>
    <x-slot name="header">{{ __('admin.my_visits_title') }}</x-slot>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('distributor.visits.index') }}" class="row g-3 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_from') }}</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_to') }}</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_shop') }}</label>
                    <select name="shop_id" class="form-select">
                        <option value="">{{ __('admin.all_shops') }}</option>
                        @foreach ($shops as $shop)
                            <option value="{{ $shop->id }}" {{ (string) request('shop_id') === (string) $shop->id ? 'selected' : '' }}>
                                {{ $shop->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">{{ __('admin.filter_apply') }}</button>
                    <a href="{{ route('distributor.visits.index') }}" class="btn btn-outline-secondary" title="{{ __('admin.filter_reset') }}">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if ($visits->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-geo-alt fs-1 d-block mb-2"></i>
                {{ __('admin.no_visits_found') }}
            </div>
        </div>
    @else
        <div class="eh-visit-list">
            @foreach ($visits as $visit)
                <div class="eh-visit-card" data-bs-toggle="modal" data-bs-target="#visit-details-{{ $visit->id }}" role="button" tabindex="0">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold">{{ $visit->shop?->name }}</div>
                            <div class="text-muted small">{{ $visit->visited_at?->format('d/m/Y H:i') }}</div>
                            @if ($visit->zone)
                                <div class="text-muted small"><i class="bi bi-geo-alt"></i> {{ $visit->zone }}</div>
                            @endif
                        </div>
                        @if ($visit->visit_type === 'distribution')
                            <span class="badge bg-success-subtle text-success">{{ __('dashboard.visit_type_sale') }}</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">{{ __('dashboard.visit_type_visit_only') }}</span>
                        @endif
                    </div>

                    <div class="eh-visit-card-footer">
                        <div class="text-muted small">
                            {{ $visit->formatted_distance ?? '—' }}
                            @if ($visit->is_within_allowed_distance)
                                <span class="badge bg-success-subtle text-success ms-1">{{ __('dashboard.gps_ok') }}</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger ms-1">{{ __('dashboard.gps_alert') }}</span>
                            @endif
                        </div>

                        <div class="d-flex align-items-center gap-1 text-primary fw-semibold">
                            @if ($visit->distribution)
                                <span>{{ rtrim(rtrim(number_format((float) $visit->distribution->total_quantity, 3), '0'), '.') }}</span>
                            @endif
                            <i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} small"></i>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="visit-details-{{ $visit->id }}" tabindex="-1" aria-hidden="true">
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
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            {{ $visits->links() }}
        </div>
    @endif
</x-app-layout>
