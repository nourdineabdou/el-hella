<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shop_number',
        'name',
        'owner_name',
        'phone',
        'wilaya',
        'moughataa',
        'district',
        'address',
        'latitude',
        'longitude',
        'location_updated_at',
        'location_source',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'location_updated_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    public function locationRequests(): HasMany
    {
        return $this->hasMany(ShopLocationRequest::class);
    }

    public function gpsAlerts(): HasMany
    {
        return $this->hasMany(GpsAlert::class);
    }

    public function hasLocation(): bool
    {
        return $this->latitude !== null
            && $this->longitude !== null;
    }
}
