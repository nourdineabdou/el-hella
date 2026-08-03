<x-app-layout>
    <x-slot name="header">{{ __('admin.sold_products_title') }}</x-slot>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.products-sold.index') }}" class="row g-3 align-items-end">
                <div class="col-6 col-md-2">
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
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">{{ __('admin.filter_apply') }}</button>
                    <a href="{{ route('admin.products-sold.index') }}" class="btn btn-outline-secondary" title="{{ __('admin.filter_reset') }}">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.products-sold.index', ['date' => today()->toDateString()] + request()->except('date')) }}" class="btn btn-outline-primary btn-sm">
                        {{ __('admin.filter_today') }}
                    </a>
                    <a href="{{ route('admin.products-sold.export', request()->query()) }}" class="btn btn-outline-success ms-auto">
                        <i class="bi bi-file-earmark-excel"></i> {{ __('admin.export_excel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if ($items->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                    {{ __('admin.no_sales_found') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-mobile-cards">
                        <thead>
                            <tr>
                                <th>{{ __('admin.table_product') }}</th>
                                <th class="text-end">{{ __('admin.table_quantity') }}</th>
                                <th>{{ __('admin.table_unit') }}</th>
                                <th>{{ __('dashboard.table_distributor') }}</th>
                                <th>{{ __('dashboard.table_shop') }}</th>
                                <th>{{ __('admin.zone_label') }}</th>
                                <th>{{ __('dashboard.table_date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td class="fw-semibold" data-label="{{ __('admin.table_product') }}">
                                        {{ app()->getLocale() === 'fr' && $item->product?->name_fr ? $item->product->name_fr : $item->product?->name_ar }}
                                    </td>
                                    <td class="text-end" data-label="{{ __('admin.table_quantity') }}">
                                        {{ rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.') }}
                                    </td>
                                    <td data-label="{{ __('admin.table_unit') }}">{{ $item->unit }}</td>
                                    <td data-label="{{ __('dashboard.table_distributor') }}">{{ $item->distribution?->distributor?->user?->name }}</td>
                                    <td data-label="{{ __('dashboard.table_shop') }}">{{ $item->distribution?->shop?->name }}</td>
                                    <td data-label="{{ __('admin.zone_label') }}">{{ $item->distribution?->shop?->id ? ($shopZones[$item->distribution->shop->id] ?? '—') : '—' }}</td>
                                    <td class="text-muted small" data-label="{{ __('dashboard.table_date') }}">{{ $item->distribution?->distributed_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
