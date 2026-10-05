<!DOCTYPE html>
<html lang="en">

<head>
     @yield('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
 
 

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ $setup->favicon_url ?? ''}}">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        media="print" onload="this.media='all'">
    <!-- Local CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
   
    @include('components.meta-info.pixel', ['setup' => $setup])
    <style>
        :root {
            --primary-color: {{ $themeColor->theme_template['primary_color'] ?? '#BD4F00' }};

            --primary-text: {{ $themeColor->theme_template['primary_text_color'] ?? '#ffffff' }};

            --primary-hover-text: {{ $themeColor->theme_template['primary_hover_text'] ?? '#a34400' }};
            --primary-hover-color: {{ $themeColor->theme_template['primary_hover_color'] ?? '#a34400' }};

            --secondary-color: {{ str_replace('##', '#', $themeColor->theme_template['secondary_color'] ?? '#FFA500') }};
            --secondary-text: {{ trim($themeColor->theme_template['secondary_text_color'] ?? '#000000') }};

            --header-bg: {{ $themeColor->theme_template['header_color'] ?? ($themeColor->theme_template['primary_color'] ?? '#66267b') }};
            --header-text: {{ $themeColor->theme_template['header_text_color'] ?? '#ffffff' }};

            --footer-bg: {{ $themeColor->theme_template['footer_color'] ?? '#0a061e' }};
            --footer-text: {{ $themeColor->theme_template['footer_text_color'] ?? '#ffffff' }};
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
    </style>
    @stack('styles')
    @if(isset($footerCodes))
        @foreach($footerCodes as $footerCode)
            {!! $footerCode->code !!}
        @endforeach
    @endif
</head>

<body class="font-manrope">
    <!-- HEADER -->
    @include('template4.partials.header')

    <!-- Page Content Area -->
    <main class="bg-[#fcfcfc]  ">
        @yield('content')
    </main>

    <!-- FOOTER -->
    @include('template4.partials.footer')
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
        // Mobile Menu Logic
        const menuToggle = document.getElementById("menu-toggle");
        const closeSidebar = document.getElementById("close-sidebar");
        const sidebar = document.getElementById("mobile-sidebar");
        const overlay = document.getElementById("overlay");

        function toggleSidebar() {
            sidebar.classList.toggle("-translate-x-full");
            overlay.classList.toggle("hidden");
        }

        menuToggle.addEventListener("click", toggleSidebar);
        closeSidebar.addEventListener("click", toggleSidebar);
        overlay.addEventListener("click", toggleSidebar);

        // Accordion Logic for Mobile
        document.querySelectorAll(".accordion-btn").forEach((btn) => {
            btn.addEventListener("click", function() {
                const target = document.getElementById(this.dataset.target);
                const icon = this.querySelector("i");
                target.classList.toggle("hidden");
                icon.classList.toggle("fa-plus");
                icon.classList.toggle("fa-minus");
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
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
