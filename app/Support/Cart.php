<?php

namespace App\Support;

use App\Models\Plant;
use Illuminate\Support\Collection;

/**
 * Session cart. Lines are keyed "<plant id>:<buy as>" where buy as is
 * "single" or "set" (a set of N plants at the plant's set price).
 */
class Cart
{
    private const KEY = 'cart';

    public const MAX_QTY = 99;

    /** @return array<string,int> */
    public function raw(): array
    {
        return session(self::KEY, []);
    }

    public function add(Plant $plant, int $qty = 1, string $buyAs = 'single'): void
    {
        $buyAs = $buyAs === 'set' && $plant->set_size && $plant->set_price ? 'set' : 'single';
        $lines = $this->raw();
        $key = $plant->id.':'.$buyAs;
        $lines[$key] = min(self::MAX_QTY, ($lines[$key] ?? 0) + max(1, $qty));
        session([self::KEY => $lines]);
    }

    public function update(string $key, int $qty): void
    {
        $lines = $this->raw();
        if (! array_key_exists($key, $lines)) {
            return;
        }
        if ($qty <= 0) {
            unset($lines[$key]);
        } else {
            $lines[$key] = min(self::MAX_QTY, $qty);
        }
        session([self::KEY => $lines]);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    public function has(Plant $plant): bool
    {
        foreach (array_keys($this->raw()) as $key) {
            if ((int) strtok($key, ':') === $plant->id) {
                return true;
            }
        }

        return false;
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    /**
     * Priced lines, re-read from the database every time so a price change
     * in the dashboard applies at once. Lines whose plant is gone or no
     * longer buyable are dropped.
     *
     * @return Collection<int, object{key:string, plant:Plant, buyAs:string, qty:int, unitPrice:int, unitsPerItem:int, lineTotal:int, label:string}>
     */
    public function lines(): Collection
    {
        $raw = $this->raw();
        $ids = array_unique(array_map(fn ($k) => (int) strtok($k, ':'), array_keys($raw)));
        $plants = Plant::with('primaryImage', 'category')->whereIn('id', $ids)->get()->keyBy('id');

        return collect($raw)->map(function (int $qty, string $key) use ($plants) {
            [$id, $buyAs] = explode(':', $key) + [1 => 'single'];
            $plant = $plants->get((int) $id);
            if (! $plant || ! $plant->isBuyable()) {
                return null;
            }
            $isSet = $buyAs === 'set' && $plant->set_size && $plant->set_price;
            $unit = $isSet ? $plant->set_price : $plant->price;

            return (object) [
                'key' => $key,
                'plant' => $plant,
                'buyAs' => $isSet ? 'set' : 'single',
                'qty' => $qty,
                'unitPrice' => $unit,
                'unitsPerItem' => $isSet ? $plant->set_size : 1,
                'lineTotal' => $unit * $qty,
                'label' => $isSet ? "{$plant->name} (set of {$plant->set_size})" : $plant->name,
            ];
        })->filter()->values();
    }

    public function subtotal(): int
    {
        return $this->lines()->sum('lineTotal');
    }
}
