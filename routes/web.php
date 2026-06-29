<?php

use App\Http\Controllers\Backend\IndexController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\BrandController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\ContctController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\LandingController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\SellerController;
use App\Http\Controllers\Frontend\SupportController;
use App\Http\Controllers\Frontend\TermController;
use App\Http\Controllers\Frontend\SitemapController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Controllers\Saas\IndexController as SaasIndexController;
use App\Http\Controllers\Saas\MasterBrandController;
use App\Http\Middleware\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;







 Route::domain('{store}.dorja.io')->middleware(SubdomainMiddleware::class)->group(function () {
// Route::domain('{store}.kiron-backend.test')->middleware(SubdomainMiddleware::class)->group(function () {


    //landing page
    Route::get('/sale/{slug}', [LandingController::class, 'index'])->name('landing');
    Route::post('/landing-order', [LandingController::class, 'storeLandingOrder'])->name('landing.order.store');
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/blogs', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'blogDetails'])->name('blog.details');

    Route::get('/contact', [ContctController::class, 'index'])->name('contact.index');
    Route::post('/contact/send', [ContctController::class, 'send'])->name('contact.send');

    Route::get('/support', [SupportController::class, 'index'])->name('support.index');


    Route::get('/brands', [BrandController::class, 'index'])->name('brand.index');
    Route::get('/brand/{slug}', [ProductController::class, 'brandProducts'])->name('brand.products');

    Route::get('/register', [AuthController::class, 'register'])->name('user.register');
    Route::post('/register', [AuthController::class, 'storeRegister'])->name('user.register.store');
    Route::get('/login', [AuthController::class, 'login'])->name('user.login');
    Route::post('/login', [AuthController::class, 'storeLogin'])->name('user.login.store');
    Route::middleware(['auth:customer'])->group(function () {
        Route::get('/profile', [AuthController::class, 'profile'])->name('user.profile');
        Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('user.profile.update');
        Route::post('/password/update', [AuthController::class, 'updatePassword'])->name('user.password.update');
        Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('user.dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('user.logout');
        Route::get('/order/details/{id}', [OrderController::class, 'orderDetails'])->name('user.order.details');
        Route::post('/order/return/{id}', [OrderController::class, 'requestReturn'])->name('order.return');
        Route::post('/order/review/store', [OrderController::class, 'storeReview'])->name('user.review.store');
    });
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/category/{slug}', [ProductController::class, 'categoryProducts'])->name('category.products');
    Route::get('/product/{slug}', [ProductController::class, 'productDetails'])->name('product.details');
    Route::get('/shop', [ProductController::class, 'index'])->name('shop.index');
    Route::get('/product-variation/{id}', [ProductController::class, 'getVariationModal']);
    Route::get('/flash-sale', [ProductController::class, 'flashSale'])->name('flash.sale');
    Route::get('/search-suggestions', [ProductController::class, 'searchSuggestions'])->name('search.suggestions');
    Route::get('/carts', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::get('/cart/remove/{rowId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/coupon/apply', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::get('/coupon/remove', [CartController::class, 'removeCoupon'])->name('coupon.remove');
    Route::post('/cart/shipping', [CartController::class, 'updateShipping'])->name('cart.shipping');
    Route::get('/cart-drawer-items', [CartController::class, 'getCartDrawerItems'])->name('cart.drawer.items');

    Route::get('/checkout', [OrderController::class, 'index'])->name('checkout.index');
    Route::post('/order/confirm', [OrderController::class, 'storeOrder'])->name('order.store');
    Route::post('/order/partial-save', [OrderController::class, 'partialSave'])->name('order.partial');

    Route::get('/invoice/{id}', [OrderController::class, 'invoice'])->name('order.invoice');
    Route::get('/invoice/download/{id}', [OrderController::class, 'invoice'])->name('invoice.download');
    Route::get('/product-track', [OrderController::class, 'trackOrder'])->name('order.track');
    Route::get('/order/reviews/{id}', [OrderController::class, 'getReviews'])->name('order.reviews');
    Route::get('/page/{slug}', [AboutController::class, 'showPage'])->name('frontend.page');
    Route::post('/order/payment/submit', [OrderController::class, 'submitPayment'])->name('order.payment.submit');
    Route::post('/newsletter-subscribe', [HomeController::class, 'subscribe'])->name('newsletter.subscribe');

    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
    Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots.txt');

    // Route::get('/kiron', [IndexController::class, 'index'])->name('kiron.index');
});
Route::get('/', [SaasIndexController::class, 'home'])->name('saas.index');
