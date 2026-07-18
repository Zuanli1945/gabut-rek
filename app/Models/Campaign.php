<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = ['name', 'type', 'trigger_event', 'status', 'segment_filter'];

    protected function casts(): array
    {
        return ['segment_filter' => 'array'];
    }

    public function sequences(): HasMany
    {
        return $this->hasMany(CampaignSequence::class);
    }
}
