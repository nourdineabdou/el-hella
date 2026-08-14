<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Distributor extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'phone',
        'zone',
        'region',
        'last_latitude',
        'last_longitude',
        'last_location_at',
        'is_active',
    ];

    protected $casts = [
        'last_latitude' => 'decimal:7',
        'last_longitude' => 'decimal:7',
        'last_location_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(DistributorGoal::class);
    }

    public function gpsAlerts(): HasMany
    {
        return $this->hasMany(GpsAlert::class);
    }

    public function stockDays(): HasMany
    {
        return $this->hasMany(DistributorStockDay::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class);
    }

    /**
     * The distributor's single open stock day, if any — see StockService,
     * which is responsible for opening/closing days.
     */
    public function openStockDay(): ?DistributorStockDay
    {
        return $this->stockDays()->whereNull('closed_at')->first();
    }
}
