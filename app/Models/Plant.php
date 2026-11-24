<?php

namespace App\Models;

use App\Enums\Availability;
use App\Enums\SoldAs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Plant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'availability' => Availability::class,
            'sold_as' => SoldAs::class,
            'ready_on' => 'date',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'price' => 'integer',
            'stock_qty' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PlantImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(PlantImage::class)->orderBy('sort_order');
    }

    public function pairings(): BelongsToMany
    {
        return $this->belongsToMany(Plant::class, 'plant_pairings', 'plant_id', 'paired_plant_id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }

    /** Can be added to the cart and paid for right now. */
    public function isBuyable(): bool
    {
        return $this->is_active
            && $this->availability === Availability::Ready
            && $this->stock_qty > 0;
    }

    /** "Ready now" / "Ready in 3 weeks" / "Out of stock". */
    public function availabilityLabel(): string
    {
        if ($this->availability === Availability::Ready && $this->stock_qty <= 0) {
            return Availability::OutOfStock->label();
        }

        if ($this->availability === Availability::ReadyOn && $this->ready_on) {
            $weeks = (int) max(1, ceil(now()->startOfDay()->diffInDays($this->ready_on, false) / 7));

            return $weeks === 1 ? 'Ready in 1 week' : "Ready in {$weeks} weeks";
        }

        return $this->availability->label();
    }
}
