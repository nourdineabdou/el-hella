<x-app-layout>
    <x-slot name="header">{{ __('admin.distributors_title') }}</x-slot>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h2 class="h6 mb-3">{{ __('admin.create_distributor_title') }}</h2>

            @if ($errors->any() && !$errors->hasBag('resetPassword'))
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.distributors.store') }}" class="row g-3">
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
                        <i class="bi bi-person-plus me-2"></i>{{ __('admin.create_distributor_button') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if ($distributors->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                    {{ __('admin.no_distributors_found') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-mobile-cards">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('admin.zone_label') }}</th>
                                <th>{{ __('dashboard.table_status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($distributors as $distributor)
                                <tr>
                                    <td class="fw-semibold" data-label="{{ __('Name') }}">{{ $distributor->user?->name }}</td>
                                    <td data-label="{{ __('Phone') }}">{{ $distributor->phone }}</td>
                                    <td data-label="{{ __('admin.zone_label') }}">{{ $distributor->zone ?: '—' }}</td>
                                    <td data-label="{{ __('dashboard.table_status') }}">
                                        @if ($distributor->is_active)
                                            <span class="badge bg-success-subtle text-success">{{ __('dashboard.active') }}</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ __('dashboard.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reset-password-{{ $distributor->id }}">
                                                <i class="bi bi-key"></i>
                                                <span class="d-none d-lg-inline ms-1">{{ __('admin.reset_password_button') }}</span>
                                            </button>

                                            <form method="POST" action="{{ route('admin.distributors.toggle-active', $distributor) }}"
                                                  @if ($distributor->is_active)
                                                      data-confirm="{{ __('admin.deactivate_confirm', ['name' => $distributor->user?->name]) }}"
                                                      data-confirm-title="{{ __('admin.deactivate_title') }}"
                                                      data-confirm-yes="{{ __('Validate') }}"
                                                      data-confirm-no="{{ __('Cancel') }}"
                                                  @endif>
                                                @csrf
                                                @if ($distributor->is_active)
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-slash-circle"></i>
                                                        <span class="d-none d-lg-inline ms-1">{{ __('admin.deactivate_button') }}</span>
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-check-circle"></i>
                                                        <span class="d-none d-lg-inline ms-1">{{ __('admin.activate_button') }}</span>
                                                    </button>
                                                @endif
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="reset-password-{{ $distributor->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.distributors.reset-password', $distributor) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('admin.reset_password_title') }} — {{ $distributor->user?->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label small fw-semibold">{{ __('New Password') }}</label>
                                                            <input type="password" name="password" class="form-control" autocomplete="new-password" minlength="4" required>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label small fw-semibold">{{ __('Confirm Password') }}</label>
                                                            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" minlength="4" required>
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
                    {{ $distributors->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
