<x-app-layout>
    <x-slot name="header">{{ __('admin.visits_title') }}</x-slot>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.visits.index') }}" class="row g-3 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date') }}</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_from') }}</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" {{ request('date') ? 'disabled' : '' }}>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_to') }}</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" {{ request('date') ? 'disabled' : '' }}>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_distributor') }}</label>
                    <select name="distributor_id" class="form-select">
                        <option value="">{{ __('admin.all_distributors') }}</option>
                        @foreach ($distributors as $distributor)
                            <option value="{{ $distributor->id }}" {{ (string) request('distributor_id') === (string) $distributor->id ? 'selected' : '' }}>
                                {{ $distributor->user?->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">{{ __('admin.filter_apply') }}</button>
                    <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-secondary" title="{{ __('admin.filter_reset') }}">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if ($visits->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-geo-alt fs-1 d-block mb-2"></i>
                    {{ __('admin.no_visits_found') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-mobile-cards">
                        <thead>
                            <tr>
                                <th>{{ __('dashboard.table_shop') }}</th>
                                <th>{{ __('dashboard.table_distributor') }}</th>
                                <th>{{ __('dashboard.table_type') }}</th>
                                <th>{{ __('admin.table_quantity') }}</th>
                                <th>{{ __('dashboard.table_distance') }}</th>
                                <th>{{ __('dashboard.table_date') }}</th>
                                <th>{{ __('dashboard.table_status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($visits as $visit)
                                <tr>
                                    <td class="fw-semibold" data-label="{{ __('dashboard.table_shop') }}">{{ $visit->shop?->name }}</td>
                                    <td data-label="{{ __('dashboard.table_distributor') }}">{{ $visit->distributor?->user?->name }}</td>
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
                                    <td data-label="{{ __('dashboard.table_distance') }}">{{ $visit->formatted_distance ?? '—' }}</td>
                                    <td class="text-muted small" data-label="{{ __('dashboard.table_date') }}">{{ $visit->visited_at?->format('d/m/Y H:i') }}</td>
                                    <td data-label="{{ __('dashboard.table_status') }}">
                                        @if ($visit->is_within_allowed_distance)
                                            <span class="badge bg-success-subtle text-success">{{ __('dashboard.gps_ok') }}</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">{{ __('dashboard.gps_alert') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($visit->distribution)
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#visit-details-{{ $visit->id }}">
                                                {{ __('admin.table_details') }}
                                            </button>

                                            <div class="modal fade" id="visit-details-{{ $visit->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ __('admin.sale_details') }} — {{ $visit->shop?->name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                                        </div>
                                                        <div class="modal-body">
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
                                                        </div>
                                                        <div class="modal-footer">
                                                            @if ($visit->latitude && $visit->longitude)
                                                                @php
                                                                    $mapsUrl = 'https://www.google.com/maps/dir/?api=1&destination='.$visit->latitude.','.$visit->longitude;
                                                                    $shareText = __('admin.share_visit_location_text', ['shop' => $visit->shop?->name, 'date' => $visit->visited_at?->format('d/m/Y H:i')]).' '.$mapsUrl;
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
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $visits->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
