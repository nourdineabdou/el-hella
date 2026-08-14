<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorStockItem extends Model
{
    protected $fillable = [
        'distributor_stock_day_id',
        'product_id',
        'received_quantity',
        'current_quantity',
        'returned_quantity',
    ];

    protected $casts = [
        'received_quantity' => 'decimal:3',
        'current_quantity' => 'decimal:3',
        'returned_quantity' => 'decimal:3',
    ];

    public function stockDay(): BelongsTo
    {
        return $this->belongsTo(DistributorStockDay::class, 'distributor_stock_day_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Écart = physique - théorique. Négatif = manquant, positif = surplus.
     */
    public function getDiscrepancyAttribute(): ?float
    {
        if ($this->returned_quantity === null) {
            return null;
        }

        return round((float) $this->returned_quantity - (float) $this->current_quantity, 3);
    }
}
