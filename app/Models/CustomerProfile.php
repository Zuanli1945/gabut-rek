<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerProfile extends Model
{
    protected $fillable = ['user_id', 'total_spent', 'total_orders', 'last_order_at', 'preferred_scent_families', 'notes'];

    protected function casts(): array
    {
        return [
            'total_spent' => 'decimal:2',
            'last_order_at' => 'datetime',
            'preferred_scent_families' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function abandonedCarts(): HasMany
    {
        return $this->hasMany(AbandonedCart::class, 'user_id', 'user_id');
    }
}
