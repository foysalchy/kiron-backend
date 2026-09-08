<!DOCTYPE html>
<html lang="en">

   @yield('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ $setup->favicon_url }}">

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

<body class="font-['Poppins',_sans-serif]">
    <!-- HEADER -->
    @include('template1.partials.header')

    <!-- Page Content Area -->
    <main class="bg-[#f2f4f8]  ">
        @yield('content')
    </main>

    <!-- FOOTER -->
    @include('template1.partials.footer')
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
