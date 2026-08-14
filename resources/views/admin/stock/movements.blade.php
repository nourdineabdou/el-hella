<x-app-layout>
    <x-slot name="header">{{ __('admin.stock_admin_movements_title') }}</x-slot>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.stock.movements') }}" class="row g-3 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_from') }}</label>
                    <x-date-input name="date_from" :value="request('date_from')" />
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_to') }}</label>
                    <x-date-input name="date_to" :value="request('date_to')" />
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
                                {{ $product->translated_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_movement_type') }}</label>
                    <select name="movement_type" class="form-select">
                        <option value="">{{ __('admin.all_movement_types') }}</option>
                        @foreach (['entree_stock', 'vente', 'echantillon', 'retour_stock', 'ajustement'] as $type)
                            <option value="{{ $type }}" {{ request('movement_type') === $type ? 'selected' : '' }}>
                                {{ __('admin.movement_type_'.$type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">{{ __('admin.filter_apply') }}</button>
                    <a href="{{ route('admin.stock.movements') }}" class="btn btn-outline-secondary" title="{{ __('admin.filter_reset') }}">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if ($movements->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                    {{ __('admin.no_stock_movements_found') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-mobile-cards">
                        <thead>
                            <tr>
                                <th>{{ __('admin.table_movement_date') }}</th>
                                <th>{{ __('dashboard.table_distributor') }}</th>
                                <th>{{ __('admin.table_product') }}</th>
                                <th>{{ __('admin.table_movement_type') }}</th>
                                <th class="text-end">{{ __('admin.table_quantity') }}</th>
                                <th class="text-end">{{ __('admin.table_balance_after') }}</th>
                                <th>{{ __('dashboard.table_shop') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movements as $movement)
                                @php
                                    $unit = $movement->product?->unit;
                                    $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 3), '0'), '.');
                                    $isPositive = (float) $movement->quantity_stock_unit >= 0;
                                @endphp
                                <tr>
                                    <td class="text-muted small" data-label="{{ __('admin.table_movement_date') }}">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="fw-semibold" data-label="{{ __('dashboard.table_distributor') }}">{{ $movement->distributor?->user?->name }}</td>
                                    <td data-label="{{ __('admin.table_product') }}">{{ $movement->product?->translated_name }}</td>
                                    <td data-label="{{ __('admin.table_movement_type') }}">
                                        <span class="badge bg-{{ match($movement->movement_type) {
                                            'entree_stock' => 'success-subtle text-success',
                                            'vente' => 'primary-subtle text-primary',
                                            'echantillon' => 'warning-subtle text-warning',
                                            'retour_stock' => 'secondary-subtle text-secondary',
                                            default => 'danger-subtle text-danger',
                                        } }}">
                                            {{ __('admin.movement_type_'.$movement->movement_type) }}
                                        </span>
                                    </td>
                                    <td class="text-end {{ $isPositive ? 'text-success' : 'text-danger' }}" data-label="{{ __('admin.table_quantity') }}">
                                        {{ $isPositive ? '+' : '' }}{{ $fmt($movement->quantity_stock_unit) }} {{ $unit }}
                                    </td>
                                    <td class="text-end" data-label="{{ __('admin.table_balance_after') }}">{{ $fmt($movement->balance_after) }} {{ $unit }}</td>
                                    <td class="text-muted small" data-label="{{ __('dashboard.table_shop') }}">{{ $movement->shop?->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $movements->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
