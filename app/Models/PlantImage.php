<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PlantImage extends Model
{
    protected $guarded = [];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
