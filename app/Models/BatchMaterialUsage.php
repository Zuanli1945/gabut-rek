<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchMaterialUsage extends Model
{
    protected $fillable = ['batch_production_id', 'material_id', 'quantity_used', 'cost'];

    public function batchProduction(): BelongsTo
    {
        return $this->belongsTo(BatchProduction::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
