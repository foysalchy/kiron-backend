<?php

use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\BrandController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\ContctController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\SellerController;
use App\Http\Controllers\Frontend\SupportController;
use App\Http\Controllers\Frontend\TermController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Middleware\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;


Route::domain('{store}.kiron-backend.test')->middleware(SubdomainMiddleware::class)->group(function () {

    Route::get('/', [HomeController::class, 'index']);
    Route::get('/blogs', [BlogController::class, 'index']);
    Route::get('/blog/{slug}', [BlogController::class, 'blogDetails'])->name('blog.details');

    Route::get('/contact', [ContctController::class, 'index'])->name('contact.index');
    Route::post('/contact/send', [ContctController::class, 'send'])->name('contact.send');

    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support/send', [SupportController::class, 'storeMessage'])->name('support.send');
    Route::get('/terms', [TermController::class, 'index'])->name('term.index');
    Route::get('/privacy', [TermController::class, 'privacy'])->name('privacy.index');

    Route::get('/brands',[BrandController::class,'index'])->name('brand.index');
    Route::get('/about',[AboutController::class,'index'])->name('about.index');

    Route::get('/register', [AuthController::class,'register'])->name('user.register');
    Route::post('/register', [AuthController::class,'storeRegister'])->name('user.register.store');
    Route::get('/login', [AuthController::class,'login'])->name('user.login');
    Route::post('/login', [AuthController::class, 'storeLogin'])->name('user.login.store');
    Route::middleware(['auth:customer'])->group(function () {
        Route::get('/profile', [AuthController::class,'profile'])->name('user.profile');
        Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('user.profile.update');
        Route::post('/password/update', [AuthController::class, 'updatePassword'])->name('user.password.update');
        Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('user.dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('user.logout');
        });
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/category/{slug}', [ProductController::class, 'categoryProducts'])->name('category.products');
    Route::get('/product/{slug}', [ProductController::class, 'productDetails'])->name('product.details');
    Route::get('/shop', [ProductController::class, 'index'])->name('shop.index');
    Route::get('/product-variation/{id}', [ProductController::class, 'getVariationModal']);
    Route::get('/carts', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::get('/cart/remove/{rowId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/coupon/apply', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::get('/coupon/remove', [CartController::class, 'removeCoupon'])->name('coupon.remove');
    Route::post('/cart/shipping', [CartController::class, 'updateShipping'])->name('cart.shipping');

    Route::get('/checkout', [OrderController::class,'index'])->name('checkout.index');
    Route::post('/order/confirm', [OrderController::class, 'storeOrder'])->name('order.store');
    Route::post('/order/partial-save', [OrderController::class, 'partialSave'])->name('order.partial');

    Route::get('/invoice', function () {
        return view('template1.frontend.invoice');
    });
    Route::get('/order-details', function () {
        return view('template1.frontend.order-details');
    });


    Route::get('/product-track', function () {
        return view('template1.frontend.product-track');
    });


});
