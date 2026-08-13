<x-app-layout>
    <x-slot name="header">{{ __('admin.goals_title') }}</x-slot>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h2 class="h6 mb-1">{{ __('admin.set_goal_title') }}</h2>
            <p class="text-muted small mb-3">{{ __('admin.set_goal_hint') }}</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.goals.store') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_distributor') }}</label>
                    <select name="distributor_id" class="form-select" required>
                        <option value="">{{ __('admin.all_distributors') }}</option>
                        @foreach ($distributors as $distributor)
                            <option value="{{ $distributor->id }}" {{ (string) old('distributor_id') === (string) $distributor->id ? 'selected' : '' }}>
                                {{ $distributor->user?->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_from') }}</label>
                    <x-date-input name="date_from" :value="old('date_from')" required />
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_to') }}</label>
                    <x-date-input name="date_to" :value="old('date_to')" required />
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">{{ __('admin.target_visits_label') }}</label>
                    <input type="number" inputmode="numeric" name="target_visits" min="1" max="100" value="{{ old('target_visits') }}" class="form-control" required>
                </div>
                <div class="col-6 col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">{{ __('admin.save_goal') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.goals.index') }}" class="row g-3 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_from') }}</label>
                    <x-date-input name="date_from" :value="$dateFrom" />
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_to') }}</label>
                    <x-date-input name="date_to" :value="$dateTo" />
                </div>
                <div class="col-12 col-md-4">
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
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">{{ __('admin.filter_apply') }}</button>
                    <a href="{{ route('admin.goals.index') }}" class="btn btn-outline-secondary" title="{{ __('admin.filter_reset') }}">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if ($goals->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-bullseye fs-1 d-block mb-2"></i>
                    {{ __('admin.no_goals_found') }}
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-mobile-cards">
                        <thead>
                            <tr>
                                <th>{{ __('dashboard.table_date') }}</th>
                                <th>{{ __('dashboard.table_distributor') }}</th>
                                <th>{{ __('admin.table_target') }}</th>
                                <th>{{ __('admin.table_actual') }}</th>
                                <th>{{ __('admin.table_percentage') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($goals as $goal)
                                <tr>
                                    <td data-label="{{ __('dashboard.table_date') }}">{{ \Illuminate\Support\Carbon::parse($goal->goal_date)->format('d/m/Y') }}</td>
                                    <td class="fw-semibold" data-label="{{ __('dashboard.table_distributor') }}">{{ $goal->distributor?->user?->name }}</td>
                                    <td data-label="{{ __('admin.table_target') }}">{{ $goal->target_visits }}</td>
                                    <td data-label="{{ __('admin.table_actual') }}">{{ $goal->actual_visits }}</td>
                                    <td data-label="{{ __('admin.table_percentage') }}">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: .5rem; max-width: 90px;">
                                                <div class="progress-bar {{ $goal->percentage >= 100 ? 'bg-success' : ($goal->percentage >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $goal->percentage }}%"></div>
                                            </div>
                                            <span class="small fw-semibold text-nowrap">{{ $goal->percentage }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $goals->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
