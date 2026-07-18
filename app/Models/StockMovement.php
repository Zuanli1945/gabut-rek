<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = ['material_id', 'type', 'quantity', 'reference_type', 'reference_id', 'notes'];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
