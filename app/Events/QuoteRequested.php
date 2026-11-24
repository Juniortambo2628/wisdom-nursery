<?php

namespace App\Events;

use App\Models\QuoteRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuoteRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(public QuoteRequest $quote) {}
}
