<?php

use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\ContctController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\SupportController;
use App\Http\Controllers\Frontend\TermController;
use App\Http\Middleware\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;


Route::domain('{store}.kiron-backend.test')->middleware(SubdomainMiddleware::class)->group(function () {

    Route::get('/', [HomeController::class, 'index']);
    Route::get('/blogs', [BlogController::class, 'index']);
    Route::get('/blog/{slug}', [BlogController::class, 'blogDetails'])->name('blog.details');

    Route::get('/contact', [ContctController::class, 'index'])->name('contact.index');
    Route::post('/contact/send', [ContctController::class, 'send'])->name('contact.send');

    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::get('/terms', [TermController::class, 'index'])->name('term.index');
    Route::get('/privacy', [TermController::class, 'privacy'])->name('privacy.index');

    Route::get('/about', function () {
        return view('template1.frontend.about');
    });
    Route::get('/apply-seller', function () {
        return view('template1.frontend.apply-seller');
    });

    Route::get('/brands', function () {
        return view('template1.frontend.brand');
    });
    Route::get('/carts', function () {
        return view('template1.frontend.cart');
    });
    Route::get('/checkout', function () {
        return view('template1.frontend.checkout');
    });
    Route::get('/invoice', function () {
        return view('template1.frontend.invoice');
    });
    Route::get('/order-details', function () {
        return view('template1.frontend.order-details');
    });

    Route::get('/product-details', function () {
        return view('template1.frontend.product-details');
    });
    Route::get('/product-track', function () {
        return view('template1.frontend.product-track');
    });
    Route::get('/shop', function () {
        return view('template1.frontend.shop');
    });


    Route::get('/wishlist', function () {
        return view('template1.frontend.wishlist');
    });
    Route::get('/login', function () {
        return view('template1.frontend.user.login');
    });
    Route::get('/register', function () {
        return view('template1.frontend.user.register');
    });
    Route::get('/profile', function () {
        return view('template1.frontend.user.profile');
    });
    Route::get('/dashboard', function () {
        return view('template1.frontend.user.dashboard');
    });
});
