<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlert extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['notified_at' => 'datetime'];
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }
}
