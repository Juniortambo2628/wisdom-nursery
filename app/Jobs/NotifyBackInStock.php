<?php

namespace App\Jobs;

use App\Models\Plant;
use App\Models\StockAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Texts everyone who asked "Notify me" for a plant that is buyable again. Each person once. */
class NotifyBackInStock implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(public Plant $plant)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        if (! $this->plant->fresh()?->isBuyable()) {
            return; // sold out again before the queue ran
        }

        $text = "Good news: {$this->plant->name} is back in stock at Wisdom Propagation Nursery, "
            .ksh($this->plant->price).'. Order: '.route('plants.show', $this->plant);

        StockAlert::where('plant_id', $this->plant->id)->whereNull('notified_at')->each(function (StockAlert $alert) use ($text) {
            SendAlert::dispatch('sms', $alert->phone, 'back_in_stock', $text, $this->plant);
            $alert->update(['notified_at' => now()]);
        });
    }
}
