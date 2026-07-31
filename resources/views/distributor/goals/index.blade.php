<x-app-layout>
    <x-slot name="header">{{ __('admin.my_goals_title') }}</x-slot>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('distributor.goals.index') }}" class="row g-3 align-items-end">
                <div class="col-6 col-md-4">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_from') }}</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                </div>
                <div class="col-6 col-md-4">
                    <label class="form-label small fw-semibold">{{ __('admin.filter_date_to') }}</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">{{ __('admin.filter_apply') }}</button>
                    <a href="{{ route('distributor.goals.index') }}" class="btn btn-outline-secondary" title="{{ __('admin.filter_reset') }}">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if ($goals->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-bullseye fs-1 d-block mb-2"></i>
                {{ __('admin.no_goals_found_self') }}
            </div>
        </div>
    @else
        <div class="eh-visit-list">
            @foreach ($goals as $goal)
                <div class="eh-visit-card" style="cursor: default;">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="fw-semibold">{{ \Illuminate\Support\Carbon::parse($goal->goal_date)->format('d/m/Y') }}</div>
                        <span class="badge {{ $goal->percentage >= 100 ? 'bg-success-subtle text-success' : ($goal->percentage >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') }}">
                            {{ $goal->percentage }}%
                        </span>
                    </div>

                    <div class="progress mt-2" style="height: .5rem;">
                        <div class="progress-bar {{ $goal->percentage >= 100 ? 'bg-success' : ($goal->percentage >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $goal->percentage }}%"></div>
                    </div>

                    <div class="eh-visit-card-footer">
                        <div class="text-muted small">{{ __('admin.target_visits_label') }} : {{ $goal->target_visits }}</div>
                        <div class="fw-semibold small">{{ __('admin.table_actual') }} : {{ $goal->actual_visits }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            {{ $goals->links() }}
        </div>
    @endif
</x-app-layout>
