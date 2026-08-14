<?php

namespace App\Http\Controllers\Distributor;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\Product;
use App\Models\Sample;
use App\Models\StockMovement;
use App\Models\Visit;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(private readonly StockService $stock)
    {
    }

    public function index(Request $request): View
    {
        $distributor = $request->user()->distributor;
        $day = $distributor?->openStockDay();

        $items = $day
            ? $day->items()->with('product')->get()->sortBy(fn ($item) => $item->product?->translated_name)
            : collect();

        return view('distributor.stock.index', [
            'distributor' => $distributor,
            'day' => $day,
            'items' => $items,
        ]);
    }

    public function showReceive(Request $request): View
    {
        $products = Product::where('is_active', true)->orderBy('name_ar')->get();

        return view('distributor.stock.receive', [
            'products' => $products,
        ]);
    }

    public function receive(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quantities' => ['required', 'array'],
            'quantities.*' => ['nullable', 'string'],
        ]);

        $distributor = $request->user()->distributor;

        if (! $distributor) {
            return back()->withErrors(['quantities' => __('dashboard.no_distributor_profile')]);
        }

        $received = 0;

        foreach ($validated['quantities'] as $productId => $rawQuantity) {
            $quantity = StockService::normalizeDecimal((string) $rawQuantity);

            if ($quantity <= 0) {
                continue;
            }

            $product = Product::find($productId);

            if (! $product) {
                continue;
            }

            $this->stock->receive($distributor, $product, $quantity, $request->user());
            $received++;
        }

        if ($received === 0) {
            return back()->withErrors(['quantities' => __('admin.stock_receive_empty')]);
        }

        return redirect()->route('distributor.stock.index')->with('success', __('admin.stock_received_success'));
    }

    public function day(Request $request): View
    {
        $distributor = $request->user()->distributor;

        // The open day if there is one; otherwise the most recently closed
        // one, so the summary stays visible right after closing instead of
        // disappearing the moment the day closes.
        $day = $distributor?->openStockDay()
            ?? $distributor?->stockDays()->latest('id')->first();

        $items = $day
            ? $day->items()->with('product')->get()->sortBy(fn ($item) => $item->product?->translated_name)
            : collect();

        $movementTotals = $day
            ? StockMovement::where('distributor_stock_day_id', $day->id)
                ->whereIn('movement_type', ['vente', 'echantillon'])
                ->selectRaw('product_id, movement_type, SUM(ABS(quantity_stock_unit)) as total')
                ->groupBy('product_id', 'movement_type')
                ->get()
                ->groupBy('product_id')
            : collect();

        foreach ($items as $item) {
            $forProduct = $movementTotals->get($item->product_id, collect());
            $item->sold_quantity = (float) ($forProduct->firstWhere('movement_type', 'vente')->total ?? 0);
            $item->sampled_quantity = (float) ($forProduct->firstWhere('movement_type', 'echantillon')->total ?? 0);
        }

        $activity = null;

        if ($day) {
            $activity = [
                'visits' => Visit::where('distributor_id', $distributor->id)
                    ->whereBetween('visited_at', [$day->created_at, $day->closed_at ?? now()])
                    ->count(),
                'sales' => Distribution::where('distributor_id', $distributor->id)
                    ->whereBetween('distributed_at', [$day->created_at, $day->closed_at ?? now()])
                    ->whereNull('cancelled_at')
                    ->count(),
                'samples' => Sample::where('distributor_id', $distributor->id)
                    ->whereBetween('given_at', [$day->created_at, $day->closed_at ?? now()])
                    ->count(),
            ];
        }

        return view('distributor.stock.day', [
            'distributor' => $distributor,
            'day' => $day,
            'items' => $items,
            'activity' => $activity,
        ]);
    }

    public function closeDay(Request $request): RedirectResponse
    {
        $distributor = $request->user()->distributor;
        $day = $distributor?->openStockDay();

        if (! $day) {
            return redirect()->route('distributor.stock.index');
        }

        $validated = $request->validate([
            'returned' => ['required', 'array'],
            'returned.*' => ['nullable', 'string'],
            'confirm' => ['required', 'accepted'],
        ]);

        $returned = [];

        foreach ($day->items as $item) {
            $raw = $validated['returned'][$item->product_id] ?? '0';
            $returned[$item->product_id] = StockService::normalizeDecimal((string) $raw);
        }

        $this->stock->closeDay($day, $returned, $request->user());

        return redirect()->route('distributor.stock.index')->with('success', __('admin.stock_day_closed_success'));
    }
}
