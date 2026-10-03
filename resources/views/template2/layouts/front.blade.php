<!DOCTYPE html>
<html lang="en">

<head>
    @yield('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">



    <!-- Favicon -->
    <link rel="icon" type="image/x-icon"
        href="{{ $setup->favicon_url ?? asset('images/template1/frontend/sell.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <!-- Outfit Font -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        media="print" onload="this.media='all'" rel="stylesheet" />
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        media="print" onload="this.media='all'">
    <!-- Local CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('components.meta-info.pixel', ['setup' => $setup])
    <style>
        :root {
            --primary-color:
                {{ $themeColor->theme_template['primary_color'] ?? '#016738' }}
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

<body class="font-['Poppins',_sans-serif]">
    <!-- HEADER -->
    @include('template2.partials.header')

    <!-- Page Content Area -->
    <main class="bg-[#fcfcfc]">
        @yield('content')
    </main>

    <!-- FOOTER -->
    @include('template2.partials.footer')

    <!-- Floating Cart Button -->
    <button id="floating-cart-btn" onclick="toggleCartDrawer()" class="fixed z-[90] right-0 bottom-24 primary-bg shadow-2xl p-3 flex flex-col items-center justify-center gap-1 rounded-l-lg hover:bg-opacity-90 transition-all border border-r-0 border-white/20 group cursor-pointer outline-none">
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
            <span id="floating-cart-subtotal">{{ \Gloudemans\Shoppingcart\Facades\Cart::subtotal() }}</span>৳
        </div>
    </button>

    <!-- Global Variation Modal  -->
    <div id="variation-modal"
        class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 relative">
            <button onclick="closeModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl border-none bg-transparent cursor-pointer">&times;</button>


            <div id="modal-content-area"></div>
        </div>
    </div>


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

            // Observe the floating cart subtotal for changes
            const subtotalEl = document.getElementById('floating-cart-subtotal');
            if (subtotalEl) {
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'characterData' || mutation.type === 'childList') {
                            const btn = document.getElementById('floating-cart-btn');
                            if (btn) {
                                // Add shake class
                                btn.classList.remove('animate-cart-shake');
                                void btn.offsetWidth; // trigger reflow
                                btn.classList.add('animate-cart-shake');
                                
                                // Play sound
                                playCartSound();
                            }
                        }
                    });
                });
                
                observer.observe(subtotalEl, { characterData: true, childList: true, subtree: true });
            }
        });
    </script>
</body>

</html>