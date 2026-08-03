<x-app-layout>
    <x-slot name="header">{{ __('admin.gps_alerts_title') }}</x-slot>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.gps-alerts.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_status') }}</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>{{ __('admin.status_pending') }}</option>
                        <option value="justified" {{ $status === 'justified' ? 'selected' : '' }}>{{ __('admin.status_justified') }}</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>{{ __('admin.status_rejected') }}</option>
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>{{ __('admin.all_statuses') }}</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if ($alerts->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-shield-check fs-1 d-block mb-2"></i>
                    {{ __('admin.no_gps_alerts_found') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-mobile-cards">
                        <thead>
                            <tr>
                                <th>{{ __('dashboard.table_shop') }}</th>
                                <th>{{ __('dashboard.table_distributor') }}</th>
                                <th>{{ __('dashboard.table_date') }}</th>
                                <th class="text-end">{{ __('dashboard.table_distance') }}</th>
                                <th class="text-end">{{ __('admin.table_allowed_distance') }}</th>
                                <th>{{ __('dashboard.table_status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($alerts as $alert)
                                <tr>
                                    <td class="fw-semibold" data-label="{{ __('dashboard.table_shop') }}">{{ $alert->shop?->name }}</td>
                                    <td data-label="{{ __('dashboard.table_distributor') }}">{{ $alert->distributor?->user?->name }}</td>
                                    <td class="text-muted small" data-label="{{ __('dashboard.table_date') }}">{{ $alert->visit?->visited_at?->format('d/m/Y H:i') }}</td>
                                    <td class="text-end" data-label="{{ __('dashboard.table_distance') }}">{{ number_format((float) $alert->distance, 0) }} m</td>
                                    <td class="text-end" data-label="{{ __('admin.table_allowed_distance') }}">{{ number_format((float) $alert->allowed_distance, 0) }} m</td>
                                    <td data-label="{{ __('dashboard.table_status') }}">
                                        @if ($alert->status === 'pending')
                                            <span class="badge bg-warning-subtle text-warning">{{ __('admin.status_pending') }}</span>
                                        @elseif ($alert->status === 'justified')
                                            <span class="badge bg-success-subtle text-success">{{ __('admin.status_justified') }}</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">{{ __('admin.status_rejected') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($alert->status === 'pending')
                                            <div class="d-flex gap-2 justify-content-end">
                                                <form method="POST" action="{{ route('admin.gps-alerts.review', $alert) }}">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="justified">
                                                    <button type="submit" class="btn btn-sm btn-success">{{ __('admin.justify_button') }}</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.gps-alerts.review', $alert) }}">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="rejected">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('admin.reject_button') }}</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-muted small">{{ $alert->reviewed_at?->format('d/m/Y H:i') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $alerts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
