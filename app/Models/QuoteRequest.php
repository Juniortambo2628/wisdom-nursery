<?php

namespace App\Models;

use App\Enums\QuoteStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteRequest extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['status' => QuoteStatus::class];
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }
}
