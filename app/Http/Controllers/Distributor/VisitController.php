<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'date' => ['nullable', 'date'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'shop_id' => ['nullable', 'exists:shops,id'],
        ]);

        $distributor = $request->user()->distributor;

        $query = Visit::with(['shop', 'distribution.items.product'])
            ->where('distributor_id', $distributor?->id);

        if ($request->filled('date')) {
            $query->whereDate('visited_at', $request->input('date'));
        } else {
            if ($request->filled('date_from')) {
                $query->whereDate('visited_at', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('visited_at', '<=', $request->input('date_to'));
            }
        }

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->input('shop_id'));
        }

        $visits = $query->latest('visited_at')->paginate(10)->withQueryString();

        $shops = Shop::whereHas('visits', function ($shopQuery) use ($distributor) {
            $shopQuery->where('distributor_id', $distributor?->id);
        })->orderBy('name')->get();

        return view('distributor.visits.index', [
            'visits' => $visits,
            'shops' => $shops,
        ]);
    }
}