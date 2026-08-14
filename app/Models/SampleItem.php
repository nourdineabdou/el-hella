<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleItem extends Model
{
    protected $fillable = [
        'sample_id',
        'product_id',
        'quantity_input',
        'input_unit',
        'quantity_stock_unit',
    ];

    protected $casts = [
        'quantity_input' => 'decimal:3',
        'quantity_stock_unit' => 'decimal:3',
    ];

    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
