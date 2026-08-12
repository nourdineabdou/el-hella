<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisitController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'date' => ['nullable', 'date'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'distributor_id' => ['nullable', 'exists:distributors,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'sale_status' => ['nullable', 'in:sale,cancelled,visit_only'],
        ]);

        $visits = $this->filteredQuery($request)
            ->latest('visited_at')
            ->paginate(10)
            ->withQueryString();

        $distributors = Distributor::with('user')->orderBy('id')->get();
        $products = Product::where('is_active', true)->orderBy('name_ar')->get();

        return view('admin.visits.index', [
            'visits' => $visits,
            'distributors' => $distributors,
            'products' => $products,
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
            'sale_status' => ['nullable', 'in:sale,cancelled,visit_only'],
        ]);

        $visits = $this->filteredQuery($request)->latest('visited_at')->get();

        $filename = 'visites-'.now()->format('Y-m-d-His').'.xlsx';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(app()->getLocale() === 'ar');

        $columns = [
            __('dashboard.table_shop'),
            __('admin.zone_label'),
            __('dashboard.table_distributor'),
            __('dashboard.table_type'),
            __('admin.sold_products_title'),
            __('dashboard.table_date'),
            __('dashboard.table_status'),
        ];
        $sheet->fromArray($columns, null, 'A1');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $row = 2;

        foreach ($visits as $visit) {
            $products = $visit->distribution
                ? $visit->distribution->items->map(function ($item) {
                    $name = app()->getLocale() === 'fr' && $item->product?->name_fr
                        ? $item->product->name_fr
                        : $item->product?->name_ar;

                    return trim($name.' ('.rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.').' '.$item->unit.')');
                })->implode(', ')
                : '';

            $type = __('dashboard.visit_type_visit_only');

            if ($visit->visit_type === 'distribution') {
                $type = $visit->distribution?->cancelled_at
                    ? __('admin.sale_cancelled_badge')
                    : __('dashboard.visit_type_sale');
            }

            $sheet->fromArray([
                $visit->shop?->name,
                $visit->zone,
                $visit->distributor?->user?->name,
                $type,
                $products,
                $visit->visited_at?->format('d/m/Y H:i'),
                $visit->is_within_allowed_distance ? __('dashboard.gps_ok') : __('dashboard.gps_alert'),
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

    public function cancelSale(Visit $visit): RedirectResponse
    {
        $distribution = $visit->distribution;

        if (! $distribution || $distribution->cancelled_at) {
            return back();
        }

        $distribution->update([
            'cancelled_at' => now(),
            'cancelled_by' => auth()->id(),
        ]);

        return back()->with('success', __('admin.sale_cancelled'));
    }

    private function filteredQuery(Request $request)
    {
        $query = Visit::with(['shop', 'distributor.user', 'distribution.items.product', 'distribution.cancelledBy']);

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

        if ($request->filled('distributor_id')) {
            $query->where('distributor_id', $request->input('distributor_id'));
        }

        if ($request->filled('product_id')) {
            $productId = $request->input('product_id');
            $query->whereHas('distribution.items', function ($itemQuery) use ($productId) {
                $itemQuery->where('product_id', $productId);
            });
        }

        match ($request->input('sale_status')) {
            'sale' => $query->where('visit_type', 'distribution')
                ->whereHas('distribution', fn ($q) => $q->whereNull('cancelled_at')),
            'cancelled' => $query->where('visit_type', 'distribution')
                ->whereHas('distribution', fn ($q) => $q->whereNotNull('cancelled_at')),
            'visit_only' => $query->where('visit_type', '!=', 'distribution'),
            default => null,
        };

        return $query;
    }
}
