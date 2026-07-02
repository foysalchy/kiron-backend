<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Explore our shop for the best products. Fast delivery and quality guaranteed.">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <title>{{ $setup->shop_name ?? 'Bhaiya Digital' }}</title>
<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
   <link rel="icon" type="image/x-icon" href="{{ $setup->favicon_url ?? asset('default-favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <!-- Outfit Font -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        media="print" onload="this.media='all' rel="stylesheet" />
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        media="print" onload="this.media='all'">
    <!-- Local CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    <style>
    /* এটি আপনার পুরো সাইটের যেকোনো সাইড স্ক্রল চিরতরে বন্ধ করে দিবে */
    html, body {
        max-width: 100%;
        overflow-x: hidden;
    }

    /* মারকিউ কন্টেইনারকে ফিক্স করার জন্য */
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
