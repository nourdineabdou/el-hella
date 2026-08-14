<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\DistributorStockDay;
use App\Models\DistributorStockItem;
use App\Models\Product;
use App\Models\Sample;
use App\Models\SampleItem;
use App\Models\StockMovement;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'distributor_id' => ['nullable', 'exists:distributors,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $dateFrom = $request->input('date_from') ?: today()->toDateString();
        $dateTo = $request->input('date_to') ?: today()->toDateString();

        $daysQuery = DistributorStockDay::with('distributor.user')
            ->whereBetween('stock_date', [$dateFrom, $dateTo]);

        if ($request->filled('distributor_id')) {
            $daysQuery->where('distributor_id', $request->input('distributor_id'));
        }

        $dayIds = (clone $daysQuery)->pluck('id');

        $itemsQuery = DistributorStockItem::with(['product', 'stockDay.distributor.user'])
            ->whereIn('distributor_stock_day_id', $dayIds);

        if ($request->filled('product_id')) {
            $itemsQuery->where('product_id', $request->input('product_id'));
        }

        $items = $itemsQuery->get();

        $movementTotals = StockMovement::whereIn('distributor_stock_day_id', $dayIds)
            ->whereIn('movement_type', ['vente', 'echantillon'])
            ->selectRaw('distributor_stock_day_id, product_id, movement_type, SUM(ABS(quantity_stock_unit)) as total')
            ->groupBy('distributor_stock_day_id', 'product_id', 'movement_type')
            ->get()
            ->groupBy(fn ($row) => $row->distributor_stock_day_id.'-'.$row->product_id);

        foreach ($items as $item) {
            $key = $item->distributor_stock_day_id.'-'.$item->product_id;
            $forItem = $movementTotals->get($key, collect());
            $item->sold_quantity = (float) ($forItem->firstWhere('movement_type', 'vente')->total ?? 0);
            $item->sampled_quantity = (float) ($forItem->firstWhere('movement_type', 'echantillon')->total ?? 0);
        }

        $items = $items->sortByDesc(fn ($item) => $item->stockDay->stock_date->format('Y-m-d').'-'.str_pad($item->distributor_stock_day_id, 10, '0', STR_PAD_LEFT));

        $distributorIds = $request->filled('distributor_id')
            ? [$request->input('distributor_id')]
            : Distributor::pluck('id');

        $activity = [
            'shops_visited' => Visit::whereIn('distributor_id', $distributorIds)
                ->whereBetween('visited_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59'])
                ->distinct('shop_id')
                ->count('shop_id'),
            'sales_count' => Distribution::whereIn('distributor_id', $distributorIds)
                ->whereBetween('distributed_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59'])
                ->whereNull('cancelled_at')
                ->count(),
            'sales_quantity' => (float) Distribution::whereIn('distributor_id', $distributorIds)
                ->whereBetween('distributed_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59'])
                ->whereNull('cancelled_at')
                ->sum('total_quantity'),
            'samples_count' => Sample::whereIn('distributor_id', $distributorIds)
                ->whereBetween('given_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59'])
                ->count(),
            'samples_quantity' => (float) SampleItem::whereIn('sample_id', Sample::whereIn('distributor_id', $distributorIds)
                ->whereBetween('given_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59'])
                ->pluck('id'))
                ->sum('quantity_stock_unit'),
        ];

        return view('admin.stock.index', [
            'items' => $items,
            'activity' => $activity,
            'distributors' => Distributor::with('user')->orderBy('id')->get(),
            'products' => Product::where('is_active', true)->orderBy('name_ar')->get(),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function movements(Request $request): View
    {
        $request->validate([
            'distributor_id' => ['nullable', 'exists:distributors,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'movement_type' => ['nullable', 'in:entree_stock,vente,echantillon,retour_stock,ajustement'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = StockMovement::with(['distributor.user', 'product', 'shop'])
            ->latest('created_at');

        if ($request->filled('distributor_id')) {
            $query->where('distributor_id', $request->input('distributor_id'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->input('movement_type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $movements = $query->paginate(15)->withQueryString();

        return view('admin.stock.movements', [
            'movements' => $movements,
            'distributors' => Distributor::with('user')->orderBy('id')->get(),
            'products' => Product::where('is_active', true)->orderBy('name_ar')->get(),
        ]);
    }

    public function reviewDiscrepancy(Request $request, DistributorStockDay $stockDay): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ]);

        $stockDay->update([
            'admin_note' => $validated['admin_note'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', __('admin.discrepancy_review_saved'));
    }
}
