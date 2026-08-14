<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'distributor_id',
        'distributor_stock_day_id',
        'product_id',
        'movement_type',
        'quantity_input',
        'input_unit',
        'quantity_stock_unit',
        'balance_before',
        'balance_after',
        'shop_id',
        'visit_id',
        'distribution_item_id',
        'sample_item_id',
        'created_by',
    ];

    protected $casts = [
        'quantity_input' => 'decimal:3',
        'quantity_stock_unit' => 'decimal:3',
        'balance_before' => 'decimal:3',
        'balance_after' => 'decimal:3',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function stockDay(): BelongsTo
    {
        return $this->belongsTo(DistributorStockDay::class, 'distributor_stock_day_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function distributionItem(): BelongsTo
    {
        return $this->belongsTo(DistributionItem::class);
    }

    public function sampleItem(): BelongsTo
    {
        return $this->belongsTo(SampleItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getReferenceAttribute(): string
    {
        return 'MVT-'.$this->id;
    }
}
