<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Distribution extends Model
{
    protected $fillable = [
        'visit_id',
        'distributor_id',
        'shop_id',
        'total_quantity',
        'gps_status',
        'observation',
        'distributed_at',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'total_quantity' => 'decimal:3',
        'distributed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DistributionItem::class);
    }
}
