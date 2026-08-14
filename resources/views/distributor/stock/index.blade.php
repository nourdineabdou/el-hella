<x-app-layout>
    <x-slot name="header">{{ __('admin.my_stock_title') }}</x-slot>

    @unless ($distributor)
        <div class="alert alert-warning">{{ __('dashboard.no_distributor_profile') }}</div>
    @endunless

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <a href="{{ route('distributor.stock.receive') }}" class="btn btn-primary w-100 eh-action-btn">
                <i class="bi bi-plus-circle me-2"></i>{{ __('admin.stock_add_button') }}
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('distributor.stock.day') }}" class="btn btn-outline-primary w-100 eh-action-btn">
                <i class="bi bi-clipboard-data me-2"></i>{{ __('admin.stock_my_day_button') }}
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('distributor.shops.index') }}" class="btn btn-outline-success w-100 eh-action-btn">
                <i class="bi bi-basket me-2"></i>{{ __('Sell products') }}
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('distributor.stock.day') }}" class="btn btn-outline-danger w-100 eh-action-btn">
                <i class="bi bi-lock me-2"></i>{{ __('admin.stock_close_day_button') }}
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3">
            <h2 class="h6 mb-0">
                {{ __('admin.stock_current_title') }}
                @if ($day)
                    <span class="text-muted small fw-normal">— {{ $day->stock_date->format('d/m/Y') }}</span>
                @endif
            </h2>
        </div>
        <div class="card-body p-0">
            @if ($items->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                    {{ __('admin.stock_empty') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-mobile-cards">
                        <thead>
                            <tr>
                                <th>{{ __('admin.table_product') }}</th>
                                <th class="text-end">{{ __('admin.stock_current_quantity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td class="fw-semibold" data-label="{{ __('admin.table_product') }}">{{ $item->product?->translated_name }}</td>
                                    <td class="text-end" data-label="{{ __('admin.stock_current_quantity') }}">
                                        {{ rtrim(rtrim(number_format((float) $item->current_quantity, 3), '0'), '.') }} {{ $item->product?->unit }}
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
