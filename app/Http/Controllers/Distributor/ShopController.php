<?php

namespace App\Http\Controllers\Distributor;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\DistributionItem;
use App\Models\GpsAlert;
use App\Models\Product;
use App\Models\Sample;
use App\Models\SampleItem;
use App\Models\Shop;
use App\Models\Visit;
use App\Services\StockService;
use App\Services\ZoneResolver;
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

    public function __construct(
        private readonly ZoneResolver $zoneResolver,
        private readonly StockService $stock,
    ) {
    }

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

        $distributor = $request->user()->distributor;

        if ($distributor) {
            Visit::create([
                'distributor_id' => $distributor->id,
                'shop_id' => $shop->id,
                'visit_type' => 'without_distribution',
                'latitude' => $shop->latitude,
                'longitude' => $shop->longitude,
                'zone' => $this->zoneResolver->resolve((float) $shop->latitude, (float) $shop->longitude),
                'distance_from_shop' => 0,
                'is_within_allowed_distance' => true,
                'visited_at' => now(),
            ]);

            $distributor->update([
                'last_latitude' => $shop->latitude,
                'last_longitude' => $shop->longitude,
                'last_location_at' => now(),
            ]);
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

    /**
     * Only products the distributor currently has stock for — selling or
     * sampling something they have zero of makes no sense, and would just
     * get rejected by StockService anyway. Includes each product's current
     * stock so the picker can show it.
     */
    public function searchProducts(Request $request)
    {
        $search = trim($request->input('q', ''));

        if ($search === '') {
            return response()->json(['data' => []]);
        }

        $distributor = $request->user()->distributor;
        $stockDay = $distributor?->openStockDay();

        if (! $stockDay) {
            return response()->json(['data' => [], 'no_stock' => true]);
        }

        $stockByProduct = $stockDay->items()
            ->where('current_quantity', '>', 0)
            ->pluck('current_quantity', 'product_id');

        if ($stockByProduct->isEmpty()) {
            return response()->json(['data' => [], 'no_stock' => true]);
        }

        $products = Product::query()
            ->where('is_active', true)
            ->whereIn('id', $stockByProduct->keys())
            ->where(function ($query) use ($search) {
                $query->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_fr', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->orderBy('name_ar')
            ->limit(30)
            ->get(['id', 'name_ar', 'name_fr', 'unit', 'code']);

        $data = $products->map(function ($product) use ($stockByProduct) {
            return [
                'id' => $product->id,
                'name' => app()->getLocale() === 'fr' && $product->name_fr ? $product->name_fr : $product->name_ar,
                'unit' => $product->unit,
                'code' => $product->code,
                'available' => (float) $stockByProduct[$product->id],
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

        $isWithinRange = ! $this->exceedsAllowedDistance($distance, $request->input('gps_accuracy'));

        $user = $request->user();
        $distributor = $user->distributor;

        if (! $distributor) {
            return response()->json(['message' => 'Distributeur non trouvé.'], 403);
        }

        $zone = $this->zoneResolver->resolve((float) $request->latitude, (float) $request->longitude);

        try {
            DB::transaction(function () use ($request, $shop, $distributor, $distance, $zone, $isWithinRange, $user) {
                $visit = Visit::create([
                    'distributor_id' => $distributor->id,
                    'shop_id' => $shop->id,
                    'visit_type' => 'distribution',
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'zone' => $zone,
                    'gps_accuracy' => $request->input('gps_accuracy'),
                    'distance_from_shop' => $distance,
                    'is_within_allowed_distance' => $isWithinRange,
                    'visited_at' => now(),
                ]);

                if (! $isWithinRange) {
                    GpsAlert::create([
                        'visit_id' => $visit->id,
                        'distributor_id' => $distributor->id,
                        'shop_id' => $shop->id,
                        'distance' => $distance,
                        'allowed_distance' => self::MAX_DISTANCE_METERS,
                        'status' => 'pending',
                    ]);
                }

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

                    $distributionItem = DistributionItem::create([
                        'distribution_id' => $distribution->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit' => $product->unit ?: 'kg',
                    ]);
                    $totalQuantity += $quantity;

                    $this->stock->deductForSale($distributor, $product, $quantity, [
                        'shop_id' => $shop->id,
                        'visit_id' => $visit->id,
                        'distribution_item_id' => $distributionItem->id,
                    ], $user);
                }

                $distribution->update(['total_quantity' => $totalQuantity]);

                $distributor->update([
                    'last_latitude' => $request->latitude,
                    'last_longitude' => $request->longitude,
                    'last_location_at' => now(),
                ]);
            });
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message' => __('admin.insufficient_stock', [
                    'available' => rtrim(rtrim(number_format($e->available, 3), '0'), '.'),
                    'unit' => $e->product->unit,
                ]),
            ], 422);
        }

        return response()->json([
            'message' => 'Vente enregistrée avec succès.',
            'gps_alert' => ! $isWithinRange,
        ]);
    }

    public function visit(Request $request, Shop $shop)
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'samples' => ['nullable', 'array'],
            'samples.*.product_id' => ['required_with:samples', 'exists:products,id'],
            'samples.*.quantity' => ['required_with:samples', 'numeric', 'min:0.001'],
        ]);

        $distance = $this->calculateDistance(
            $shop->latitude,
            $shop->longitude,
            $request->latitude,
            $request->longitude,
        );

        $isWithinRange = ! $this->exceedsAllowedDistance($distance, $request->input('gps_accuracy'));

        $user = $request->user();
        $distributor = $user->distributor;

        if (! $distributor) {
            return response()->json(['message' => 'Distributeur non trouvé.'], 403);
        }

        $zone = $this->zoneResolver->resolve((float) $request->latitude, (float) $request->longitude);
        $samples = $request->input('samples', []);

        try {
            $visit = DB::transaction(function () use ($request, $shop, $distributor, $distance, $zone, $isWithinRange, $samples, $user) {
                $visit = Visit::create([
                    'distributor_id' => $distributor->id,
                    'shop_id' => $shop->id,
                    'visit_type' => 'without_distribution',
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'zone' => $zone,
                    'gps_accuracy' => $request->input('gps_accuracy'),
                    'distance_from_shop' => $distance,
                    'is_within_allowed_distance' => $isWithinRange,
                    'visited_at' => now(),
                ]);

                if (! $isWithinRange) {
                    GpsAlert::create([
                        'visit_id' => $visit->id,
                        'distributor_id' => $distributor->id,
                        'shop_id' => $shop->id,
                        'distance' => $distance,
                        'allowed_distance' => self::MAX_DISTANCE_METERS,
                        'status' => 'pending',
                    ]);
                }

                if (! empty($samples)) {
                    $sample = Sample::create([
                        'visit_id' => $visit->id,
                        'distributor_id' => $distributor->id,
                        'shop_id' => $shop->id,
                        'given_at' => now(),
                    ]);

                    foreach ($samples as $entry) {
                        $quantity = floatval($entry['quantity']);
                        if ($quantity <= 0) {
                            continue;
                        }

                        $product = Product::find($entry['product_id']);
                        if (! $product) {
                            continue;
                        }

                        $unit = $product->isWeighedInKg() ? 'g' : $product->unit;
                        $quantityStockUnit = $this->stock->convertToStockUnit($product, $quantity, $unit);

                        $sampleItem = SampleItem::create([
                            'sample_id' => $sample->id,
                            'product_id' => $product->id,
                            'quantity_input' => $quantity,
                            'input_unit' => $unit,
                            'quantity_stock_unit' => $quantityStockUnit,
                        ]);

                        $this->stock->deductForSample($distributor, $product, $quantity, $unit, [
                            'shop_id' => $shop->id,
                            'visit_id' => $visit->id,
                            'sample_item_id' => $sampleItem->id,
                        ], $user);
                    }
                }

                $distributor->update([
                    'last_latitude' => $request->latitude,
                    'last_longitude' => $request->longitude,
                    'last_location_at' => now(),
                ]);

                return $visit;
            });
        } catch (InsufficientStockException $e) {
            $isGrams = $e->product->isWeighedInKg();

            return response()->json([
                'message' => __('admin.insufficient_stock_sample', [
                    'available' => rtrim(rtrim(number_format($isGrams ? $e->available * 1000 : $e->available, 3), '0'), '.'),
                    'unit' => $isGrams ? __('admin.gram_unit') : $e->product->unit,
                ]),
            ], 422);
        }

        return response()->json([
            'message' => 'Visite enregistrée.',
            'gps_alert' => ! $isWithinRange,
            'visit_id' => $visit->id,
        ]);
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
