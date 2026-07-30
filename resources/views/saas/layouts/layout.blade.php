<!DOCTYPE html>
<html lang="en">

<head>


    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    @yield('meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        media="print" onload="this.media='all'">
    <!-- Local CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('components.meta-info.pixel', ['setup' => $setup])
    @stack('styles')
    <style>
        html {
            scroll-behavior: auto !important;
        }

        html.lenis,
        html.lenis body {
            height: auto;
        }

        body {
            max-width: 100%;
        }

        .scroll-container {
            width: 100%;
            overflow-x: hidden;
        }

        .move-up {
            display: block;
            /* টাইটেল সেন্টারে রাখার জন্য এটি জরুরি */
            will-change: transform;
            /* ট্রানজিশন লিনিয়ার রাখলে স্ক্রলের সাথে স্মুথলি ম্যাচ করবে */
            transition: transform 0.1s linear;
            transform: translate3d(0, 0, 0);
            /* শুরুতে একদম অরিজিনাল জায়গায় থাকবে */
        }

        .scroll-wrapper {
            overflow: hidden;
            width: 100%;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    @include('saas.partials.header')

    <!-- Page Content Area -->
    <main class="bg-[#f2f4f8]  ">
        @yield('content')
    </main>

    <!-- FOOTER -->
    @include('saas.partials.footer')



    @stack('scripts')
    <script>
        const menuToggle = document.getElementById("menu-toggle");
        const menuClose = document.getElementById("menu-close");
        const mobileMenu = document.getElementById("mobile-menu");
        const menuOverlay = document.getElementById("menu-overlay");

        function toggleMenu() {
            mobileMenu.classList.toggle("translate-x-full");
        }

        menuToggle.addEventListener("click", toggleMenu);
        menuClose.addEventListener("click", toggleMenu);
        menuOverlay.addEventListener("click", toggleMenu);

        function initLenisAnimation() {
            if (window.lenis) {
                window.lenis.on('scroll', () => {
                    const vh = window.innerHeight;

                    document.querySelectorAll('.move-up').forEach(el => {
                        const rect = el.getBoundingClientRect();

                        // এলিমেন্টটি যখন স্ক্রিনে দেখা যাবে শুধু তখনই মুভ করবে
                        if (rect.top < vh && rect.bottom > 0) {
                            const speed = parseFloat(el.dataset.speed) || 0.05; // স্পিড কমিয়ে ০.০৫ রাখা হয়েছে

                            // এলিমেন্টটি যখন স্ক্রিনের মাঝামাঝি থাকবে তখন এর পজিশন হবে ০ (ডিজাইন একদম ঠিক থাকবে)
                            // মাঝামাঝি থেকে উপরে বা নিচে গেলে এটি সামান্য মুভ করবে
                            const elementCenter = rect.top + (rect.height / 2);
                            const viewportCenter = vh / 2;
                            const movement = (elementCenter - viewportCenter) * speed;

                            el.style.transform = `translate3d(0, ${movement}px, 0)`;
                        }
                    });
                });
            } else {
                setTimeout(initLenisAnimation, 100);
            }
        }
        initLenisAnimation();
    </script>

</body>

</html>
