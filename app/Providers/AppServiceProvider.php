<?php

namespace App\Providers;

use App\Support\Cart;
use App\Support\QuoteList;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Cart::class);
        $this->app->scoped(QuoteList::class);
    }

    public function boot(): void
    {
        // Public forms and checkout (handoff 7: rate limit forms and the STK endpoint).
        RateLimiter::for('forms', fn (Request $r) => Limit::perMinute(5)->by($r->ip()));
        RateLimiter::for('checkout', fn (Request $r) => [
            Limit::perMinute(5)->by($r->ip()),
            Limit::perHour(30)->by($r->ip()),
        ]);

        View::composer('components.layouts.site', function ($view) {
            $view->with('cartCount', app(Cart::class)->count())
                ->with('quoteCount', count(app(QuoteList::class)->raw()));
        });
    }
}
