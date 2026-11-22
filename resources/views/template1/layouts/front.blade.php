<!DOCTYPE html>
<html lang="en">

@yield('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">


<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="{{ $setup->favicon_url }}">

@include('components.fontawesome')
<!-- Local CSS -->
@vite(['resources/css/app.css', 'resources/js/app.js'])

@include('components.meta-info.pixel', ['setup' => $setup])
<style>
    :root {
        --primary-color:
            {{ $themeColor->theme_template['primary_color'] ?? '#BD4F00' }}
        ;

        --primary-text:
            {{ $themeColor->theme_template['primary_text_color'] ?? '#ffffff' }}
        ;

        --primary-hover-text:
            {{ $themeColor->theme_template['primary_hover_text'] ?? '#a34400' }}
        ;
        --primary-hover-color:
            {{ $themeColor->theme_template['primary_hover_color'] ?? '#a34400' }}
        ;

        --secondary-color:
            {{ str_replace('##', '#', $themeColor->theme_template['secondary_color'] ?? '#FFA500') }}
        ;
        --secondary-text:
            {{ trim($themeColor->theme_template['secondary_text_color'] ?? '#000000') }}
        ;

        --header-bg:
            {{ $themeColor->theme_template['header_color'] ?? ($themeColor->theme_template['primary_color'] ?? '#66267b') }}
        ;
        --header-text:
            {{ $themeColor->theme_template['header_text_color'] ?? '#ffffff' }}
        ;

        --footer-bg:
            {{ $themeColor->theme_template['footer_color'] ?? '#0a061e' }}
        ;
        --footer-text:
            {{ $themeColor->theme_template['footer_text_color'] ?? '#ffffff' }}
        ;
    }

    .header-custom-bg {
        background-color: var(--header-bg) !important;
        color: var(--header-text) !important;
    }

    .footer-custom-bg {
        background-color: var(--footer-bg) !important;
        color: var(--footer-text) !important;
    }

    .text-header {
        color: var(--header-text) !important;
    }

    .text-footer {
        color: var(--footer-text) !important;
    }

    .primary-bg {
        background-color: var(--primary-color) !important;
        color: var(--primary-text) !important;
    }

    .text-primary {
        color: var(--primary-text) !important;
    }

    .text-brand {
        color: var(--primary-color) !important;
    }

    .secondary-bg {
        background-color: var(--secondary-color) !important;
        color: var(--secondary-text) !important;
    }

    .text-secondary {
        color: var(--secondary-text) !important;
    }

    .hover-text:hover {
        color: var(--primary-hover-text) !important;
    }

    .primary-bg-hover:hover {
        background-color: var(--primary-hover-color) !important;
    }

    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .prose h1 {
        margin-top: 0;
        margin-bottom: 5px;
        font-size: 22px !important;
        line-height: 28px;
        font-weight: 400;
    }

    .prose h2 {
        margin-top: 20px;
        margin-bottom: 0;
        font-size: 22px !important;
        line-height: 28px;
        font-weight: 400;
    }

    .prose h3 {
        margin-top: 0;
        margin-bottom: 5px;
        font-size: 16px !important;
        line-height: 24px;
        font-weight: 600;
    }

    .prose h4 {
        margin-top: 0;
        margin-bottom: 5px;
        font-size: 15px;
        line-height: 20px;
        font-weight: 600;
    }

    .prose hr {
        margin: 10px 0px !important;
    }

    .prose h5 {
        margin-top: 0;
        margin-bottom: 5px;
        font-size: 14px;
        line-height: 1.4;
        font-weight: 600;
    }

    .prose h6 {
        margin-top: 0;
        margin-bottom: 5px;
        font-size: 13px;
        line-height: 1.4;
        font-weight: 600;
    }

    .prose p {
        margin-top: 0 !important;
        margin-bottom: 8px;
        font-size: 16px;
        line-height: 1.6;
    }

    .prose h1+p,
    .prose h2+p,
    .prose h3+p,
    .prose h4+p,
    .prose h5+p,
    .prose h6+p {
        margin-top: 0;
    }

    @keyframes cartShake {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-10deg); }
        50% { transform: rotate(10deg); }
        75% { transform: rotate(-10deg); }
    }
    
    .animate-cart-shake {
        animation: cartShake 0.4s ease-in-out;
    }
</style>
@stack('styles')
@if(isset($footerCodes))
    @foreach($footerCodes as $footerCode)
        {!! $footerCode->code !!}
    @endforeach
@endif
</head>

<body class="font-storefront">
    <!-- HEADER -->
    @include('template1.partials.header')

    <!-- Page Content Area -->
    <main class="bg-[#f2f4f8]  ">
        @yield('content')
    </main>

    <!-- FOOTER -->
    @include('template1.partials.footer')

    <!-- Floating Cart Button -->
    @if (!request()->routeIs('cart.index', 'checkout.index'))
    <button id="floating-cart-btn" onclick="toggleCartDrawer()" class="fixed z-[90] right-0 bottom-24 primary-bg text-primary shadow-2xl p-3 flex flex-col items-center justify-center gap-1 rounded-l-lg hover:bg-opacity-90 transition-all border border-r-0 border-white/20 group cursor-pointer outline-none">
        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-7 sm:w-7 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span class="cart-count-nav absolute -top-2 -right-2 bg-white text-[var(--primary-color)] text-[10px] font-bold h-4 w-4 sm:h-5 sm:w-5 flex items-center justify-center rounded-full">
                {{ \Gloudemans\Shoppingcart\Facades\Cart::count() }}
            </span>
        </div>
        <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider mt-1">Cart</span>
        <div class="bg-white/20 px-2 py-0.5 rounded text-[10px] sm:text-xs font-bold w-full text-center mt-1">
            <span id="floating-cart-subtotal">{{ \Gloudemans\Shoppingcart\Facades\Cart::subtotal() }}</span>{{ $setup->currency ?? '৳' }}
        </div>
    </button>
    @endif

    <!-- Cart Drawer Overlay -->
    <div id="cart-overlay" onclick="toggleCartDrawer()"
        class="fixed inset-0 bg-black/50 z-[1100] hidden transition-opacity duration-300"></div>

    <!-- Cart Drawer Panel -->
    <div id="cart-drawer"
        class="fixed top-0 right-0 h-full w-[350px] max-w-[95vw] sm:max-w-[90vw] bg-white z-[1200] shadow-2xl transform translate-x-full transition-transform duration-300 flex flex-col">

        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b">
            <div class="text-lg font-bold text-gray-800">Shopping Cart</div>
            <button onclick="toggleCartDrawer()" aria-label="Close cart drawer"
                class="text-gray-500 hover:text-red-500 text-2xl border-none bg-transparent cursor-pointer">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Products List -->
        <div class="flex-1 overflow-y-auto p-4" id="mini-cart-list">
            <!-- Component called -->
            <x-template1.cart-drawer-items />
        </div>

        <!-- Footer -->
        <div class="p-4 border-t bg-gray-50" id="mini-cart-footer"
            style="{{ \Gloudemans\Shoppingcart\Facades\Cart::count() > 0 ? '' : 'display:none;' }}">
            <div class="flex justify-between items-center mb-4">
                <span class="text-lg font-bold text-gray-700">SUBTOTAL:</span>
                <span class="text-lg font-bold text-gray-900" id="mini-cart-subtotal-val"
                    data-subtotal-raw="{{ \Gloudemans\Shoppingcart\Facades\Cart::subtotal(0, '', '') }}">
                    {{ \Gloudemans\Shoppingcart\Facades\Cart::subtotal() }}{{ $setup->currency ?? '৳' }}
                </span>
            </div>

            <div class="space-y-3">
                <a href="{{ route('cart.index') }}"
                    class="block w-full text-center primary-bg text-primary py-3 rounded font-bold uppercase hover:bg-opacity-90 transition-colors">
                    VIEW CART
                </a>
                <a href="{{ route('checkout.index') }}"
                    class="block w-full text-center bg-black text-primary py-3 rounded font-bold uppercase hover:bg-opacity-90 transition-colors">
                    CHECKOUT
                </a>
            </div>
        </div>
    </div>

    <!-- Global Variation Modal  -->
    <div id="variation-modal"
        class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 relative">
            <button onclick="closeModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl border-none bg-transparent cursor-pointer">&times;</button>


            <div id="modal-content-area"></div>
        </div>
    </div>


    <!-- Scroll To Top Button -->
    <x-scroll-to-top bottom="bottom-52 md:bottom-8" />

    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr.error("{{ $error }}");
                @endforeach
            @endif

            @if (Session::has('success'))
                toastr.success("{{ Session::get('success') }}");
            @endif

            @if (Session::has('error'))
                toastr.error("{{ Session::get('error') }}");
            @endif

            @if (Session::has('warning'))
                toastr.warning("{{ Session::get('warning') }}");
            @endif

            // Cart Update Animation and Sound
            function playCartSound() {
                try {
                    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioCtx.createOscillator();
                    const gainNode = audioCtx.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioCtx.destination);
                    
                    // A pleasant short "pop/bell" sound
                    oscillator.type = 'sine';
                    oscillator.frequency.setValueAtTime(800, audioCtx.currentTime);
                    oscillator.frequency.exponentialRampToValueAtTime(300, audioCtx.currentTime + 0.1);
                    
                    gainNode.gain.setValueAtTime(1, audioCtx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.1);
                    
                    oscillator.start();
                    oscillator.stop(audioCtx.currentTime + 0.1);
                } catch (e) {
                    console.log("Audio not supported");
                }
            }

            // Observe the cart count for changes to trigger price update (refreshMiniCart)
            const cartCountEls = document.querySelectorAll('.cart-count-nav');
            if (cartCountEls.length > 0) {
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'characterData' || mutation.type === 'childList') {
                            if (typeof refreshMiniCart === 'function') {
                                refreshMiniCart();
                            }
                        }
                    });
                });
                
                observer.observe(cartCountEls[0], { characterData: true, childList: true, subtree: true });
            }

            // Immediately trigger shake and sound on Add to Cart button clicks
            document.addEventListener('click', function(e) {
                const target = e.target.closest('button') || e.target.closest('a');
                if (target) {
                    const onclickAttr = target.getAttribute('onclick') || '';
                    const idAttr = target.getAttribute('id') || '';
                    
                    if (
                        onclickAttr.includes('addSingleToCart') || 
                        onclickAttr.includes('processAddVariation') || 
                        onclickAttr.includes('handleAddToCart') || 
                        idAttr === 'btn-cart' || 
                        idAttr === 'btn-order'
                    ) {
                        const btn = document.getElementById('floating-cart-btn');
                        if (btn) {
                            btn.classList.remove('animate-cart-shake');
                            void btn.offsetWidth; // trigger reflow
                            btn.classList.add('animate-cart-shake');
                        }
                        
                        playCartSound();
                    }
                }
            });
        });
    </script>
</body>

</html>