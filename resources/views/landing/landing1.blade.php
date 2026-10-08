<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $landing->title }}</title>
    <link rel="icon" type="image/x-icon" href="{{ $setup->favicon_url }}">
    @include('components.fontawesome')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            overflow-x: hidden;
            font-family: {!! $setup->lang === 'bn' ? "'Hind Siliguri', sans-serif" : "'Manrope', sans-serif" !!};
        }

        /* Fix: prevent horizontal scroll on mobile */
        .rv-track-wrap {
            overflow: hidden;
        }
    </style>
    <style>
        .rv-swiper .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background: #d1d5db;
            /* Gray color */
            opacity: 1;
            transition: all 0.3s ease;
            border-radius: 50%;
        }

        .rv-swiper .swiper-pagination-bullet-active {
            background: #0D2601 !important;
            width: 30px;
            border-radius: 10px;
        }

        .swiper-button-next,
        .swiper-button-prev {
            z-index: 50;
        }

        .swiper-slide {
            transition: transform 0.3s ease;
        }

        .swiper-slide:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<body class="text-gray-800 bg-gray-100 overflow-x-hidden">

    <main>
    {{-- ══ SECTION 1: HERO ══ --}}
    <section class="bg-[#0D2601] text-white pt-10 pb-16 px-4 text-center">
        <div class="max-w-7xl mx-auto">

            <img src={{ $setup->logo_url }} alt="Moringa Logo" width="80" height="80"
                class="mx-auto mb-6 w-20">

            <div class="border border-[#2e8c03] p-4 md:p-6 rounded mb-6">
                <h1 class="text-xl sm:text-2xl md:text-4xl lg:text-5xl font-semibold leading-snug">
                    {!! $landing->short_description !!}
                </h1>
            </div>

            {{-- <p class="text-base sm:text-lg md:text-2xl text-green-50 mb-6">
                525 grams of premium sajina powder + 100 grams of black cumin honey free.
            </p> --}}

            <a href="#order"
                class="inline-flex items-center gap-2 bg-[#f5a623] mb-6 text-[#0D2601] px-6 md:px-10 py-3 md:py-4 border-2 border-[#ad7419] rounded-xl font-bold text-lg sm:text-xl md:text-3xl shadow-lg hover:scale-105 transition-transform">
                Click to order.
                <img src="https://landing-page-images-1.s3.ap-south-1.amazonaws.com/landing-34/hand.png"
                    width="32" height="32" class="w-8 md:w-12" alt="">
            </a>

            {{-- Video --}}
            <div class="w-full max-w-6xl mx-auto mt-6">
                <div
                    class="relative border-[8px] sm:border-[12px] md:border-[18px] border-[#35B11E] rounded-xl overflow-hidden bg-black shadow-2xl">
                    <div class="relative aspect-video rounded-2xl overflow-hidden shadow-lg bg-black">

                        @if ($landing->video)
                            <video class="w-full h-full object-cover" controls playsinline
                                poster="{{ $landing->thumbnail_url }}">
                                <source src="{{ $landing->video_url }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        @else
                            <img src="{{ $landing->thumbnail_url ?? asset('./images/default-thumbnail.jpg') }}"
                                class="w-full h-full object-cover" alt="{{ $landing->name }}">

                            <div
                                class="absolute inset-0 pointer-events-none flex flex-col justify-between p-3 md:p-5 bg-gradient-to-t from-black/70 via-transparent to-black/30">
                                <div></div>
                                <div class="flex justify-end">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- ══ SECTION 2: PROMO PRICE ══ --}}
    <section class="py-16 md:py-24 px-4">
        <div class="max-w-7xl mx-auto relative">
            <!-- Main Green Container -->
            <div class="bg-[#2e8c03] p-8 md:p-24 rounded-2xl shadow-2xl text-center">

                <!-- Red Header Banner -->
                <div
                    class="bg-[#C00000] text-white py-6 text-xl md:text-4xl font-medium rounded-xl mb-12 shadow-lg w-full leading-tight">
                    Limited Time Offer: Get {{ $landing->discount_percentage ?? '' }}% Off on
                    {{ $landing->title ?? '' }}
                </div>

                <!-- White Price Box -->
                <div class="bg-white rounded-xl overflow-hidden shadow-inner border border-white">
                    <!-- Regular Price -->
                    <div class="py-6 px-8">
                        <p class="text-[#2e8c03] line-through text-2xl md:text-4xl font-medium decoration-4">
                            regular price {{ $landing->regular_price ?? '' }} {{ $setup->currency }}
                        </p>
                    </div>

                    <!-- Dotted Divider (Matches the image) -->
                    <div class="border-t-3 border-dotted border-[#2e8c03] mx-6"></div>

                    <!-- Offer Price -->
                    <div class="py-10 px-4">
                        <p class="text-[#2e8c03] font-medium text-2xl md:text-4xl">
                            discount price {{ $landing->discount_price ?? '' }} {{ $setup->currency }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Overlapping 3D CTA Button -->
            <div class="absolute left-1/2 -translate-x-1/2 -bottom-10 w-full flex justify-center">
                <a href="#order"
                    class="inline-flex items-center gap-4 bg-[#f1a32a] text-[#0D2601] px-10 md:px-20 py-4 rounded-xl font-bold text-2xl md:text-4xl border-3 border-[#b87d21] whitespace-nowrap">
                    Order Now
                    <img src="https://landing-page-images-1.s3.ap-south-1.amazonaws.com/landing-34/hand.png"
                        width="48" height="48" class="w-12 md:w-16" alt="hand icon">
                </a>
            </div>
        </div>
    </section>


    {{-- ══ SECTION 3: BENEFITS ══ --}}
    <section class="bg-[#f1fcf1] py-16 px-4 mt-8 md:mt-10">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10">

            <div class="bg-white rounded-xl overflow-hidden">
                <div class="benefits-container text-base md:text-xl p-4 md:p-6">
                    <article class="prose prose-slate max-w-none">
                        {!! $landing->extras['content'] ?? '' !!}
                    </article>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden">
                <div class="benefits-container text-base md:text-xl p-4 md:p-6">
                    <article class="prose prose-slate max-w-none">
                        {!! $landing->extras['content2'] ?? '' !!}
                    </article>
                </div>

            </div>
        </div>

        <div class="text-center mt-12">
            <a href="#order"
                class="inline-flex items-center gap-3 bg-[#f1a32a] text-[#0D2601] px-6 sm:px-10 md:px-16 py-3 md:py-5 rounded-xl font-bold text-lg sm:text-2xl md:text-4xl border-2 border-[#b87d21]">
                Order Now
                <img src="https://landing-page-images-1.s3.ap-south-1.amazonaws.com/landing-34/hand.png"
                    width="32" height="32" class="w-8 md:w-14" alt="">
            </a>
        </div>
    </section>


    {{-- ══ SECTION 4: PRODUCT BANNER ══ --}}
    <section class="py-12 px-4">
        <div class="max-w-2xl mx-auto text-center">
            <article class="prose prose-slate max-w-none">
                {!! $landing->description ?? '' !!}
            </article>
        </div>
    </section>


    {{-- ══ SECTION 5: WHY TRUST US ══ --}}
    <section class="bg-[#1a3a1a] text-white py-16 px-4">
        <div class="max-w-7xl mx-auto text-center">
            <div class="space-y-10">
                @if (isset($landing->extras['features2']) && is_array($landing->extras['features2']))
                    @foreach ($landing->extras['features2'] as $feature)
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h3 class="text-2xl font-bold text-[#1a3a1a] mb-4">
                                {{ $feature['title'] ?? '' }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                                <article class="prose prose-slate max-w-none text-lg">
                                    {!! $feature['description'] ?? '' !!}
                                </article>

                                @if (!empty($feature['image']))
                                    <div class="rounded-lg overflow-hidden ">
                                        <img src="{{ is_array($feature['image']) ? ($feature['image']['previewUrl'] ?? '') : (str_starts_with($feature['image'], 'blob:') || str_starts_with($feature['image'], 'data:') ? $feature['image'] : \Illuminate\Support\Facades\Storage::disk('r2')->url($feature['image'])) }}"
                                            alt="{{ $feature['title'] }}"
                                            class="w-full h-auto object-cover transform hover:scale-105 transition-transform duration-500">

                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="mt-10">
                <a href="#order"
                    class="inline-flex items-center gap-2 bg-[#f5a623] text-[#0D2601] px-6 sm:px-10 md:px-14 py-3 rounded-xl font-semibold text-lg sm:text-2xl md:text-4xl border-2 border-[#b87d21]">
                    Click to order.
                    <img src="{{ asset('./images/landing/img/hand.png') }}" width="32" height="32"
                        class="w-8 md:w-14" alt="">
                </a>
            </div>
        </div>
    </section>


    {{-- ══ SECTION 6: 8-GRID BENEFITS ══ --}}
    <section class="py-14 px-4 bg-white">
        <div class="max-w-7xl mx-auto text-center">
            <div
                class="inline-block border-4 md:border-10 border-[#4caf50] px-4 py-4 rounded-lg text-base sm:text-lg md:text-2xl font-semibold mb-10 uppercase">
                {{ data_get($landing->extras, 'features_title', 'Why would you eat sajina leaf powder?') }}
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 text-sm sm:text-base md:text-xl">
                @php
                    $grid = $landing->extras['features'] ?? [
                        'It works against serious diseases like diabetes by controlling sugar levels.',
                        'Eating sajan leaves regularly increases the taste of the mouth.',
                        'Helps keep the liver and kidneys healthy.',
                        'High blood pressure will be under control.',
                        'The body does not show signs of age easily.',
                        'Increases immunity.',
                        'It will be very helpful for weight loss.',
                        'Relieves problems caused by fever, cough and cold.',
                    ];
                @endphp

                @foreach ($grid as $g)
                    <div
                        class="border-2 border-[#4caf50] p-3 md:p-4 rounded-lg flex items-center justify-center min-h-[90px] md:min-h-[120px] text-center text-[#1a3a1a] hover:bg-[#e8f5e9] transition-all duration-300">
                        {{ is_array($g) ? $g['content'] ?? '' : $g }}
                    </div>
                @endforeach
            </div>

            <div class="mt-10">
                <a href="#order"
                    class="inline-flex items-center gap-2 bg-[#f5a623] text-[#0D2601] px-6 sm:px-10 md:px-14 py-3 rounded-xl font-semibold text-lg sm:text-2xl md:text-4xl border-2 border-[#b87d21] hover:scale-105 transition-transform">
                    Click to order.
                    <img src="{{ asset('./images/landing/img/hand.png') }}" width="32" height="32"
                        class="w-8 md:w-14" alt="">
                </a>
            </div>
        </div>
    </section>

    {{-- ══ SECTION 7: PRICING ══ --}}
    <section class="py-14 px-4">
        <div
            class="max-w-7xl mx-auto border-4 md:border-6 border-dashed border-[#1f5e00] rounded-3xl p-6 md:p-14 text-center bg-white shadow-sm">
            <h2 class="text-2xl sm:text-3xl md:text-5xl font-semibold text-[#1f5e00] mb-2">
                {{ $landing->title ?? '' }} Price
            </h2>
            <p class="text-base md:text-2xl text-[#1f5e00] mb-8">Affordable Price for the Best Product</p>
            <div class="bg-[#C00000] text-white p-6 md:p-10 rounded-xl shadow-lg">
                <p class="text-base md:text-2xl font-medium mb-3">
                    Original Price: {{ $landing->regular_price ?? '' }} {{ $setup->currency }}
                </p>
                <h3 class="text-xl sm:text-2xl md:text-4xl font-semibold leading-snug">
                    {{ $landing->discount_price ?? '' }} {{ $setup->currency }} Offer for Limited Time
                </h3>
            </div>
        </div>
    </section>


    {{-- ══ SECTION 8: REVIEW SLIDER ══ --}}
    <section class="py-14 px-4 md:px-[14%] bg-gray-50 overflow-hidden">
        <div class="container mx-auto">
            <h2 class="text-2xl sm:text-3xl md:text-5xl font-black text-center mb-12 text-[#0D2601]">
                What our customers say about us
            </h2>

            <div class="relative px-4 md:px-10">
                <!-- Swiper Container -->
                <div class="swiper rv-swiper">
                    <div class="swiper-wrapper">
                        @if ($landing->img_paths && count($landing->img_paths) > 0)
                            @foreach ($landing->img_paths as $img)
                                <div class="swiper-slide">
                                    <div
                                        class="bg-white rounded-2xl border border-gray-50 overflow-hidden mx-1 mb-10">
                                        <img src="{{ is_array($img) ? ($img['previewUrl'] ?? '') : (str_starts_with($img, 'blob:') || str_starts_with($img, 'data:') ? $img : \Illuminate\Support\Facades\Storage::disk('r2')->url($img)) }}" class="w-full h-auto object-cover"
                                            alt="Customer Review">
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- <div
                    class="swiper-button-prev !w-10 !h-10 !bg-white !text-gray-800 shadow-lg rounded-full after:!text-xs !-left-2 md:!-left-5">
                </div>
                <div
                    class="swiper-button-next !w-10 !h-10 !bg-white !text-gray-800 shadow-lg rounded-full after:!text-xs !-right-2 md:!-right-5">
                </div> --}}

                <div class="swiper-pagination !-bottom-2"></div>
            </div>
        </div>
    </section>


    {{-- ══ SECTION 9: ORDER FORM ══ --}}
    <section id="order" class="py-14 px-4 bg-[#f8faff]">
         <x-landing.order-form :landing="$landing" />
    </section>


    </main>
    {{-- ══ FOOTER ══ --}}
    <footer class="bg-gray-50 pt-12 pb-8 px-4 border-t border-gray-100">
        <div class="max-w-5xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-8">
                <div class="flex items-center gap-3 text-gray-700">
                    <div
                        class="w-10 h-10 rounded-full bg-[#2e8c03]/10 flex items-center justify-center text-[#2e8c03]">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <span class="font-medium text-sm md:text-base">{{ $setup->corporate_address }}</span>
                </div>
                <div class="flex gap-6 font-semibold text-gray-600 text-sm md:text-base">
                    <a href="#" class="hover:text-[#2e8c03] transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-[#2e8c03] transition-colors">Terms & Conditions</a>
                </div>
            </div>
            <div class="border-t border-gray-200 pt-6 text-center">
                <p class="text-gray-500 text-sm">
                <p>© {{ date('Y') }} {{ $setup->shop_name ?? '' }}. All rights reserved.</p>
                </p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swiper !== 'undefined') {
                new Swiper('.rv-swiper', {
                    slidesPerView: 1,
                    spaceBetween: 20,
                    loop: true,
                    centeredSlides: false,
                    autoplay: {
                        delay: 3000,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    breakpoints: {
                        768: {
                            slidesPerView: 2,
                            spaceBetween: 30,
                        },
                        1024: {
                            slidesPerView: 3,
                            spaceBetween: 30,
                        }
                    }
                });
            }
        });
    </script>

</body>

</html>
