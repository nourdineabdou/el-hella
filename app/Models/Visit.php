<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    protected $fillable = [
        'distributor_id',
        'shop_id',
        'visit_type',
        'without_distribution_reason',
        'observation',
        'latitude',
        'longitude',
        'zone',
        'gps_accuracy',
        'distance_from_shop',
        'is_within_allowed_distance',
        'visited_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'gps_accuracy' => 'decimal:2',
        'distance_from_shop' => 'decimal:2',
        'is_within_allowed_distance' => 'boolean',
        'visited_at' => 'datetime',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function distribution(): HasOne
    {
        return $this->hasOne(Distribution::class);
    }

    public function gpsAlert(): HasOne
    {
        return $this->hasOne(GpsAlert::class);
    }

    protected function formattedDistance(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->distance_from_shop === null) {
                    return null;
                }

                $distance = (float) $this->distance_from_shop;

                if ($distance > 1000) {
                    return number_format($distance / 1000, 1).' '.__('km');
                }

                return number_format($distance, 0).' '.__('m');
            },
        );
    }
}
