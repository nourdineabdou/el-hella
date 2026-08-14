<x-app-layout>
    <x-slot name="header">{{ __('admin.stock_add_button') }}</x-slot>

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <p class="text-muted small px-3 px-md-4 pt-3 pt-md-4 mb-3">{{ __('admin.stock_add_hint') }}</p>

            <form method="POST" action="{{ route('distributor.stock.receive.store') }}">
                @csrf

                <div class="eh-receive-list">
                    @foreach ($products as $product)
                        <div class="eh-receive-row">
                            <div class="eh-receive-icon">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold text-truncate">{{ $product->translated_name }}</div>
                                <div class="text-muted small">{{ $product->unit }}</div>
                            </div>
                            <div class="eh-receive-input">
                                <input
                                    type="text"
                                    inputmode="decimal"
                                    autocomplete="off"
                                    name="quantities[{{ $product->id }}]"
                                    class="form-control form-control-lg text-end eh-receive-quantity"
                                    placeholder="0"
                                >
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-grid p-3 p-md-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle me-2"></i>{{ __('admin.stock_add_button') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.eh-receive-quantity').forEach((input) => {
            const row = input.closest('.eh-receive-row');
            input.addEventListener('input', () => {
                const hasValue = parseFloat((input.value || '').replace(',', '.')) > 0;
                row.classList.toggle('has-quantity', hasValue);
            });
        });
    </script>
</x-app-layout>
