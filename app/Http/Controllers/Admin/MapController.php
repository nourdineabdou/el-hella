<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\Shop;
use App\Services\ZoneResolver;
use Illuminate\View\View;

class MapController extends Controller
{
    public function __construct(private readonly ZoneResolver $zoneResolver)
    {
    }

    public function index(): View
    {
        $shops = Shop::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'owner_name', 'phone', 'latitude', 'longitude'])
            ->map(fn (Shop $shop) => [
                'id' => $shop->id,
                'name' => $shop->name,
                'owner_name' => $shop->owner_name,
                'phone' => $shop->phone,
                'latitude' => (float) $shop->latitude,
                'longitude' => (float) $shop->longitude,
            ]);

        $distributors = Distributor::query()
            ->with(['user', 'visits' => function ($query) {
                $query->latest('visited_at')->limit(1)->with('shop');
            }])
            ->where('is_active', true)
            ->whereNotNull('last_latitude')
            ->whereNotNull('last_longitude')
            ->get()
            ->map(fn (Distributor $distributor) => [
                'id' => $distributor->id,
                'name' => $distributor->user?->name,
                'phone' => $distributor->phone,
                'last_shop_name' => $distributor->visits->first()?->shop?->name,
                'zone' => $this->zoneResolver->resolve((float) $distributor->last_latitude, (float) $distributor->last_longitude),
                'latitude' => (float) $distributor->last_latitude,
                'longitude' => (float) $distributor->last_longitude,
                'last_location_at' => $distributor->last_location_at?->format('d/m/Y H:i'),
            ]);

        return view('admin.map.index', [
            'shops' => $shops,
            'distributors' => $distributors,
        ]);
    }
}