<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignSequence extends Model
{
    protected $fillable = ['campaign_id', 'delay_hours', 'channel', 'subject', 'message', 'order'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
