<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Plant;
use App\Support\Cart;
use App\Support\QuoteList;
use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlantController extends Controller
{
    public function index(Request $request, Cart $cart, QuoteList $quoteList): View
    {
        $categories = Category::orderBy('sort_order')->get();
        $active = $categories->firstWhere('slug', $request->query('category'));
        $search = trim((string) $request->query('q'));
        $view = $request->query('view') === 'list' ? 'list' : 'grid'; // grid is the default

        $plants = Plant::active()->with('primaryImage', 'category')
            ->when($active, fn ($q) => $q->where('category_id', $active->id))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('botanical_name', 'like', "%{$search}%")))
            ->ordered()->get();

        return view('pages.plants.index', [
            'categories' => $categories,
            'activeCategory' => $active,
            'search' => $search,
            'view' => $view,
            'plants' => $plants,
            'updatedAt' => Plant::active()->max('updated_at'),
            'cart' => $cart,
            'cartLines' => $lines = $cart->lines(),
            'cartWhatsapp' => $lines->isNotEmpty() ? WhatsApp::forCart($lines, $lines->sum('lineTotal')) : null,
            'quoteList' => $quoteList,
            'quoteLines' => $quoteList->lines(),
        ]);
    }

    public function show(Plant $plant, Cart $cart): View
    {
        abort_unless($plant->is_active, 404);
        $plant->load('images', 'category');

        $pairings = $plant->pairings()->active()->with('primaryImage', 'category')->limit(4)->get();
        if ($pairings->isEmpty()) {
            $pairings = Plant::active()->with('primaryImage', 'category')
                ->where('category_id', $plant->category_id)->whereKeyNot($plant->id)
                ->ordered()->limit(4)->get();
        }

        return view('pages.plants.show', [
            'plant' => $plant,
            'pairings' => $pairings,
            'inCart' => $cart->has($plant),
            'whatsapp' => WhatsApp::forPlant($plant),
        ]);
    }
}
