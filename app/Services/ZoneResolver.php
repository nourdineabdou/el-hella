<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ZoneResolver
{
    /**
     * Reverse-geocode a GPS point into a human-readable neighbourhood name
     * via the free OpenStreetMap Nominatim API. Results are cached for a
     * month since a given point's neighbourhood name never changes, and the
     * request fails silently (returns null) so callers keep working if the
     * service is unreachable.
     */
    public function resolve(float $latitude, float $longitude): ?string
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
