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
            font-family:
                {!! $setup->lang === 'bn' ? "'Hind Siliguri', sans-serif" : "'Manrope', sans-serif" !!}
            ;
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
    <style>
        @if(isset($isPreview) && $isPreview)
        .preview-badge-container {
            position: relative;
        }
        .preview-badge-container:hover {
            outline: 2px dashed #13565e;
        }
        .preview-badge {
            position: absolute;
            top: 0;
            left: 0;
            background-color: #13565e;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-bottom-right-radius: 4px;
            z-index: 50;
            opacity: 0.5;
            transition: opacity 0.2s ease-in-out;
            pointer-events: none;
        }
        .preview-badge-container:hover .preview-badge {
            opacity: 1;
        }
        @endif
    </style>
</head>

<body class="text-gray-800 bg-gray-100 overflow-x-hidden">

    <main>
        {{-- ══ SECTION 1: HERO ══ --}}
        <section class="bg-[#0D2601] text-white pt-10 pb-16 px-4 text-center">
            <div class="max-w-7xl mx-auto">

                <img src="{{ $setup->logo_url }}" alt="Logo" width="96" height="96"
                    class="mx-auto mb-6 h-12 md:h-16 w-auto object-contain">

                <div class="border border-[#2e8c03] p-4 md:p-6 rounded mb-6">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-4 {{ isset($isPreview) && $isPreview ? 'preview-badge-container' : '' }}">
                        @if(isset($isPreview) && $isPreview)<span class="preview-badge">Title</span>@endif
                        {{ $product->title ?? $landing->title }}
                    </h1>
                    <p class="prose prose-invert prose-lg md:prose-xl mx-auto leading-snug {{ isset($isPreview) && $isPreview ? 'preview-badge-container' : '' }}">
                        @if(isset($isPreview) && $isPreview)<span class="preview-badge" style="top: -20px;">Short Description</span>@endif
                        {!! $product->short_description ?? $landing->short_description !!}
                    </p>
                </div>

                <a href="#order"
                    class="inline-flex items-center gap-2 bg-[#f5a623] mb-6 text-[#0D2601] px-6 md:px-10 py-3 md:py-4 border-2 border-[#ad7419] rounded-xl font-bold text-lg sm:text-xl md:text-3xl shadow-lg hover:scale-105 transition-transform">
                    Click to order.
                    <img src="{{asset('./images/pointing-right.png')}}" width="30" height="28" class="w-8 md:w-12"
                        alt="">
                </a>

                {{-- Video --}}
                <div class="w-full max-w-6xl mx-auto mt-6">
                    <div
                        class="relative rounded-xl overflow-hidden bg-black shadow-2xl {{ $landing->video ? 'border-[8px] sm:border-[12px] md:border-[18px] border-[#35B11E]' : '' }} {{ isset($isPreview) && $isPreview ? 'preview-badge-container' : '' }}">
                        @if(isset($isPreview) && $isPreview)<span class="preview-badge" style="top: 0; z-index: 50;">Thumbnail / Video</span>@endif
                        @if ($landing->video)
                            <div class="relative aspect-video rounded-2xl overflow-hidden shadow-lg bg-black">
                                <video class="w-full h-full object-cover" controls playsinline
                                    poster="{{ $landing->thumbnail_url ?? $product->display_image_url ?? $product->thumbnail_url ?? asset('./images/default-thumbnail.jpg') }}">
                                    <source src="{{ $landing->video_url }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        @else
                            <div
                                class="relative rounded-2xl overflow-hidden shadow-lg bg-white flex justify-center items-center p-2">
                                <img src="{{ $landing->thumbnail_url ?? $product->thumbnail_url ?? asset('./images/default-thumbnail.jpg') }}"
                                    class="w-full h-auto max-h-[60vh] object-contain rounded-xl"
                                    alt="{{ $landing->name ?? $product->title }}">
                            </div>
                        @endif
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
                        Limited Time Offer
                    </div>

                    <!-- White Price Box -->
                    <div class="bg-white rounded-xl overflow-hidden shadow-inner border border-white">
                        <!-- Regular Price -->
                        <div class="py-6 px-8 {{ isset($isPreview) && $isPreview ? 'preview-badge-container' : '' }}">
                            @if(isset($isPreview) && $isPreview)<span class="preview-badge" style="top: 0; z-index: 50;">Regular Price</span>@endif
                            <p class="text-[#2e8c03] line-through text-2xl md:text-3xl font-medium decoration-4">
                                {{ $landing->regular_price ?? '' }}
                            </p>
                        </div>

                        <!-- Dotted Divider (Matches the image) -->
                        <div class="border-t-3 border-dotted border-[#2e8c03] mx-6"></div>

                        <!-- Offer Price -->
                        <div class="py-10 px-4 {{ isset($isPreview) && $isPreview ? 'preview-badge-container' : '' }}">
                            @if(isset($isPreview) && $isPreview)<span class="preview-badge" style="top: 0; z-index: 50;">Discount Price</span>@endif
                            <p class="text-[#2e8c03] font-medium text-2xl md:text-4xl">
                                {{ $landing->discount_price ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Overlapping 3D CTA Button -->
                <div class="absolute left-1/2 -translate-x-1/2 -bottom-10 w-full flex justify-center">
                    <a href="#order"
                        class="inline-flex items-center gap-4 bg-[#f1a32a] text-[#0D2601] px-10 md:px-20 py-4 rounded-xl font-bold text-2xl md:text-4xl border-3 border-[#b87d21] whitespace-nowrap">
                        Order Now
                        <img src="{{ asset('./images/pointing-right.png') }}" width="48" height="48"
                            class="w-12 md:w-16" alt="hand icon">
                    </a>
                </div>
            </div>
        </section>


        {{-- ══ SECTION 3: BENEFITS ══ --}}
        <section class="bg-[#f1fcf1] py-16 px-4 mt-8 md:mt-10">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10">

                <div class="bg-white rounded-xl overflow-hidden">
                    <div class="benefits-container text-base md:text-xl p-4 md:p-6 {{ isset($isPreview) && $isPreview ? 'preview-badge-container' : '' }}">
                        @if(isset($isPreview) && $isPreview)<span class="preview-badge" style="top: 0; z-index: 50;">Content</span>@endif
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
                    <img src="{{ asset('./images/pointing-right.png') }}" width="32" height="32" class="w-8 md:w-14"
                        alt="hand icon">
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
                            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 {{ isset($isPreview) && $isPreview ? 'preview-badge-container' : '' }}">
                                @if(isset($isPreview) && $isPreview)<span class="preview-badge" style="top: 0; z-index: 50;">Features 2</span>@endif
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
                        <img src="{{ asset('./images/pointing-right.png') }}" width="32" height="32" class="w-8 md:w-14"
                            alt="">
                    </a>
                </div>
            </div>
        </section>


        {{-- ══ SECTION 6: 8-GRID BENEFITS ══ --}}
        <section class="py-14 px-4 bg-white">
            <div class="max-w-7xl mx-auto text-center {{ isset($isPreview) && $isPreview ? 'preview-badge-container' : '' }}">
                @if(isset($isPreview) && $isPreview)<span class="preview-badge" style="top: 0; z-index: 50;">Features</span>@endif
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
                        <img src="{{ asset('./images/pointing-right.png') }}" width="32" height="32" class="w-8 md:w-14"
                            alt="">
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
                        {{ $landing->regular_price ?? '' }}
                    </p>
                    <h3 class="text-xl sm:text-2xl md:text-4xl font-semibold leading-snug">
                        {{ $landing->discount_price ?? '' }} Offer for Limited Time
                    </h3>
                </div>
            </div>
        </section>


        {{-- ══ SECTION 8: REVIEW SLIDER ══ --}}
        <section class="py-14 px-4 md:px-[14%] bg-gray-50 overflow-hidden">
            <div class="container mx-auto {{ isset($isPreview) && $isPreview ? 'preview-badge-container' : '' }}">
                @if(isset($isPreview) && $isPreview)<span class="preview-badge" style="top: 0; z-index: 50;">Review  Images</span>@endif
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
                                        <div class="bg-white rounded-2xl border border-gray-50 overflow-hidden mx-1 mb-10">
                                            <img src="{{ is_array($img) ? ($img['previewUrl'] ?? '') : (str_starts_with($img, 'blob:') || str_starts_with($img, 'data:') ? $img : \Illuminate\Support\Facades\Storage::disk('r2')->url($img)) }}"
                                                class="w-full h-auto object-cover" alt="Customer Review">
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
                    <div class="w-10 h-10 rounded-full bg-[#2e8c03]/10 flex items-center justify-center text-[#2e8c03]">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <span class="font-medium text-sm md:text-base">{{ $setup->corporate_address }}</span>
                </div>
                <div class="flex gap-6 font-semibold text-gray-600 text-sm md:text-base">
                    @php
                        $privacy = \App\Models\ContentSetting::where('company_id', $landing->company_id)->where('page_type', \App\Models\ContentSetting::PRIVACY_POLICY)->first();
                        $terms = \App\Models\ContentSetting::where('company_id', $landing->company_id)->where('page_type', \App\Models\ContentSetting::TERMS_AND_CONDITIONS)->first();
                        $privacyLink = $privacy ? ($privacy->icon_url ?? $privacy->text_content) : '#';
                        $termsLink = $terms ? ($terms->icon_url ?? $terms->text_content) : '#';
                    @endphp
                    <a href="{{ $privacyLink }}" target="_blank" class="hover:text-[#2e8c03] transition-colors">Privacy Policy</a>
                    <a href="{{ $termsLink }}" target="_blank" class="hover:text-[#2e8c03] transition-colors">Terms & Conditions</a>
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
        document.addEventListener('DOMContentLoaded', function () {
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