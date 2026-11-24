<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\StockAlertController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/how-we-grow', [PageController::class, 'howWeGrow'])->name('how-we-grow');
Route::get('/gallery', [PageController::class, 'gallery'])->name('gallery');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

Route::get('/plants', [PlantController::class, 'index'])->name('plants.index');
Route::get('/plants/{plant:slug}', [PlantController::class, 'show'])->name('plants.show');

Route::get('/grower-tips', [PostController::class, 'index'])->name('posts.index');
Route::get('/grower-tips/{post:slug}', [PostController::class, 'show'])->name('posts.show');

Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart', [CartController::class, 'update'])->name('cart.update');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');

Route::get('/orders/{order:public_token}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/orders/{order:public_token}/status', [OrderController::class, 'status'])->middleware('throttle:60,1')->name('orders.status');
Route::post('/orders/{order:public_token}/mpesa-code', [OrderController::class, 'submitCode'])->middleware('throttle:forms')->name('orders.mpesa-code');

Route::get('/quote', [QuoteController::class, 'show'])->name('quote.show');
Route::post('/quote/items', [QuoteController::class, 'setItem'])->name('quote.items');
Route::post('/quote', [QuoteController::class, 'store'])->middleware('throttle:forms')->name('quote.store');

Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:forms')->name('contact.store');
Route::post('/plants/{plant:slug}/notify', [StockAlertController::class, 'store'])->middleware('throttle:forms')->name('plants.notify');

Route::get('/robots.txt', [\App\Http\Controllers\SeoController::class, 'robots']);
Route::get('/sitemap.xml', [\App\Http\Controllers\SeoController::class, 'sitemap'])->name('sitemap');
