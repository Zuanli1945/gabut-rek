<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbandonedCart extends Model
{
    protected $fillable = ['user_id', 'cart_data', 'total', 'status', 'abandoned_at', 'email_sent_at', 'wa_sent_at', 'recovered_at'];

    protected function casts(): array
    {
        return [
            'cart_data' => 'array',
            'total' => 'decimal:2',
            'abandoned_at' => 'datetime',
            'email_sent_at' => 'datetime',
            'wa_sent_at' => 'datetime',
            'recovered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
