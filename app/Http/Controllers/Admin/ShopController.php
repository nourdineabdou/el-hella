<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $query = Shop::query()
            ->withCount('visits')
            ->withSum(['distributions as total_quantity' => function ($query) {
                $query->whereNull('cancelled_at');
            }], 'total_quantity');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($shopQuery) use ($search) {
                $shopQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $shops = $query->orderBy('name')->paginate(20)->withQueryString();

        $topShops = Shop::withSum(['distributions as total_quantity' => function ($query) {
                $query->whereNull('cancelled_at');
            }], 'total_quantity')
            ->having('total_quantity', '>', 0)
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        return view('admin.shops.index', [
            'shops' => $shops,
            'topShops' => $topShops,
        ]);
    }
}