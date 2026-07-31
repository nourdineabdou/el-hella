<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorGoal extends Model
{
    protected $fillable = [
        'distributor_id',
        'goal_date',
        'target_visits',
        'target_distributions',
        'target_quantity',
        'observation',
    ];

    protected $casts = [
        'goal_date' => 'date',
        'target_quantity' => 'decimal:3',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }
}
