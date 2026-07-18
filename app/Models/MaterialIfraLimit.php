<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialIfraLimit extends Model
{
    protected $fillable = ['material_id', 'category', 'max_percent'];

    protected function casts(): array
    {
        return ['max_percent' => 'decimal:4'];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
