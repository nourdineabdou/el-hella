<x-app-layout>
    <x-slot name="header">{{ __('admin.visits_title') }}</x-slot>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.visits.index') }}" class="row g-3 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date') }}</label>
                    <x-date-input name="date" :value="request('date')" />
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_from') }}</label>
                    <x-date-input name="date_from" :value="request('date_from')" :disabled="(bool) request('date')" />
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_to') }}</label>
                    <x-date-input name="date_to" :value="request('date_to')" :disabled="(bool) request('date')" />
                </div>
                <div class="col-6 col-md-2">
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
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_product') }}</label>
                    <select name="product_id" class="form-select">
                        <option value="">{{ __('admin.all_products') }}</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" {{ (string) request('product_id') === (string) $product->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'fr' && $product->name_fr ? $product->name_fr : $product->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_sale_status') }}</label>
                    <select name="sale_status" class="form-select">
                        <option value="">{{ __('admin.sale_status_all') }}</option>
                        <option value="sale" {{ request('sale_status') === 'sale' ? 'selected' : '' }}>{{ __('admin.sale_status_sale') }}</option>
                        <option value="cancelled" {{ request('sale_status') === 'cancelled' ? 'selected' : '' }}>{{ __('admin.sale_status_cancelled') }}</option>
                        <option value="visit_only" {{ request('sale_status') === 'visit_only' ? 'selected' : '' }}>{{ __('admin.sale_status_visit_only') }}</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">{{ __('admin.filter_apply') }}</button>
                    <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-secondary" title="{{ __('admin.filter_reset') }}">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
                <div class="col-12">
                    <a href="{{ route('admin.visits.export', request()->query()) }}" class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-excel"></i> {{ __('admin.export_excel') }}
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
                                <th>{{ __('admin.zone_label') }}</th>
                                <th>{{ __('dashboard.table_distributor') }}</th>
                                <th>{{ __('dashboard.table_type') }}</th>
                                <th>{{ __('admin.table_quantity') }}</th>
                                <th>{{ __('dashboard.table_date') }}</th>
                                <th>{{ __('dashboard.table_status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($visits as $visit)
                                <tr>
                                    <td class="fw-semibold" data-label="{{ __('dashboard.table_shop') }}">{{ $visit->shop?->name }}</td>
                                    <td data-label="{{ __('admin.zone_label') }}">{{ $visit->zone ?? '—' }}</td>
                                    <td data-label="{{ __('dashboard.table_distributor') }}">{{ $visit->distributor?->user?->name }}</td>
                                    <td data-label="{{ __('dashboard.table_type') }}">
                                        @if ($visit->visit_type === 'distribution')
                                            @if ($visit->distribution?->cancelled_at)
                                                <span class="badge bg-danger-subtle text-danger">{{ __('admin.sale_cancelled_badge') }}</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success">{{ __('dashboard.visit_type_sale') }}</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ __('dashboard.visit_type_visit_only') }}</span>
                                        @endif
                                    </td>
                                    <td data-label="{{ __('admin.table_quantity') }}" class="{{ $visit->distribution?->cancelled_at ? 'text-muted text-decoration-line-through' : '' }}">
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
                                    <td class="text-end">
                                        @if ($visit->distribution)
                                            <div class="d-flex gap-2 justify-content-end">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#visit-details-{{ $visit->id }}">
                                                    {{ __('admin.table_details') }}
                                                </button>

                                                @unless ($visit->distribution->cancelled_at)
                                                    <form method="POST" action="{{ route('admin.visits.cancel-sale', $visit) }}"
                                                          data-confirm="{{ __('admin.cancel_sale_confirm') }}"
                                                          data-confirm-title="{{ __('admin.cancel_sale_confirm_title') }}"
                                                          data-confirm-yes="{{ __('Validate') }}"
                                                          data-confirm-no="{{ __('Cancel') }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            {{ __('admin.cancel_sale_button') }}
                                                        </button>
                                                    </form>
                                                @endunless
                                            </div>

                                            <div class="modal fade" id="visit-details-{{ $visit->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ __('admin.sale_details') }} — {{ $visit->shop?->name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            @if ($visit->distribution->cancelled_at)
                                                                <p class="text-danger small mb-3">
                                                                    <i class="bi bi-x-circle"></i>
                                                                    {{ __('admin.cancelled_by_at', ['name' => $visit->distribution->cancelledBy?->name ?? '—', 'date' => $visit->distribution->cancelled_at->format('d/m/Y H:i')]) }}
                                                                </p>
                                                            @endif
                                                            @if ($visit->zone)
                                                                <p class="text-muted small mb-3"><i class="bi bi-geo-alt"></i> {{ __('admin.zone_label') }} : {{ $visit->zone }}</p>
                                                            @endif
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
