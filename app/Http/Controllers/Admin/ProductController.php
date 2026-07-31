<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::orderBy('name_ar')->paginate(10);

        return view('admin.products.index', [
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_fr' => ['nullable', 'string', 'max:255'],
            'unit' => ['required', 'in:kg,unit,carton,bag'],
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::create([
                ...$validated,
                'code' => 'TEMP-'.Str::random(8),
                'is_active' => true,
            ]);

            $product->update([
                'code' => 'PROD-'.str_pad((string) $product->id, 4, '0', STR_PAD_LEFT),
            ]);
        });

        return redirect()->route('admin.products.index')->with('success', __('admin.product_created'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_fr' => ['nullable', 'string', 'max:255'],
            'unit' => ['required', 'in:kg,unit,carton,bag'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', __('admin.product_updated'));
    }
}