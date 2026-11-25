<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">


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

    @include('components.fontawesome')
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
        .theme-global-bg {
            background: #EEF5F3;
            color: #0A1F22;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(70px);
            opacity: .55;
            animation: drift 20s ease-in-out infinite alternate;
            pointer-events: none;
        }

        @keyframes drift {
            0% {
                transform: translate(0, 0) scale(1);
            }
            50% {
                transform: translate(60px, -40px) scale(1.12);
            }
            100% {
                transform: translate(-50px, 50px) scale(.92);
            }
        }

        .glass {
            background: rgba(255, 255, 255, 0.55);
            -webkit-backdrop-filter: blur(18px) saturate(170%);
            backdrop-filter: blur(18px) saturate(170%);
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.9) inset, 0 18px 40px -24px rgba(0, 58, 63, 0.35);
        }

        .shine {
            position: relative;
            overflow: hidden;
        }

        .shine::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(115deg, transparent 30%, rgba(255, 255, 255, 0.7) 50%, transparent 70%);
            transform: translateX(-130%);
            transition: transform 1.8s ease;
            pointer-events: none;
        }

        .shine:hover::after {
            transform: translateX(130%);
        }

        .hero-reveal {
            opacity: 0;
            transform: translateY(48px);
            transition: opacity 1.1s cubic-bezier(0.22, 1, 0.36, 1), transform 1.1s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: opacity, transform;
        }

        .hero-reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body class="theme-global-bg">
    <!-- Global Scroll Progress Indicator (Exact EduManager Style) -->
    <div id="globalScrollProgress" class="fixed top-0 inset-x-0 h-[3.5px] origin-left z-[9999] bg-gradient-to-r from-[#00555C] via-[#00A08A] to-[#7CE0C3] pointer-events-none transition-transform duration-75" aria-hidden="true" style="transform: scaleX(0);"></div>

    <!-- Ambient Blurry Color Orbs from Original Design -->
    <div aria-hidden="true" style="position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none">
        <span class="orb" style="width: 600px; height: 600px; left: -160px; top: 10%; background: #B8F0DE; animation-duration: 24s"></span>
        <span class="orb" style="width: 520px; height: 520px; right: -140px; top: 40%; background: #A8DCF5; animation-duration: 28s; animation-delay: -8s"></span>
        <span class="orb" style="width: 440px; height: 440px; left: 35%; bottom: -160px; background: #7CE0C3; opacity: .35; animation-duration: 32s; animation-delay: -16s"></span>
    </div>

    <!-- HEADER -->
    @include('saas.partials.header')

    <!-- Page Content Area -->
    <main class="relative z-10">
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

        // Top Scroll Progress Bar (EduManager Signature Feature)
        window.addEventListener('scroll', function () {
            const scrollBar = document.getElementById('globalScrollProgress');
            if (!scrollBar) return;
            const scrollTop = window.scrollY || document.documentElement.scrollTop;
            const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = docHeight > 0 ? (scrollTop / docHeight) : 0;
            scrollBar.style.transform = `scaleX(${scrolled})`;
        }, { passive: true });

        document.addEventListener('DOMContentLoaded', () => {
            const revealItems = document.querySelectorAll('.hero-reveal');
            if (revealItems.length) {
                if ('IntersectionObserver' in window) {
                    const revealObserver = new IntersectionObserver((entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('is-visible');
                            } else {
                                entry.target.classList.remove('is-visible');
                            }
                        });
                    }, { threshold: 0.08 });
                    revealItems.forEach((el) => revealObserver.observe(el));
                } else {
                    revealItems.forEach((el) => el.classList.add('is-visible'));
                }
            }

            window.lenisReady.then((lenis) => {
                lenis.on('scroll', () => {
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
            });
        });
    </script>

</body>

</html>
