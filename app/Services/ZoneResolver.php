<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ZoneResolver
{
    /**
     * Nouakchott's 9 official communes (moughataas), grouped into the 3
     * departments (Nord/Ouest/Sud), loaded once per request from the bundled
     * GeoJSON boundaries.
     *
     * @var list<array{commune: string, department: string, rings: list<list<array{0: float, 1: float}>>}>|null
     */
    private static ?array $communes = null;

    /**
     * Resolve a GPS point into one of Nouakchott's 9 communes via a local
     * point-in-polygon test against known commune boundaries — no network
     * call, so it's fast and always returns the correct official name.
     * Falls back to OpenStreetMap reverse geocoding for points outside
     * Nouakchott (e.g. a shop in another town).
     */
    public function resolve(float $latitude, float $longitude): ?string
    {
        $commune = $this->matchCommune($latitude, $longitude);

        if ($commune !== null) {
            return $commune;
        }

        return $this->reverseGeocode($latitude, $longitude);
    }

    private function matchCommune(float $latitude, float $longitude): ?string
    {
        foreach ($this->loadCommunes() as $commune) {
            foreach ($commune['rings'] as $ring) {
                if ($this->pointInPolygon($longitude, $latitude, $ring)) {
                    return $commune['commune'];
                }
            }
        }

        return null;
    }

    /**
     * Ray casting algorithm: counts how many times a ray cast from the point
     * crosses the polygon's edges. An odd count means the point is inside.
     *
     * @param  list<array{0: float, 1: float}>  $polygon
     */
    private function pointInPolygon(float $x, float $y, array $polygon): bool
    {
        $inside = false;
        $count = count($polygon);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            [$xi, $yi] = $polygon[$i];
            [$xj, $yj] = $polygon[$j];

            $intersects = ($yi > $y) !== ($yj > $y)
                && $x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi;

            if ($intersects) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    /**
     * @return list<array{commune: string, department: string, rings: list<list<array{0: float, 1: float}>>}>
     */
    private function loadCommunes(): array
    {
        if (self::$communes !== null) {
            return self::$communes;
        }

        return self::$communes = Cache::remember('geo:nouakchott-communes', now()->addDay(), function () {
            $path = resource_path('geo/nouakchott-communes.geojson');
            $geojson = json_decode(file_get_contents($path), true);

            return array_map(function (array $feature) {
                $geometry = $feature['geometry'];
                $polygons = $geometry['type'] === 'MultiPolygon'
                    ? $geometry['coordinates']
                    : [$geometry['coordinates']];

                $rings = [];
                foreach ($polygons as $polygon) {
                    $rings[] = $polygon[0];
                }

                return [
                    'commune' => $feature['properties']['commune'],
                    'department' => $feature['properties']['department'],
                    'rings' => $rings,
                ];
            }, $geojson['features']);
        });
    }

    /**
     * Reverse-geocode a GPS point into a human-readable place name via the
     * free OpenStreetMap Nominatim API, used only as a fallback when the
     * point falls outside Nouakchott's known communes. Results are cached
     * for a month since a given point's name never changes, and the request
     * fails silently (returns null) so callers keep working if the service
     * is unreachable.
     */
    private function reverseGeocode(float $latitude, float $longitude): ?string
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
