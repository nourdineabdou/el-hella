<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\GpsAlert;
use App\Models\Shop;
use App\Models\Visit;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $recentVisits = Visit::with(['shop', 'distributor.user', 'distribution.items.product'])
            ->latest('visited_at')
            ->limit(10)
            ->get();

        $recentShops = Shop::latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'distributorsCount' => Distributor::where('is_active', true)->count(),
            'shopsCount' => Shop::where('is_active', true)->count(),
            'visitsTodayCount' => Visit::whereDate('visited_at', today())->count(),
            'gpsAlertsCount' => GpsAlert::where('status', 'pending')->count(),
            'recentVisits' => $recentVisits,
            'recentShops' => $recentShops,
        ]);
    }
}
