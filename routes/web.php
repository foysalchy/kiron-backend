<?php

use App\Http\Controllers\Backend\IndexController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\BrandController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\ContctController;
use App\Http\Controllers\Frontend\CustomerPasswordResetController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\LandingController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\SellerController;
use App\Http\Controllers\Frontend\SupportController;
use App\Http\Controllers\Frontend\TermController;
use App\Http\Controllers\Frontend\SitemapController;
use App\Http\Controllers\Saas\SeoController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Controllers\Saas\IndexController as SaasIndexController;
use App\Http\Controllers\Saas\MasterBrandController;
use App\Http\Controllers\Saas\PartnerPortalController;
use App\Http\Middleware\SubdomainMiddleware;
use Illuminate\Support\Facades\Route;

// Partner & Referral Web Portal Routes
Route::get('/_preview_card', [App\Http\Controllers\Frontend\FrontendController::class, 'previewCard'])->name('preview.card');

Route::prefix('partner')->name('partner.')->group(function () {
    Route::get('/login', [PartnerPortalController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [PartnerPortalController::class, 'login'])->name('login.submit');
    Route::get('/register', [PartnerPortalController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [PartnerPortalController::class, 'register'])->name('register.submit');
    Route::get('/register/verify-email', [PartnerPortalController::class, 'showVerifyEmailForm'])->name('register.verify-form');
    Route::post('/register/verify-email', [PartnerPortalController::class, 'verifyEmailAndRegister'])->name('register.verify-submit');
    Route::post('/register/resend-otp', [PartnerPortalController::class, 'resendRegisterOtp'])->name('register.resend-otp');
    Route::post('/logout', [PartnerPortalController::class, 'logout'])->name('logout');

    // Partner Forgot & Reset Password
    Route::get('/forgot-password', [PartnerPortalController::class, 'showForgotPasswordForm'])->name('password.forgot');
    Route::post('/forgot-password/request-otp', [PartnerPortalController::class, 'requestPasswordResetOtp'])->name('password.request-otp');
    Route::get('/reset-password', [PartnerPortalController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [PartnerPortalController::class, 'resetPassword'])->name('password.update');

    Route::get('/dashboard', [PartnerPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/referrals', [PartnerPortalController::class, 'referrals'])->name('referrals');
    Route::get('/earnings', [PartnerPortalController::class, 'earnings'])->name('earnings');
    Route::get('/withdrawals', [PartnerPortalController::class, 'withdrawals'])->name('withdrawals');
    Route::post('/withdrawals/request', [PartnerPortalController::class, 'requestWithdrawal'])->name('withdrawals.request');
    Route::get('/profile', [PartnerPortalController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [PartnerPortalController::class, 'updateProfile'])->name('profile.update');
});

Route::get('/git-pull', function () {
    $output = shell_exec('cd /var/www/html/managesuite && git pull 2>&1');

    return response()->json([
        'status' => 'success',
        'output' => $output,
    ]);
});

Route::get('/partners', [SaasIndexController::class, 'partnerProgram'])->name('saas.partner');

Route::domain('dorja.io')->group(function () {
    Route::get('/', [SaasIndexController::class, 'home'])->name('saas.index');
    Route::get('/features', [SaasIndexController::class, 'features'])->name('saas.feature.list');
    Route::get('/features/{slug}', [SaasIndexController::class, 'featureDetails'])->name('saas.feature.details');
    Route::get('/blog', [SaasIndexController::class, 'blogPosts'])->name('saas.blog.list');
    Route::get('/blog/{slug}', [SaasIndexController::class, 'blogPostDetails'])->name('saas.blog.details');
    Route::get('/faqs', [SaasIndexController::class, 'faqList'])->name('saas.faq.list');
    Route::get('/pricing', [SaasIndexController::class, 'packageList'])->name('saas.package.list');
    Route::get('/partners', [SaasIndexController::class, 'partnerProgram'])->name('saas.partner.domain');
    Route::get('/contact', [SaasIndexController::class, 'contact'])->name('saas.contact');
    Route::post('/contact/send', [SaasIndexController::class, 'send'])->name('saas.contact.send');
    Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap.saas.index');
    Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots.saas.txt');
});


Route::middleware(SubdomainMiddleware::class)->group(function () {



    // Route::get('/sale/{slug}', [LandingController::class, 'index'])->name('landing');
    Route::post('/landing-order', [LandingController::class, 'storeLandingOrder'])->name('landing.order.store');
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/reservation', [App\Http\Controllers\Frontend\ReservationController::class, 'index'])->name('reservation.index');
    Route::get('/reservation/availability', [App\Http\Controllers\Frontend\ReservationController::class, 'availability'])->name('reservation.availability');
    Route::post('/reservation/store', [App\Http\Controllers\Frontend\ReservationController::class, 'store'])->name('reservation.store');
    Route::post('/reservation/status', [App\Http\Controllers\Frontend\ReservationController::class, 'checkStatus'])->name('reservation.status')->middleware('throttle:10,1');
    Route::get('/categories', [HomeController::class, 'allCategories'])->name('categories.all');
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    // Route::get('/blog/{slug}', [BlogController::class, 'blogDetails'])->name('blog.details');

    Route::get('/contact', [ContctController::class, 'index'])->name('contact.index');
    Route::post('/contact/send', [ContctController::class, 'send'])->name('contact.send');

    Route::get('/faq', [SupportController::class, 'index'])->name('faq.index');


    Route::get('/brands', [BrandController::class, 'index'])->name('brand.index');
    // Route::get('/brand/{slug}', [ProductController::class, 'brandProducts'])->name('brand.products');

    Route::get('/register', [AuthController::class, 'register'])->name('user.register');
    Route::post('/register', [AuthController::class, 'storeRegister'])->name('user.register.store');
    Route::get('/login', [AuthController::class, 'login'])->name('user.login');
    Route::post('/login', [AuthController::class, 'storeLogin'])->name('user.login.store');
    Route::get('/forgot-password', [CustomerPasswordResetController::class, 'showForgotPasswordForm'])->name('password.forgot');
    Route::post('/password/otp/request', [CustomerPasswordResetController::class, 'requestOtp'])->name('password.otp.request');
    Route::post('/password/otp/verify', [CustomerPasswordResetController::class, 'verifyOtp'])->name('password.otp.verify');
    Route::middleware(['auth:customer'])->group(function () {
        Route::get('/profile', [AuthController::class, 'profile'])->name('user.profile');
        Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('user.profile.update');
        Route::post('/password/update', [AuthController::class, 'updatePassword'])->name('user.password.update');
        Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('user.dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('user.logout');
        Route::get('/order/details/{id}', [OrderController::class, 'orderDetails'])->name('user.order.details');
        Route::post('/order/return/{id}', [OrderController::class, 'requestReturn'])->name('order.return');
        Route::post('/order/review/store', [OrderController::class, 'storeReview'])->name('user.review.store');
    // My Reservations page
        Route::get('/dashboard/reservations', [App\Http\Controllers\Frontend\ReservationDashboardController::class, 'myReservations'])->name('dashboard.reservations');
    });
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    // Route::get('/category/{slug}', [ProductController::class, 'categoryProducts'])->name('category.products');
    // Route::get('/product/{slug}', [ProductController::class, 'productDetails'])->name('product.details');
    Route::get('/shop', [ProductController::class, 'index'])->name('shop.index');
    Route::get('/menu/filter', [ProductController::class, 'filterMenu'])->name('menu.filter');
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
    Route::get('/thank-you/{id}', [OrderController::class, 'thankyou'])->name('order.thankyou');
    Route::get('/invoice/download/{id}', [OrderController::class, 'invoice'])->name('invoice.download');
    Route::get('/product-track', [OrderController::class, 'trackOrder'])->name('order.track');
    Route::get('/order/reviews/{id}', [OrderController::class, 'getReviews'])->name('order.reviews');
    // Route::get('/page/{slug}', [AboutController::class, 'showPage'])->name('frontend.page');
    Route::post('/order/payment/submit', [OrderController::class, 'submitPayment'])->name('order.payment.submit');
    Route::post('/newsletter-subscribe', [HomeController::class, 'subscribe'])->name('newsletter.subscribe');

    // Route::get('/category/{slug}', [ProductController::class, 'categoryProducts'])->name('category.products');
    // Route::get('/subcategory/{mega_slug}/{sub_slug}', [ProductController::class, 'subcategoryProducts'])->name('subcategory.products');
    // Route::get('/minicategory/{mega_slug}/{sub_slug}/{mini_slug}', [ProductController::class, 'minicategoryProducts'])->name('minicategory.products');
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
    Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots.txt');
    Route::get('/llms.txt', [SitemapController::class, 'llms'])->name('llms.txt');
    Route::get('/feeds/google/products.xml', [SitemapController::class, 'googleXml'])->name('google.xml');
    Route::get('/feeds/facebook/products.csv', [SitemapController::class, 'facebookCatalogCsv'])->name('facebook.catalog.csv');
    Route::get('/feeds/tiktok/products.csv', [SitemapController::class, 'tiktokCatalogCsv'])->name('tiktok.catalog.csv');

    Route::get('/sale/{slug}', [App\Http\Controllers\Frontend\LandingController::class, 'index'])->name('landing');

    // Dynamic Route Resolver for root-level slugs
    Route::get('/{slug}', [App\Http\Controllers\Frontend\DynamicRouteController::class, 'resolve'])->name('dynamic.slug');
    Route::get('/{slug}', [App\Http\Controllers\Frontend\DynamicRouteController::class, 'resolve'])->name('product.details');
    Route::get('/{slug}', [App\Http\Controllers\Frontend\DynamicRouteController::class, 'resolve'])->name('category.products');
    Route::get('/{slug}', [App\Http\Controllers\Frontend\DynamicRouteController::class, 'resolve'])->name('subcategory.products');
    Route::get('/{slug}', [App\Http\Controllers\Frontend\DynamicRouteController::class, 'resolve'])->name('minicategory.products');
    Route::get('/{slug}', [App\Http\Controllers\Frontend\DynamicRouteController::class, 'resolve'])->name('frontend.page');
    Route::get('/{slug}', [App\Http\Controllers\Frontend\DynamicRouteController::class, 'resolve'])->name('blog.details');
    Route::get('/{slug}', [App\Http\Controllers\Frontend\DynamicRouteController::class, 'resolve'])->name('brand.products');

    // Route::get('/kiron', [SaasIndexController::class, 'home'])->name('kiron.index');
});
