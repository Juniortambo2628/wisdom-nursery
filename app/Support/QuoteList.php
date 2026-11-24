<?php

namespace App\Support;

use App\Models\Plant;
use Illuminate\Support\Collection;

/** Session list of plants and quantities for a bulk quote. */
class QuoteList
{
    private const KEY = 'quote_list';

    public const MAX_QTY = 1_000_000;

    /** @return array<int,int> plant id => quantity */
    public function raw(): array
    {
        return session(self::KEY, []);
    }

    public function set(Plant $plant, int $qty): void
    {
        $list = $this->raw();
        if ($qty <= 0) {
            unset($list[$plant->id]);
        } else {
            $list[$plant->id] = min(self::MAX_QTY, $qty);
        }
        session([self::KEY => $list]);
    }

    public function has(Plant $plant): bool
    {
        return array_key_exists($plant->id, $this->raw());
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    /** @return Collection<int, object{plant:Plant, qty:int}> */
    public function lines(): Collection
    {
        $raw = $this->raw();
        $plants = Plant::with('category')->whereIn('id', array_keys($raw))->get()->keyBy('id');

        return collect($raw)
            ->map(fn (int $qty, int $id) => $plants->has($id) ? (object) ['plant' => $plants[$id], 'qty' => $qty] : null)
            ->filter()->values();
    }
}
