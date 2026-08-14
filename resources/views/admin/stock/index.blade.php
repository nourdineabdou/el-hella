<x-app-layout>
    <x-slot name="header">{{ __('admin.stock_admin_title') }}</x-slot>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.stock.index') }}" class="row g-3 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_from') }}</label>
                    <x-date-input name="date_from" :value="$dateFrom" />
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_to') }}</label>
                    <x-date-input name="date_to" :value="$dateTo" />
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
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_product') }}</label>
                    <select name="product_id" class="form-select">
                        <option value="">{{ __('admin.all_products') }}</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" {{ (string) request('product_id') === (string) $product->id ? 'selected' : '' }}>
                                {{ $product->translated_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">{{ __('admin.filter_apply') }}</button>
                    <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary" title="{{ __('admin.filter_reset') }}">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
                <div class="col-12">
                    <a href="{{ route('admin.stock.movements') }}" class="btn btn-outline-primary">
                        <i class="bi bi-clock-history"></i> {{ __('admin.stock_admin_movements_title') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3 row-cols-2 row-cols-md-5">
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold">{{ $activity['shops_visited'] }}</div>
                    <div class="text-muted small">{{ __('admin.table_visits_count') }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold">{{ $activity['sales_count'] }}</div>
                    <div class="text-muted small">{{ __('admin.table_sales_count') }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold">{{ rtrim(rtrim(number_format($activity['sales_quantity'], 3), '0'), '.') ?: 0 }}</div>
                    <div class="text-muted small">{{ __('admin.table_quantity_sold') }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold">{{ $activity['samples_count'] }}</div>
                    <div class="text-muted small">{{ __('admin.samples_count') }}</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold">{{ rtrim(rtrim(number_format($activity['samples_quantity'], 3), '0'), '.') ?: 0 }} kg</div>
                    <div class="text-muted small">{{ __('admin.stock_sampled_label') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3">
            <h2 class="h6 mb-0">{{ __('admin.stock_admin_overview_title') }}</h2>
        </div>
        <div class="card-body p-0">
            @if ($items->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                    {{ __('admin.no_stock_movements_found') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-mobile-cards">
                        <thead>
                            <tr>
                                <th>{{ __('dashboard.table_distributor') }}</th>
                                <th>{{ __('dashboard.table_date') }}</th>
                                <th>{{ __('admin.table_product') }}</th>
                                <th class="text-end">{{ __('admin.stock_received_label') }}</th>
                                <th class="text-end">{{ __('admin.stock_sold_label') }}</th>
                                <th class="text-end">{{ __('admin.stock_sampled_label') }}</th>
                                <th class="text-end">{{ __('admin.stock_theoretical_label') }}</th>
                                <th class="text-end">{{ __('admin.stock_physical_label') }}</th>
                                <th class="text-end">{{ __('admin.stock_discrepancy_label') }}</th>
                                <th>{{ __('dashboard.table_status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $reviewedDayIds = []; @endphp
                            @foreach ($items as $item)
                                @php
                                    $unit = $item->product?->unit;
                                    $fmt = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format((float) $v, 3), '0'), '.');
                                    $day = $item->stockDay;
                                    $showReviewButton = $day && ! $day->isOpen() && ! in_array($day->id, $reviewedDayIds, true);
                                    if ($showReviewButton) {
                                        $reviewedDayIds[] = $day->id;
                                    }
                                @endphp
                                <tr>
                                    <td class="fw-semibold" data-label="{{ __('dashboard.table_distributor') }}">{{ $item->stockDay?->distributor?->user?->name }}</td>
                                    <td class="text-muted small" data-label="{{ __('dashboard.table_date') }}">{{ $item->stockDay?->stock_date?->format('d/m/Y') }}</td>
                                    <td data-label="{{ __('admin.table_product') }}">{{ $item->product?->translated_name }}</td>
                                    <td class="text-end" data-label="{{ __('admin.stock_received_label') }}">{{ $fmt($item->received_quantity) }} {{ $unit }}</td>
                                    <td class="text-end" data-label="{{ __('admin.stock_sold_label') }}">{{ $fmt($item->sold_quantity) }} {{ $unit }}</td>
                                    <td class="text-end" data-label="{{ __('admin.stock_sampled_label') }}">{{ $fmt($item->sampled_quantity) }} {{ $unit }}</td>
                                    <td class="text-end fw-semibold" data-label="{{ __('admin.stock_theoretical_label') }}">{{ $fmt($item->current_quantity) }} {{ $unit }}</td>
                                    <td class="text-end" data-label="{{ __('admin.stock_physical_label') }}">{{ $fmt($item->returned_quantity) }} {{ $item->returned_quantity !== null ? $unit : '' }}</td>
                                    <td class="text-end" data-label="{{ __('admin.stock_discrepancy_label') }}">
                                        @if ($item->discrepancy !== null)
                                            <span class="{{ $item->discrepancy < 0 ? 'text-danger' : ($item->discrepancy > 0 ? 'text-success' : '') }}">
                                                {{ $item->discrepancy > 0 ? '+' : '' }}{{ $fmt($item->discrepancy) }} {{ $unit }}
                                            </span>
                                        @endif
                                    </td>
                                    <td data-label="{{ __('dashboard.table_status') }}">
                                        @if ($item->stockDay?->isOpen())
                                            <span class="badge bg-warning-subtle text-warning">{{ __('admin.stock_day_open') }}</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ __('admin.stock_day_closed') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($showReviewButton)
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#review-day-{{ $day->id }}">
                                                <i class="bi bi-chat-left-text"></i> {{ __('admin.discrepancy_review_button') }}
                                            </button>

                                            <div class="modal fade" id="review-day-{{ $day->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ __('admin.discrepancy_review_title') }} — {{ $day->distributor?->user?->name }} ({{ $day->stock_date->format('d/m/Y') }})</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                                        </div>
                                                        <form method="POST" action="{{ route('admin.stock.review-discrepancy', $day) }}">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <textarea name="admin_note" class="form-control" rows="4" required>{{ $day->admin_note }}</textarea>
                                                                @if ($day->reviewer)
                                                                    <p class="text-muted small mt-2 mb-0">{{ __('admin.reviewed_by_at', ['name' => $day->reviewer->name, 'date' => $day->reviewed_at?->format('d/m/Y H:i')]) }}</p>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
                                                                <button type="submit" class="btn btn-primary">{{ __('admin.save_changes') }}</button>
                                                            </div>
                                                        </form>
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
            @endif
        </div>
    </div>
</x-app-layout>
