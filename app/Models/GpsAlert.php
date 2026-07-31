<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsAlert extends Model
{
    protected $fillable = [
        'visit_id',
        'distributor_id',
        'shop_id',
        'distance',
        'allowed_distance',
        'status',
        'distributor_justification',
        'admin_comment',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'distance' => 'decimal:2',
        'allowed_distance' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }
}
