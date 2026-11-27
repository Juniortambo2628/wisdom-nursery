<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\GalleryImage;
use App\Models\Partner;
use App\Models\Plant;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $categories = Category::query()
            ->withCount(['plants' => fn ($q) => $q->active()])
            ->withMin(['plants' => fn ($q) => $q->active()], 'price')
            ->orderBy('sort_order')->get();

        $popular = Plant::active()->with('primaryImage', 'category')
            ->orderByDesc('is_featured')->ordered()->limit(4)->get();

        return view('pages.home', [
            'categories' => $categories,
            'popular' => $popular,
            'gallery' => GalleryImage::where('is_active', true)->orderBy('sort_order')->limit(5)->get(),
            // Placeholders ([Customer …]) are left out, so the section only appears with real quotes.
            'testimonials' => Testimonial::where('is_active', true)->where('quote', 'not like', '[%')->orderBy('sort_order')->get(),
            'partners' => Partner::orderBy('sort_order')->get(),
        ]);
    }

    public function about(): View
    {
        return view('pages.about', [
            'team' => TeamMember::where('is_active', true)->orderBy('sort_order')->get(),
            'gallery' => GalleryImage::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    /** Our services: the five services, as an expanding card row (text and photos in Site settings → Services). */
    public function services(): View
    {
        $links = [
            1 => [route('plants.index', ['category' => 'vegetable-seedlings']), 'See seedlings'],
            2 => [route('plants.index', ['category' => 'fruit-trees']), 'See fruit trees'],
            3 => [route('contact'), 'Talk to us'],
            4 => [route('posts.index'), 'Read grower tips'],
            5 => [route('plants.index', ['category' => 'ornamentals-palms']), 'See ornamentals'],
        ];

        $services = collect($links)->map(fn ($link, $i) => [
            'title' => setting("service_{$i}_title"),
            'text' => setting("service_{$i}_text"),
            'image' => site_image("service_{$i}_image"),
            'srcset' => site_image_srcset("service_{$i}_image"),
            'url' => $link[0],
            'action' => $link[1],
        ])->filter(fn ($s) => filled($s['title']))->values();

        return view('pages.services', ['services' => $services]);
    }

    public function howWeGrow(): View
    {
        return view('pages.how-we-grow');
    }

    public function gallery(): View
    {
        return view('pages.gallery', [
            'gallery' => GalleryImage::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact');
    }
}
