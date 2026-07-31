<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\DistributorGoal;
use App\Models\Visit;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $distributor = auth()->user()->distributor;

        if (! $distributor) {
            return view('distributor.dashboard', [
                'distributor' => null,
                'todayVisits' => 0,
                'todayDistributions' => 0,
                'todayQuantity' => 0,
                'completionRate' => 0,
                'recentVisits' => collect(),
            ]);
        }

        $todayVisits = Visit::where('distributor_id', $distributor->id)
            ->whereDate('visited_at', today())
            ->count();

        $todayDistributions = Distribution::where('distributor_id', $distributor->id)
            ->whereDate('distributed_at', today())
            ->count();

        $todayQuantity = (float) Distribution::where('distributor_id', $distributor->id)
            ->whereDate('distributed_at', today())
            ->sum('total_quantity');

        $goal = DistributorGoal::where('distributor_id', $distributor->id)
            ->whereDate('goal_date', today())
            ->first();

        $completionRate = 0;

        if ($goal) {
            $rates = [];

            if ($goal->target_visits > 0) {
                $rates[] = min(100, $todayVisits / $goal->target_visits * 100);
            }

            if ($goal->target_distributions > 0) {
                $rates[] = min(100, $todayDistributions / $goal->target_distributions * 100);
            }

            if ($goal->target_quantity > 0) {
                $rates[] = min(100, $todayQuantity / (float) $goal->target_quantity * 100);
            }

            $completionRate = count($rates) > 0 ? round(array_sum($rates) / count($rates)) : 0;
        }

        $recentVisits = Visit::with(['shop', 'distribution.items.product'])
            ->where('distributor_id', $distributor->id)
            ->latest('visited_at')
            ->limit(10)
            ->get();

        return view('distributor.dashboard', [
            'distributor' => $distributor,
            'todayVisits' => $todayVisits,
            'todayDistributions' => $todayDistributions,
            'todayQuantity' => $todayQuantity,
            'completionRate' => $completionRate,
            'recentVisits' => $recentVisits,
        ]);
    }
}
