<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\DistributorGoal;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoalController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $distributor = $request->user()->distributor;

        $dateFrom = $request->input('date_from') ?: today()->startOfMonth()->toDateString();
        $dateTo = $request->input('date_to') ?: today()->toDateString();

        $query = DistributorGoal::where('distributor_id', $distributor?->id)
            ->whereBetween('goal_date', [$dateFrom, $dateTo]);

        $goals = $query->orderBy('goal_date', 'desc')->paginate(10)->withQueryString();

        $goals->getCollection()->transform(function (DistributorGoal $goal) use ($distributor) {
            $actualVisits = Visit::where('distributor_id', $distributor?->id)
                ->whereDate('visited_at', $goal->goal_date)
                ->count();

            $goal->actual_visits = $actualVisits;
            $goal->percentage = $goal->target_visits > 0
                ? min(100, (int) round($actualVisits / $goal->target_visits * 100))
                : null;

            return $goal;
        });

        return view('distributor.goals.index', [
            'goals' => $goals,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}