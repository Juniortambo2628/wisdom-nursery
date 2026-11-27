<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\Post;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $body = app()->isProduction()
            ? "User-agent: *\nDisallow: /admin\nDisallow: /cart\nDisallow: /checkout\nDisallow: /orders/\n\nSitemap: ".route('sitemap')."\n"
            : "User-agent: *\nDisallow: /\n"; // staging and local are never indexed

        return response($body, 200, ['Content-Type' => 'text/plain']);
    }

    public function sitemap(): Response
    {
        $urls = collect(['home', 'plants.index', 'about', 'services', 'how-we-grow', 'gallery', 'posts.index', 'contact', 'quote.show'])
            ->map(fn ($r) => ['loc' => route($r), 'lastmod' => null])
            ->concat(Plant::active()->get(['slug', 'updated_at'])->map(fn ($p) => ['loc' => route('plants.show', $p), 'lastmod' => $p->updated_at]))
            ->concat(Post::published()->get(['slug', 'updated_at'])->map(fn ($p) => ['loc' => route('posts.show', $p), 'lastmod' => $p->updated_at]));

        return response()->view('sitemap', ['urls' => $urls], 200, ['Content-Type' => 'application/xml']);
    }
}
