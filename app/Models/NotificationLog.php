<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationLog extends Model
{
    use MassPrunable;

    protected $guarded = [];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** Kept for a year so missed alerts can be traced. */
    public function prunable()
    {
        return static::where('created_at', '<', now()->subYear());
    }
}
