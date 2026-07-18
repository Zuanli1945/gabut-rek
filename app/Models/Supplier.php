<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = ['name', 'contact', 'email', 'phone', 'lead_time_days', 'notes'];

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }
}
