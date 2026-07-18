<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaVersion extends Model
{
    protected $fillable = ['formula_id', 'version', 'materials_snapshot', 'cost_per_ml', 'created_by', 'notes'];

    protected function casts(): array
    {
        return [
            'materials_snapshot' => 'array',
            'cost_per_ml' => 'decimal:4',
        ];
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
