<?php

namespace App\Models;

use App\Enums\Fulfilment;
use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'fulfilment' => Fulfilment::class,
            'paid_at' => 'datetime',
            'ready_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'collected_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->public_token ??= Str::random(40);
        });

        // WN-1001, WN-1002… derived from the id so numbers never collide.
        static::created(function (Order $order) {
            if (! $order->number) {
                $order->forceFill([
                    'number' => 'WN-'.($order->id + config('nursery.order_number_offset')),
                ])->saveQuietly();
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function deliveryArea(): BelongsTo
    {
        return $this->belongsTo(DeliveryArea::class);
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    /** Move to a new status and stamp its timestamp column. */
    public function transitionTo(OrderStatus $status): void
    {
        $from = $this->status;
        $this->status = $status;
        if ($column = $status->timestampColumn()) {
            $this->{$column} ??= now();
        }
        $this->save();

        if ($from !== $status) {
            OrderStatusChanged::dispatch($this, $from);
        }
    }

    /** Statuses staff can move this order to next from the dashboard. */
    public function nextStatuses(): array
    {
        return match ($this->status) {
            OrderStatus::Paid => [OrderStatus::Ready],
            OrderStatus::Ready => $this->fulfilment === Fulfilment::Delivery ? [OrderStatus::Dispatched] : [OrderStatus::Collected],
            OrderStatus::Dispatched => [OrderStatus::Delivered],
            default => [],
        };
    }

    public function adminUrl(): string
    {
        return url('/admin/orders/'.$this->id);
    }
}
