<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\DistributorGoal;
use App\Models\Visit;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GoalController extends Controller
{
    private const MAX_RANGE_DAYS = 92;

    public function index(Request $request): View
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'distributor_id' => ['nullable', 'exists:distributors,id'],
        ]);

        $dateFrom = $request->input('date_from') ?: today()->startOfMonth()->toDateString();
        $dateTo = $request->input('date_to') ?: today()->toDateString();

        $query = DistributorGoal::with('distributor.user')
            ->whereBetween('goal_date', [$dateFrom, $dateTo]);

        if ($request->filled('distributor_id')) {
            $query->where('distributor_id', $request->input('distributor_id'));
        }

        $goals = $query->orderBy('goal_date', 'desc')
            ->orderBy('distributor_id')
            ->paginate(10)
            ->withQueryString();

        $goals->getCollection()->transform(function (DistributorGoal $goal) {
            $actualVisits = Visit::where('distributor_id', $goal->distributor_id)
                ->whereDate('visited_at', $goal->goal_date)
                ->count();

            $goal->actual_visits = $actualVisits;
            $goal->percentage = $goal->target_visits > 0
                ? min(100, (int) round($actualVisits / $goal->target_visits * 100))
                : null;

            return $goal;
        });

        $distributors = Distributor::with('user')->where('is_active', true)->orderBy('id')->get();

        return view('admin.goals.index', [
            'goals' => $goals,
            'distributors' => $distributors,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'distributor_id' => ['required', 'exists:distributors,id'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'target_visits' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $period = CarbonPeriod::create($validated['date_from'], $validated['date_to']);

        if (iterator_count($period) > self::MAX_RANGE_DAYS) {
            return back()->withErrors(['date_to' => __('admin.goal_range_too_long')])->withInput();
        }

        foreach ($period as $date) {
            DistributorGoal::updateOrCreate(
                [
                    'distributor_id' => $validated['distributor_id'],
                    'goal_date' => $date->toDateString(),
                ],
                [
                    'target_visits' => $validated['target_visits'],
                ]
            );
        }

        return redirect()->route('admin.goals.index', [
            'distributor_id' => $validated['distributor_id'],
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
        ])->with('success', __('admin.goal_saved'));
    }
}