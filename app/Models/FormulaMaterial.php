<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FormulaMaterial extends Pivot
{
    protected $table = 'formula_material';

    protected $fillable = [
        'formula_id', 'material_id',
        'persentase', 'gram', 'note_posisi',
    ];

    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
