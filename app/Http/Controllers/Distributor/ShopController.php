<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\DistributionItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Validation\Rule;

class ShopController extends Controller
{
    private const MAX_DISTANCE_METERS = 60;

    private const NEARBY_RADIUS_METERS = 100;

    /**
     * How much of the device's reported GPS accuracy margin we forgive when
     * checking distance thresholds, so a noisy fix (common indoors / in areas
     * with weak signal) doesn't wrongly block a legitimate visit. Capped so a
     * wildly inaccurate (or spoofed) reading can't bypass the check entirely.
     */
    private const MAX_ACCURACY_BONUS_METERS = 50;

    public function index()
    {
        return view('distributor.shops.index');
    }

    public function search(Request $request)
    {
        $search = trim($request->input('q', ''));

        if ($search === '') {
            return response()->json(['data' => []]);
        }

        $shops = Shop::query()
            ->where('is_active', true)
            ->where(function ($query) use ($search) {
                $query->where('phone', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'owner_name', 'phone']);

        return response()->json(['data' => $shops]);
    }

    public function nearby(Request $request)
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
        ]);

        $latitude = (float) $request->input('latitude');
        $longitude = (float) $request->input('longitude');
        $accuracyBonus = min((float) $request->input('accuracy', 0), self::MAX_ACCURACY_BONUS_METERS);
        $effectiveRadius = self::NEARBY_RADIUS_METERS + $accuracyBonus;

        $shops = Shop::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw(
                'id, name, owner_name, phone, (
                    6371000 * acos(least(1, greatest(-1,
                        cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?))
                        + sin(radians(?)) * sin(radians(latitude))
                    )))
                ) as distance',
                [$latitude, $longitude, $latitude]
            )
            ->havingRaw('distance <= ?', [$effectiveRadius])
            ->orderBy('distance')
            ->limit(10)
            ->get();

        return response()->json(['data' => $shops]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\s\-()]+$/'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $phone = preg_replace('/[^0-9+]/', '', $request->phone);
        $normalizedName = mb_strtolower(trim($request->name));

        $existingShop = Shop::where('phone', $phone)
            ->orWhereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
            ->first();

        if ($existingShop) {
            return response()->json([
                'message' => 'Cette boutique existe déjà.',
                'shop' => $existingShop,
                'already_exists' => true,
            ]);
        }

        try {
            $shop = Shop::create([
                'shop_number' => Str::uuid()->toString(),
                'name' => $request->name,
                'owner_name' => $request->owner_name,
                'phone' => $phone,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_updated_at' => now(),
                'location_source' => 'distributor',
                'is_active' => true,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                $existingShop = Shop::where('phone', $phone)
                    ->orWhereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
                    ->first();

                return response()->json([
                    'message' => 'Cette boutique existe déjà.',
                    'shop' => $existingShop,
                    'already_exists' => true,
                ]);
            }

            throw $e;
        }

        return response()->json([
            'message' => 'Boutique enregistrée.',
            'shop' => $shop,
        ]);
    }

    public function show(Shop $shop)
    {
        return view('distributor.shops.show', [
            'shop' => $shop,
            'maxDistance' => self::MAX_DISTANCE_METERS,
        ]);
    }

    public function searchProducts(Request $request)
    {
        $search = trim($request->input('q', ''));

        if ($search === '') {
            return response()->json(['data' => []]);
        }

        $products = Product::query()
            ->where('is_active', true)
            ->where(function ($query) use ($search) {
                $query->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_fr', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->orderBy('name_ar')
            ->limit(30)
            ->get(['id', 'name_ar', 'name_fr', 'unit', 'code']);

        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => app()->getLocale() === 'fr' && $product->name_fr ? $product->name_fr : $product->name_ar,
                'unit' => $product->unit,
                'code' => $product->code,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function sell(Request $request, Shop $shop)
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
        ]);

        $distance = $this->calculateDistance(
            $shop->latitude,
            $shop->longitude,
            $request->latitude,
            $request->longitude,
        );

        if ($this->exceedsAllowedDistance($distance, $request->input('gps_accuracy'))) {
            return response()->json([
                'message' => 'Vous êtes trop loin de la boutique pour valider cette vente.',
                'distance' => $distance,
            ], 422);
        }

        $user = $request->user();
        $distributor = $user->distributor;

        if (! $distributor) {
            return response()->json(['message' => 'Distributeur non trouvé.'], 403);
        }

        DB::transaction(function () use ($request, $shop, $distributor, $distance) {
            $visit = Visit::create([
                'distributor_id' => $distributor->id,
                'shop_id' => $shop->id,
                'visit_type' => 'distribution',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'gps_accuracy' => $request->input('gps_accuracy'),
                'distance_from_shop' => $distance,
                'is_within_allowed_distance' => true,
                'visited_at' => now(),
            ]);

            $totalQuantity = 0;
            $distribution = Distribution::create([
                'visit_id' => $visit->id,
                'distributor_id' => $distributor->id,
                'shop_id' => $shop->id,
                'total_quantity' => 0,
                'gps_status' => 'valid',
                'distributed_at' => now(),
            ]);

            foreach ($request->items as $item) {
                $quantity = floatval($item['quantity']);
                if ($quantity <= 0) {
                    continue;
                }

                $product = Product::find($item['product_id']);
                if (! $product) {
                    continue;
                }

                DistributionItem::create([
                    'distribution_id' => $distribution->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit' => $product->unit ?: 'kg',
                ]);
                $totalQuantity += $quantity;
            }

            $distribution->update(['total_quantity' => $totalQuantity]);

            $distributor->update([
                'last_latitude' => $request->latitude,
                'last_longitude' => $request->longitude,
                'last_location_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Vente enregistrée avec succès.']);
    }

    public function visit(Request $request, Shop $shop)
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $distance = $this->calculateDistance(
            $shop->latitude,
            $shop->longitude,
            $request->latitude,
            $request->longitude,
        );

        if ($this->exceedsAllowedDistance($distance, $request->input('gps_accuracy'))) {
            return response()->json([
                'message' => 'Vous êtes trop loin de la boutique pour valider la visite.',
                'distance' => $distance,
            ], 422);
        }

        $user = $request->user();
        $distributor = $user->distributor;

        if (! $distributor) {
            return response()->json(['message' => 'Distributeur non trouvé.'], 403);
        }

        Visit::create([
            'distributor_id' => $distributor->id,
            'shop_id' => $shop->id,
            'visit_type' => 'without_distribution',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'gps_accuracy' => $request->input('gps_accuracy'),
            'distance_from_shop' => $distance,
            'is_within_allowed_distance' => true,
            'visited_at' => now(),
        ]);

        $distributor->update([
            'last_latitude' => $request->latitude,
            'last_longitude' => $request->longitude,
            'last_location_at' => now(),
        ]);

        return response()->json(['message' => 'Visite enregistrée.']);
    }

    /**
     * True if the distributor is too far from the shop, after forgiving part
     * of the device's own reported GPS accuracy margin (see
     * MAX_ACCURACY_BONUS_METERS).
     */
    private function exceedsAllowedDistance(float $distance, mixed $accuracy): bool
    {
        $accuracyBonus = min((float) ($accuracy ?? 0), self::MAX_ACCURACY_BONUS_METERS);
        $effectiveDistance = max(0, $distance - $accuracyBonus);

        return $effectiveDistance > self::MAX_DISTANCE_METERS;
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2)
            + cos($lat1Rad) * cos($lat2Rad)
            * sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}
