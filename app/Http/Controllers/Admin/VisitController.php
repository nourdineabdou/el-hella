<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
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
            'distributor_id' => ['nullable', 'exists:distributors,id'],
        ]);

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

        $visits = $query->latest('visited_at')->paginate(10)->withQueryString();

        $distributors = Distributor::with('user')->orderBy('id')->get();

        return view('admin.visits.index', [
            'visits' => $visits,
            'distributors' => $distributors,
        ]);
    }
}