<x-app-layout>
    <x-slot name="header">{{ __('admin.products_title') }}</x-slot>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h2 class="h6 mb-3">{{ __('admin.create_product_title') }}</h2>

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.products.store') }}" class="row g-3">
                @csrf
                <div class="col-12 col-md-5">
                    <label class="form-label small fw-semibold">{{ __('admin.name_ar_label') }}</label>
                    <input type="text" name="name_ar" dir="rtl" value="{{ old('name_ar') }}" class="form-control" autocomplete="off" enterkeyhint="next" required>
                </div>
                <div class="col-12 col-md-5">
                    <label class="form-label small fw-semibold">{{ __('admin.name_fr_label') }}</label>
                    <input type="text" name="name_fr" dir="ltr" value="{{ old('name_fr') }}" class="form-control" autocomplete="off" enterkeyhint="next">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.unit_label') }}</label>
                    <select name="unit" class="form-select" required>
                        @foreach (['kg', 'unit', 'carton', 'bag'] as $unit)
                            <option value="{{ $unit }}" {{ old('unit') === $unit ? 'selected' : '' }}>{{ __('admin.unit_'.$unit) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-grid d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>{{ __('admin.create_product_button') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if ($products->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                    {{ __('admin.no_products_found') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-mobile-cards">
                        <thead>
                            <tr>
                                <th>{{ __('admin.code_label') }}</th>
                                <th>{{ __('admin.name_ar_label') }}</th>
                                <th>{{ __('admin.name_fr_label') }}</th>
                                <th>{{ __('admin.unit_label') }}</th>
                                <th>{{ __('dashboard.table_status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td class="fw-semibold" data-label="{{ __('admin.code_label') }}">{{ $product->code }}</td>
                                    <td dir="rtl" data-label="{{ __('admin.name_ar_label') }}">{{ $product->name_ar }}</td>
                                    <td data-label="{{ __('admin.name_fr_label') }}">{{ $product->name_fr ?: '—' }}</td>
                                    <td data-label="{{ __('admin.unit_label') }}">{{ __('admin.unit_'.$product->unit) }}</td>
                                    <td data-label="{{ __('dashboard.table_status') }}">
                                        @if ($product->is_active)
                                            <span class="badge bg-success-subtle text-success">{{ __('dashboard.active') }}</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ __('dashboard.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit-product-{{ $product->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>

                                <div class="modal fade" id="edit-product-{{ $product->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.products.update', $product) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('admin.edit_product_title') }} — {{ $product->name_ar }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label small fw-semibold">{{ __('admin.code_label') }}</label>
                                                            <input type="text" value="{{ $product->code }}" class="form-control" disabled>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label small fw-semibold">{{ __('admin.name_ar_label') }}</label>
                                                            <input type="text" name="name_ar" dir="rtl" value="{{ $product->name_ar }}" class="form-control" required>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label small fw-semibold">{{ __('admin.name_fr_label') }}</label>
                                                            <input type="text" name="name_fr" dir="ltr" value="{{ $product->name_fr }}" class="form-control">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label small fw-semibold">{{ __('admin.unit_label') }}</label>
                                                            <select name="unit" class="form-select" required>
                                                                @foreach (['kg', 'unit', 'carton', 'bag'] as $unit)
                                                                    <option value="{{ $unit }}" {{ $product->unit === $unit ? 'selected' : '' }}>{{ __('admin.unit_'.$unit) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="active-{{ $product->id }}" {{ $product->is_active ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="active-{{ $product->id }}">{{ __('dashboard.active') }}</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
                                                    <button type="submit" class="btn btn-primary">{{ __('admin.save_changes') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
