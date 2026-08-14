<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name_ar',
        'name_fr',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function distributionItems(): HasMany
    {
        return $this->hasMany(DistributionItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function sampleItems(): HasMany
    {
        return $this->hasMany(SampleItem::class);
    }

    public function isWeighedInKg(): bool
    {
        return $this->unit === 'kg';
    }

    public function getTranslatedNameAttribute(): string
    {
        if (app()->getLocale() === 'fr' && $this->name_fr) {
            return $this->name_fr;
        }

        return $this->name_ar;
    }
}
