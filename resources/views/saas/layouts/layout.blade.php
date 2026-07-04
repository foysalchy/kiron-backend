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


    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        media="print" onload="this.media='all'">
    <!-- Local CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    <style>
        html,
        body {
            max-width: 100%;
            overflow-x: hidden;
        }

        .scroll-container {
            width: 100%;
            overflow-x: hidden;
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
    </script>

</body>

</html>
