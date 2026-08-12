<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\DistributionItem;
use App\Models\Product;
use App\Services\ZoneResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SoldProductController extends Controller
{
    public function __construct(private readonly ZoneResolver $zoneResolver)
    {
    }

    public function index(Request $request): View
    {
        $request->validate([
            'date' => ['nullable', 'date'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'distributor_id' => ['nullable', 'exists:distributors,id'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        $items = $this->filteredQuery($request)
            ->latest('distributions.distributed_at')
            ->paginate(15)
            ->withQueryString();

        $distributors = Distributor::with('user')->orderBy('id')->get();
        $products = Product::where('is_active', true)->orderBy('name_ar')->get();
        $shopZones = $this->resolveShopZones($items->getCollection());

        return view('admin.products-sold.index', [
            'items' => $items,
            'distributors' => $distributors,
            'products' => $products,
            'shopZones' => $shopZones,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $request->validate([
            'date' => ['nullable', 'date'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'distributor_id' => ['nullable', 'exists:distributors,id'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        $items = $this->filteredQuery($request)->latest('distributions.distributed_at')->get();
        $shopZones = $this->resolveShopZones($items);

        $filename = 'produits-vendus-'.now()->format('Y-m-d-His').'.xlsx';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(app()->getLocale() === 'ar');

        $columns = [
            __('admin.table_product'),
            __('admin.table_quantity'),
            __('admin.table_unit'),
            __('dashboard.table_distributor'),
            __('dashboard.table_shop'),
            __('admin.zone_label'),
            __('dashboard.table_date'),
        ];
        $sheet->fromArray($columns, null, 'A1');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $row = 2;

        foreach ($items as $item) {
            $shopId = $item->distribution?->shop?->id;

            $sheet->fromArray([
                $this->productName($item->product),
                (float) $item->quantity,
                $item->unit,
                $item->distribution?->distributor?->user?->name,
                $item->distribution?->shop?->name,
                $shopId ? ($shopZones[$shopId] ?? null) : null,
                $item->distribution?->distributed_at?->format('d/m/Y H:i'),
            ], null, 'A'.$row);

            $row++;
        }

        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    /**
     * Resolve each distinct shop's zone from its (fixed) GPS coordinates, so
     * every sale for that shop can display a zone even if it predates the
     * per-visit zone tracking. Keyed by shop id, deduplicated so a shop that
     * appears in many rows is only geocoded once (results are cached).
     */
    private function resolveShopZones(iterable $items): array
    {
        $shops = collect($items)
            ->map(fn ($item) => $item->distribution?->shop)
            ->filter(fn ($shop) => $shop && $shop->latitude && $shop->longitude)
            ->unique('id');

        $zones = [];

        foreach ($shops as $shop) {
            $zones[$shop->id] = $this->zoneResolver->resolve((float) $shop->latitude, (float) $shop->longitude);
        }

        return $zones;
    }

    private function productName(?object $product): ?string
    {
        if (! $product) {
            return null;
        }

        return app()->getLocale() === 'fr' && $product->name_fr ? $product->name_fr : $product->name_ar;
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = DistributionItem::query()
            ->with(['product', 'distribution.distributor.user', 'distribution.shop'])
            ->join('distributions', 'distribution_items.distribution_id', '=', 'distributions.id')
            ->whereNull('distributions.cancelled_at')
            ->select('distribution_items.*');

        if ($request->filled('date')) {
            $query->whereDate('distributions.distributed_at', $request->input('date'));
        } else {
            if ($request->filled('date_from')) {
                $query->whereDate('distributions.distributed_at', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('distributions.distributed_at', '<=', $request->input('date_to'));
            }
        }

        if ($request->filled('distributor_id')) {
            $query->where('distributions.distributor_id', $request->input('distributor_id'));
        }

        if ($request->filled('product_id')) {
            $query->where('distribution_items.product_id', $request->input('product_id'));
        }

        return $query;
    }
}
