<x-app-layout>
    <x-slot name="header">{{ __('admin.admins_title') }}</x-slot>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h2 class="h6 mb-3">{{ __('admin.create_admin_title') }}</h2>

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.admins.store') }}" class="row g-3">
                @csrf
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold">{{ __('Name') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" autocomplete="off" enterkeyhint="next" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold">{{ __('Phone') }}</label>
                    <input type="tel" name="phone" inputmode="tel" value="{{ old('phone') }}" class="form-control" autocomplete="off" enterkeyhint="next" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold">{{ __('New Password') }}</label>
                    <input type="password" name="password" class="form-control" autocomplete="new-password" enterkeyhint="next" minlength="4" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold">{{ __('Confirm Password') }}</label>
                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" enterkeyhint="done" minlength="4" required>
                </div>

                <div class="col-12 d-grid d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>{{ __('admin.create_admin_button') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if ($admins->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-person-badge fs-1 d-block mb-2"></i>
                    {{ __('admin.no_admins_found') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-mobile-cards">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('dashboard.table_status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($admins as $admin)
                                <tr>
                                    <td class="fw-semibold" data-label="{{ __('Name') }}">
                                        {{ $admin->name }}
                                        @if ($admin->id === auth()->id())
                                            <span class="badge bg-primary-subtle text-primary ms-1">{{ __('admin.you_label') }}</span>
                                        @endif
                                    </td>
                                    <td data-label="{{ __('Phone') }}">{{ $admin->phone }}</td>
                                    <td data-label="{{ __('dashboard.table_status') }}">
                                        @if ($admin->is_active)
                                            <span class="badge bg-success-subtle text-success">{{ __('dashboard.active') }}</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ __('dashboard.inactive') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $admins->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
