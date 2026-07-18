<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchProduction extends Model
{
    protected $fillable = [
        'formula_id', 'formula_version_id', 'batch_volume_ml', 'jumlah_unit',
        'status', 'operator_id', 'scheduled_date', 'completed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'batch_volume_ml' => 'decimal:2',
            'scheduled_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class);
    }

    public function formulaVersion(): BelongsTo
    {
        return $this->belongsTo(FormulaVersion::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function materialUsages(): HasMany
    {
        return $this->hasMany(BatchMaterialUsage::class);
    }
}
