<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Explore our shop for the best products. Fast delivery and quality guaranteed.">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">

    <title>{{ $setup->shop_name ?? 'Bhaiya Digital' }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ $setup->favicon_url }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <!-- Manrope Font (Lumina design system) -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap"
        media="print" onload="this.media='all'" rel="stylesheet" />
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        media="print" onload="this.media='all'">
    <!-- Local CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('meta')
    @include('components.meta-info.pixel', ['setup' => $setup])

    <style>
        :root {
            /* Lumina Cuisine palette used as the fallback default; still overridable per-shop via $themeColor */
            --primary-color: {{ $themeColor->theme_template['primary_color'] ?? '#D6431F' }};

            --primary-text: {{ $themeColor->theme_template['primary_text_color'] ?? '#ffffff' }};

            --primary-hover-text: {{ $themeColor->theme_template['primary_hover_text'] ?? '#ffffff' }};
            --primary-hover-color: {{ $themeColor->theme_template['primary_hover_color'] ?? '#B8371A' }};

            --secondary-color: {{ str_replace('##', '#', $themeColor->theme_template['secondary_color'] ?? '#C99A45') }};
            --secondary-text: {{ trim($themeColor->theme_template['secondary_text_color'] ?? '#18130F') }};

            --header-bg: {{ $themeColor->theme_template['header_color'] ?? '#ffffff' }};
            --header-text: {{ $themeColor->theme_template['header_text_color'] ?? '#18130F' }};

            --footer-bg: {{ $themeColor->theme_template['footer_color'] ?? '#ffffff' }};
            --footer-text: {{ $themeColor->theme_template['footer_text_color'] ?? '#18130F' }};

            /* Extra Lumina design tokens, not part of the original theme system,
               kept as static vars so partials/pages can reference them directly */
            --lumina-coal: #18130F;
            --lumina-ash: #F6F1E6;
            --lumina-ember: #D6431F;
            --lumina-ember-600: #B8371A;
            --lumina-smoke: #8B8175;
            --lumina-gold: #C99A45;
        }

        body {
            font-family: 'Manrope', sans-serif;
            background: #F6F1E6;
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

        /* ---- Lumina signature motifs (used by header/footer/content) ---- */
        html { scroll-behavior: smooth; }

        .grain { position: relative; }

        .sear {
            height: 6px;
            background: repeating-linear-gradient(115deg, var(--lumina-ember) 0 10px, transparent 10px 22px);
            opacity: .55;
        }
        .sear-corner { position: relative; overflow: hidden; }
        .sear-corner::after {
            content: "";
            position: absolute; top: -20px; right: -20px; width: 70px; height: 70px;
            background: repeating-linear-gradient(115deg, rgba(214,67,31,.35) 0 6px, transparent 6px 14px);
            border-radius: 999px;
            opacity: 0;
            transition: opacity .35s ease;
        }
        .group:hover .sear-corner::after { opacity: 1; }

        .wisp {
            position: absolute;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to top, rgba(246,241,230,.35), rgba(246,241,230,0));
            filter: blur(3px);
            border-radius: 999px;
            animation: rise linear infinite;
        }
        @keyframes rise {
            0%   { transform: translateY(0) translateX(0) scaleY(1); opacity: 0; }
            15%  { opacity: .5; }
            85%  { opacity: .25; }
            100% { transform: translateY(-340px) translateX(24px) scaleY(1.6); opacity: 0; }
        }

        .reveal { opacity: 0; transform: translateY(18px); transition: opacity .7s cubic-bezier(.16,1,.3,1), transform .7s cubic-bezier(.16,1,.3,1); }
        .reveal.in { opacity: 1; transform: translateY(0); }

        ::selection { background: var(--lumina-ember); color: var(--lumina-ash); }

        .tick { box-shadow: 0 1px 0 0 rgba(24,19,15,.08); }

        @media (prefers-reduced-motion: reduce) {
            .wisp { animation: none; display: none; }
            .reveal { transition: none; opacity: 1; transform: none; }
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- HEADER -->
    @include('template5.partials.header')

    <!-- Page Content Area -->
    <main class="bg-[#f2f4f8]">
        @yield('content')
    </main>

    <!-- FOOTER -->
    @include('template5.partials.footer')

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
        document.addEventListener("DOMContentLoaded", function () {
  const revealEls = document.querySelectorAll('.reveal');

  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('in');   // <-- must match CSS: .reveal.in
      }
    });
  }, { threshold: 0.1 });

  revealEls.forEach(el => revealObserver.observe(el));
});
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
        });
    </script>


</body>

</html>