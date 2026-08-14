<x-app-layout>
    <x-slot name="header">{{ __('admin.stock_my_day_button') }}</x-slot>

    @unless ($day)
        <div class="alert alert-light border text-center py-5">
            <i class="bi bi-box-seam fs-1 d-block mb-2 text-muted"></i>
            {{ __('admin.stock_empty') }}
        </div>
    @else
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fs-4 fw-bold">{{ $activity['visits'] }}</div>
                        <div class="text-muted small">{{ __('dashboard.today_visits') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fs-4 fw-bold">{{ $activity['sales'] }}</div>
                        <div class="text-muted small">{{ __('admin.table_sales_count') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fs-4 fw-bold">{{ $activity['samples'] }}</div>
                        <div class="text-muted small">{{ __('admin.samples_count') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('distributor.stock.day.close') }}" id="close-day-form">
            @csrf

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h2 class="h6 mb-0">{{ __('admin.stock_day_summary_title') }} — {{ $day->stock_date->format('d/m/Y') }}</h2>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-mobile-cards">
                            <thead>
                                <tr>
                                    <th>{{ __('admin.table_product') }}</th>
                                    <th class="text-end">{{ __('admin.stock_received_label') }}</th>
                                    <th class="text-end">{{ __('admin.stock_sold_label') }}</th>
                                    <th class="text-end">{{ __('admin.stock_sampled_label') }}</th>
                                    <th class="text-end">{{ __('admin.stock_theoretical_label') }}</th>
                                    <th class="text-end">{{ __('admin.stock_returned_label') }}</th>
                                    <th class="text-end">{{ __('admin.stock_discrepancy_label') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    @php
                                        $unit = $item->product?->unit;
                                        $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 3), '0'), '.');
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold" data-label="{{ __('admin.table_product') }}">{{ $item->product?->translated_name }}</td>
                                        <td class="text-end" data-label="{{ __('admin.stock_received_label') }}">{{ $fmt($item->received_quantity) }} {{ $unit }}</td>
                                        <td class="text-end" data-label="{{ __('admin.stock_sold_label') }}">{{ $fmt($item->sold_quantity) }} {{ $unit }}</td>
                                        <td class="text-end" data-label="{{ __('admin.stock_sampled_label') }}">{{ $fmt($item->sampled_quantity) }} {{ $unit }}</td>
                                        <td class="text-end fw-semibold" data-label="{{ __('admin.stock_theoretical_label') }}">{{ $fmt($item->current_quantity) }} {{ $unit }}</td>
                                        <td class="text-end" data-label="{{ __('admin.stock_returned_label') }}">
                                            @if ($day->isOpen())
                                                <input
                                                    type="text"
                                                    inputmode="decimal"
                                                    autocomplete="off"
                                                    name="returned[{{ $item->product_id }}]"
                                                    class="form-control text-end stock-returned-input"
                                                    data-theoretical="{{ (float) $item->current_quantity }}"
                                                    data-discrepancy-target="discrepancy-{{ $item->product_id }}"
                                                    value="{{ $fmt($item->current_quantity) }}"
                                                    style="max-width: 8rem; margin-inline-start: auto;"
                                                >
                                            @else
                                                {{ $fmt($item->returned_quantity) }} {{ $unit }}
                                            @endif
                                        </td>
                                        <td class="text-end" data-label="{{ __('admin.stock_discrepancy_label') }}" id="discrepancy-{{ $item->product_id }}"
                                            data-{{ $item->discrepancy > 0 ? 'positive' : ($item->discrepancy < 0 ? 'negative' : '') }}="1">
                                            @if (! $day->isOpen())
                                                {{ $item->discrepancy > 0 ? '+' : '' }}{{ $fmt($item->discrepancy) }} {{ $unit }}
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($day->isOpen())
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input type="checkbox" name="confirm" value="1" class="form-check-input" id="confirm-return">
                            <label class="form-check-label" for="confirm-return">{{ __('admin.stock_confirm_return_label') }}</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="bi bi-lock me-2"></i>{{ __('admin.stock_close_day_button') }}
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-secondary mt-3 text-center">
                    <i class="bi bi-check-circle me-2"></i>{{ __('admin.stock_day_already_closed', ['date' => $day->closed_at->format('d/m/Y H:i')]) }}
                </div>
            @endif
        </form>
    @endunless

    <script>
        document.querySelectorAll('.stock-returned-input').forEach((input) => {
            const target = document.getElementById(input.dataset.discrepancyTarget);
            const theoretical = parseFloat(input.dataset.theoretical) || 0;

            const updateDiscrepancy = () => {
                const returned = parseFloat((input.value || '0').replace(',', '.'));
                if (Number.isNaN(returned)) {
                    target.textContent = '';
                    return;
                }
                const discrepancy = Math.round((returned - theoretical) * 1000) / 1000;
                const sign = discrepancy > 0 ? '+' : '';
                target.textContent = `${sign}${discrepancy}`;
                target.classList.toggle('text-danger', discrepancy < 0);
                target.classList.toggle('text-success', discrepancy > 0);
            };

            input.addEventListener('input', updateDiscrepancy);
            updateDiscrepancy();
        });
    </script>
</x-app-layout>
