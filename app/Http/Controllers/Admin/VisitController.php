<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\Visit;
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
        ]);

        $visits = $this->filteredQuery($request)->latest('visited_at')->get();

        $filename = 'visites-'.now()->format('Y-m-d-His').'.xlsx';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(app()->getLocale() === 'ar');

        $columns = ['Boutique', 'Zone', 'Distributeur', 'Type', 'Produits vendus', 'Distance (m)', 'Date', 'Statut GPS'];
        $sheet->fromArray($columns, null, 'A1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

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

            $sheet->fromArray([
                $visit->shop?->name,
                $visit->zone,
                $visit->distributor?->user?->name,
                $visit->visit_type === 'distribution' ? 'Vente' : 'Visite simple',
                $products,
                $visit->distance_from_shop !== null ? (float) $visit->distance_from_shop : null,
                $visit->visited_at?->format('d/m/Y H:i'),
                $visit->is_within_allowed_distance ? 'OK' : 'Alerte',
            ], null, 'A'.$row);

            $row++;
        }

        foreach (range('A', 'H') as $column) {
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

    private function filteredQuery(Request $request)
    {
        $query = Visit::with(['shop', 'distributor.user', 'distribution.items.product']);

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

        return $query;
    }
}
