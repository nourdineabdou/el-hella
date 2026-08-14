<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Distributor;
use App\Models\DistributorStockDay;
use App\Models\DistributorStockItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Centralizes every stock balance change so ShopController (sales, samples)
 * and the distributor stock screens all go through the same rules: one open
 * "day" per distributor, row-level locking on the per-product balance, and
 * a full stock_movements ledger entry for every change.
 */
class StockService
{
    /**
     * Accepts "2,5" or "2.5" — distributors type with whichever separator
     * their keyboard defaults to.
     */
    public static function normalizeDecimal(string $value): float
    {
        return (float) str_replace(',', '.', trim($value));
    }

    public function convertToStockUnit(Product $product, float $quantityInput, string $inputUnit): float
    {
        if ($product->isWeighedInKg() && $inputUnit === 'g') {
            return round($quantityInput / 1000, 4);
        }

        return round($quantityInput, 4);
    }

    public function openOrCurrentDay(Distributor $distributor): DistributorStockDay
    {
        $open = $distributor->openStockDay();

        if ($open) {
            return $open;
        }

        return DistributorStockDay::create([
            'distributor_id' => $distributor->id,
            'stock_date' => now()->toDateString(),
        ]);
    }

    /**
     * Record a stock reception. Quantity is always in the product's own
     * unit (no conversion — receptions aren't given in grams).
     */
    public function receive(Distributor $distributor, Product $product, float $quantity, User $user): StockMovement
    {
        return DB::transaction(function () use ($distributor, $product, $quantity, $user) {
            $day = $this->openOrCurrentDay($distributor);
            $item = $this->lockedItem($day, $product);

            $before = (float) $item->current_quantity;
            $after = round($before + $quantity, 4);

            $item->update([
                'received_quantity' => round((float) $item->received_quantity + $quantity, 4),
                'current_quantity' => $after,
            ]);

            return StockMovement::create([
                'distributor_id' => $distributor->id,
                'distributor_stock_day_id' => $day->id,
                'product_id' => $product->id,
                'movement_type' => 'entree_stock',
                'quantity_input' => $quantity,
                'input_unit' => $product->unit,
                'quantity_stock_unit' => $quantity,
                'balance_before' => $before,
                'balance_after' => $after,
                'created_by' => $user->id,
            ]);
        });
    }

    /**
     * Deduct a sale from stock, in the product's own unit (matches how
     * DistributionItem quantities already work — no conversion).
     *
     * @param  array{shop_id?: int, visit_id?: int, distribution_item_id?: int}  $context
     *
     * @throws InsufficientStockException
     */
    public function deductForSale(Distributor $distributor, Product $product, float $quantity, array $context, User $user): StockMovement
    {
        return $this->deduct($distributor, $product, 'vente', $quantity, $product->unit, $context, $user);
    }

    /**
     * Deduct a free sample from stock. $quantity/$unit are what the
     * distributor actually typed — grams for kg products, the product's
     * own unit otherwise — and get converted internally.
     *
     * @param  array{shop_id?: int, visit_id?: int, sample_item_id?: int}  $context
     *
     * @throws InsufficientStockException
     */
    public function deductForSample(Distributor $distributor, Product $product, float $quantity, string $unit, array $context, User $user): StockMovement
    {
        return $this->deduct($distributor, $product, 'echantillon', $quantity, $unit, $context, $user);
    }

    /**
     * @throws InsufficientStockException
     */
    private function deduct(
        Distributor $distributor,
        Product $product,
        string $movementType,
        float $quantityInput,
        string $inputUnit,
        array $context,
        User $user,
    ): StockMovement {
        return DB::transaction(function () use ($distributor, $product, $movementType, $quantityInput, $inputUnit, $context, $user) {
            $day = $this->openOrCurrentDay($distributor);
            $item = $this->lockedItem($day, $product);

            $quantityStockUnit = $this->convertToStockUnit($product, $quantityInput, $inputUnit);
            $before = (float) $item->current_quantity;

            if ($quantityStockUnit > $before) {
                throw new InsufficientStockException($product, $before, $quantityStockUnit);
            }

            $after = round($before - $quantityStockUnit, 4);
            $item->update(['current_quantity' => $after]);

            return StockMovement::create([
                'distributor_id' => $distributor->id,
                'distributor_stock_day_id' => $day->id,
                'product_id' => $product->id,
                'movement_type' => $movementType,
                'quantity_input' => $quantityInput,
                'input_unit' => $inputUnit,
                'quantity_stock_unit' => -$quantityStockUnit,
                'balance_before' => $before,
                'balance_after' => $after,
                'shop_id' => $context['shop_id'] ?? null,
                'visit_id' => $context['visit_id'] ?? null,
                'distribution_item_id' => $context['distribution_item_id'] ?? null,
                'sample_item_id' => $context['sample_item_id'] ?? null,
                'created_by' => $user->id,
            ]);
        });
    }

    /**
     * Check without mutating anything — used to validate a whole sale/sample
     * basket up front so we can reject it before touching the database.
     */
    public function available(Distributor $distributor, Product $product): float
    {
        $day = $distributor->openStockDay();

        if (! $day) {
            return 0.0;
        }

        $item = DistributorStockItem::where('distributor_stock_day_id', $day->id)
            ->where('product_id', $product->id)
            ->first();

        return $item ? (float) $item->current_quantity : 0.0;
    }

    /**
     * Close the distributor's open day: records the physically-returned
     * quantity per product (for the théorique/physique/écart comparison)
     * and locks the day against further movements.
     *
     * @param  array<int, float>  $returnedByProductId  product_id => returned quantity
     */
    public function closeDay(DistributorStockDay $day, array $returnedByProductId, User $user): DistributorStockDay
    {
        return DB::transaction(function () use ($day, $returnedByProductId, $user) {
            $day = DistributorStockDay::whereKey($day->id)->lockForUpdate()->firstOrFail();

            if (! $day->isOpen()) {
                throw new \RuntimeException('This stock day is already closed.');
            }

            foreach ($day->items()->lockForUpdate()->get() as $item) {
                $returned = round((float) ($returnedByProductId[$item->product_id] ?? 0), 4);
                $before = (float) $item->current_quantity;

                $item->update(['returned_quantity' => $returned]);

                StockMovement::create([
                    'distributor_id' => $day->distributor_id,
                    'distributor_stock_day_id' => $day->id,
                    'product_id' => $item->product_id,
                    'movement_type' => 'retour_stock',
                    'quantity_input' => $returned,
                    'input_unit' => $item->product->unit,
                    'quantity_stock_unit' => $returned,
                    'balance_before' => $before,
                    'balance_after' => $before,
                    'created_by' => $user->id,
                ]);
            }

            $day->update(['closed_at' => now()]);

            return $day->fresh();
        });
    }

    private function lockedItem(DistributorStockDay $day, Product $product): DistributorStockItem
    {
        $item = DistributorStockItem::where('distributor_stock_day_id', $day->id)
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->first();

        return $item ?? DistributorStockItem::create([
            'distributor_stock_day_id' => $day->id,
            'product_id' => $product->id,
        ]);
    }
}
