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
            'testimonial' => Testimonial::where('is_active', true)->orderBy('sort_order')->first(),
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
