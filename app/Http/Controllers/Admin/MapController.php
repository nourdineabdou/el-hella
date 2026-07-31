<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\Shop;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class MapController extends Controller
{
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
                'zone' => $this->resolveZone((float) $distributor->last_latitude, (float) $distributor->last_longitude),
                'latitude' => (float) $distributor->last_latitude,
                'longitude' => (float) $distributor->last_longitude,
                'last_location_at' => $distributor->last_location_at?->format('d/m/Y H:i'),
            ]);

        return view('admin.map.index', [
            'shops' => $shops,
            'distributors' => $distributors,
        ]);
    }

    /**
     * Reverse-geocode a GPS point into a human-readable neighbourhood name
     * via the free OpenStreetMap Nominatim API. Results are cached for a
     * month since a given point's neighbourhood name never changes, and the
     * request fails silently (returns null) so the map keeps working if the
     * service is unreachable.
     */
    private function resolveZone(float $latitude, float $longitude): ?string
    {
        $cacheKey = sprintf('geocode:zone:%s:%s:%s', app()->getLocale(), round($latitude, 4), round($longitude, 4));

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($latitude, $longitude) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'ElHellaApp/1.0',
                ])->timeout(3)->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'format' => 'json',
                    'zoom' => 14,
                    'accept-language' => app()->getLocale(),
                ]);

                if (! $response->successful()) {
                    return null;
                }

                $address = $response->json('address', []);

                return $address['suburb']
                    ?? $address['neighbourhood']
                    ?? $address['quarter']
                    ?? $address['city_district']
                    ?? $address['town']
                    ?? $address['city']
                    ?? null;
            } catch (\Throwable) {
                return null;
            }
        });
    }
}