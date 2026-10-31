<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('components.meta-info.ecommerce-meta.landing-meta', ['landing' => $landing, 'setup' => $setup])
    <link rel="icon" type="image/x-icon" href="{{ $setup->favicon_url }}">
    @include('components.fontawesome')
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('components.meta-info.pixel', ['landing' => $landing, 'setup' => $setup])
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

        .prose ol {
            list-style-type: decimal !important;
            padding-left: 1.5rem !important;
            margin-top: 0.5rem !important;
            margin-bottom: 0.5rem !important;
            text-align: left !important;
        }

        .prose ul {
            list-style-type: disc !important;
            padding-left: 1.5rem !important;
            margin-top: 0.5rem !important;
            margin-bottom: 0.5rem !important;
            text-align: left !important;
        }

        .prose li {
            display: list-item !important;
            margin-top: 2px !important;
            margin-bottom: 2px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        .prose li p {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            display: inline !important;
        }

        .prose hr {
            margin: 10px 0 !important;
        }

        /* Fix: prevent horizontal scroll on mobile */
        .rv-track-wrap {
            overflow: hidden;
        }

        .Landing__34__benifit_sec {
            padding: 70px 0;
            background-color: #f3ffed;
        }

        .Landing__34__benifit_content {
            background-color: #fff;
            border-radius: 20px;
            padding: 30px 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .Landing__34__benifit_content h2 {
            width: 100%;
            color: #0d2601;
            font-size: 32px;
            font-weight: 700;
            line-height: normal;
            letter-spacing: .2px;
            border-radius: 24px;
            border: 8px solid #2e8c03;
            text-align: center;
            padding: 12px 20px;
            margin: 0 auto 35px auto;
        }

        @media (min-width: 768px) {
            .Landing__34__benifit_content h2 {
                font-size: 38px;
                border: 11px solid #2e8c03;
                padding: 15px 30px;
            }
        }

        .Landing__34__benifit_content ul {
            list-style: none !important;
            padding-left: 0 !important;
            margin: 0 !important;
        }

        .Landing__34__benifit_content ul li {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #e5eaf4;
            padding: 12px 5px;
            margin: 0 !important;
        }

        .Landing__34__benifit_content ul li:last-child {
            border-bottom: none;
        }

        .Landing__34__benifit_content ul li p {
            color: #0d2601;
            font-size: 16px;
            font-weight: 500;
            letter-spacing: .2px;
            margin: 0 !important;
            line-height: 1.6;
        }

        @media (min-width: 768px) {
            .Landing__34__benifit_content ul li p {
                font-size: 18px;
            }
        }

        .Landing__34__Banner2 {
            text-align: center;
        }

        .Landing__34__Banner2 h4,
        .Landing__34__Banner2 p,
        .Landing__34__Banner2 article,
        .Landing__34__Banner2 {
            color: #0d2601;
            font-size: 20px;
            font-weight: 400 !important;
            line-height: 1.7;
            margin-bottom: 0;
        }

        @media (min-width: 768px) {

            .Landing__34__Banner2 h4,
            .Landing__34__Banner2 p,
            .Landing__34__Banner2 article,
            .Landing__34__Banner2 {
                font-size: 24px !important;
            }
        }
    </style>
    <style>
        .swiper-pagination-bullet {
            position: relative;
            width: 12px;
            height: 12px;
            margin: 0 8px !important;
            background: #d1d5db;
            opacity: 1;
            transition: all 0.3s ease;
            border-radius: 50%;
            cursor: pointer !important;
            pointer-events: auto !important;
            display: inline-block;
        }

        .swiper-pagination-bullet::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 44px;
            height: 44px;
            min-width: 44px;
            min-height: 44px;
        }

        .swiper-pagination-bullet-active {
            background: #0D2601 !important;
            width: 30px;
            border-radius: 10px;
        }

        .swiper-pagination {
            position: relative !important;
            margin-top: 15px !important;
            z-index: 20 !important;
            pointer-events: auto !important;
        }

        .swiper-button-next,
        .swiper-button-prev {
            z-index: 50;
        }

        .swiper-slide {
            transition: transform 0.3s ease;
            height: auto;
            display: flex;
            justify-content: center;
        }

        .swiper-slide:hover {
            transform: translateY(-5px);
        }

        /* Prevent 1 or 2 images from stretching across the entire screen */
        .rv-swiper .swiper-slide {
            max-width: 360px;
        }

        @media (max-width: 640px) {
            .rv-swiper .swiper-slide {
                max-width: 100%;
            }
        }
    </style>
    <style>
        @if(isset($isPreview) && $isPreview)
            .preview-badge-container {
                position: relative;
                cursor: pointer;
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
                opacity: 0.7;
                transition: opacity 0.2s ease-in-out, transform 0.1s ease-in-out;
                cursor: pointer;
                pointer-events: auto;
            }

            .preview-badge:hover {
                opacity: 1;
                transform: scale(1.05);
            }

            .preview-badge-container:hover .preview-badge {
                opacity: 1;
            }

        @endif
    </style>
</head>

<body class="text-gray-800 bg-gray-100 overflow-x-hidden">
    @php
        $isPreviewMode = !empty($isPreview) || request()->is('*landing/preview*') || !empty($landing->is_preview);
        $productImg = $landing->thumbnail_url ?? $product->display_image_url ?? $product->thumbnail_url ?? null;

        // Static fallback preview data (Only used in Preview mode when actual data is missing)
        $previewTitle = 'প্রিমিয়াম কোয়ালিটি অর্গানিক সজিনা পাতা গুঁড়া (Moringa Powder)';
        $previewButtonText = 'অর্ডার করতে ক্লিক করুন';
        $previewPhone = '017XXXXXXXX';
        $previewShortDesc = '<p>৫২৫ গ্রাম প্রিমিয়াম সজিনা পাউডার + ১০০ গ্রাম কালোজিরা মধু ফ্রি ।</p>';
        $previewRegularPrice = '৪০০ গ্রাম সজিনা পাতার পাউডারের রেগুলার প্রাইস- ১৫০০/=';
        $previewDiscountPrice = '৪০০ গ্রাম সজিনা পাতার পাউডারের অফার প্রাইস- ১০৫০/=';
        $previewContent1 = '<h2>সজিনা পাতার উপকারিতা</h2>
                                    <ul>
                                        <li><p>উচ্চ রক্তচাপ নিয়ন্ত্রণে রাখে।</p></li>
                                        <li><p>এটির এন্টি-ব্যাকটেরিয়াল বৈশিষ্ট্য বিদ্যমান। এটি যকৃত ও কিডনী সুস্থ্য রাখতে এবং রূপের সৌন্দর্য বর্ধক হিসেবেও কাজ করে থাকে।</p></li>
                                        <li><p>রক্তে কোলেস্টেরল কমায়।</p></li>
                                        <li><p>এসিডিটি বা গ্যাস্ট্রিক নিয়ন্ত্রণে রাখে।</p></li>
                                        <li><p>শরীরে কোলেস্টেরল এর মাত্রা নিয়ন্ত্রণেও অন্যতম অবদান রাখে।</p></li>
                                        <li><p>রক্ত স্বল্পতা দুর করে।</p></li>
                                        <li><p>মানুষের শরীরের যে ৯ টি এমাইনো এসিড খাদ্যের মাধ্যমে সরবরাহ করতে হয়, তার সবগুলোই এই সজনে পাতা গুড়ার মধ্যে বিদ্যমান।</p></li>
                                        <li><p>শরীরে সুগারের মাত্রা নিয়ন্ত্রণের মাধ্যমে ডায়াবেটিসের মত কঠিন রোগের বিরুদ্ধে কাজ করে থাকে।</p></li>
                                        <li><p>ত্বকের জন্য উপকারী।</p></li>
                                        <li><p>ঠান্ডা জনিত সমস্যা দূর করে।</p></li>
                                    </ul>';

        $previewContent2 = '<h2>সেবনে সঠিক নিয়ম</h2>
                                    <ul>
                                        <li><p>খালি পেটে এক গ্লাস পানিতে ২ চা চামচ সজনে পাতা মিক্স করে খেতে পারেন।</p></li>
                                        <li><p>মধুর সাথে মিক্স করে খেতে পারেন।</p></li>
                                        <li><p>দুধের সাথে মিক্স করে খেতে পারেন।</p></li>
                                        <li><p>ডালের সাথে মিক্স করে খেতে পারেন।</p></li>
                                        <li><p>বিভিন্ন ধরনের ভাজির সাথে মিশিয়ে খাওয়া যায়।</p></li>
                                        <li><p>পেয়ারার সাথে মিক্স করে খেতে পারেন।</p></li>
                                        <li><p>আমড়ার সাথে মিক্স করে খেতে পারেন।</p></li>
                                        <li><p>কাঁচা আমের সাথে মিশিয়ে খেতে পারেন।</p></li>
                                        <li><p>তরকারির সাথে মিক্স করে খেতে পারেন।</p></li>
                                        <li><p>ত্বকের জন্য উপকারী।</p></li>
                                        <li><p>ঠান্ডা জনিত সমস্যা দূর করে।</p></li>
                                    </ul>';
        $previewDescription = '<p class="text-lg sm:text-xl md:text-[24px] font-normal text-[#0d2601] leading-relaxed m-0">গ্রাম থেকে সংগ্রহ করা শতভাগ ন্যাচারাল সজিনা পাতা নিজেদের তত্বাবধানে স্বাস্থ্য সম্মত পরিবেশে রোদে শুকিয়ে গুড়া করা হয়।প্রোডাক্ট হাতে পেয়ে, দেখে, কোয়ালিটি চেক করে পেমেন্টে করার সুবিধা ।</p>';

        $previewFeatures2 = [
            [
                'title' => 'এতো কিছু থাকতে সজিনা পাতার গুড়া কেনো খাবেন ?',
                'description' => '<p>প্রতিটি ব্যাচ সর্বোচ্চ সতর্কতার সাথে পরীক্ষা করে তৈরি করা হয়, যাতে এর প্রাকৃতিক গুনাগুণ ও পুষ্টিমান অক্ষুণ্ন থাকে।</p>',
                'image' => $productImg
            ]
        ];

        $previewGridFeatures = [
            'ডায়াবেটিস ও সুগার লেভেল নিয়ন্ত্রণে অত্যন্ত কার্যকর।',
            'শরীরের স্বাভাবিক রোগ প্রতিরোধ ক্ষমতা বৃদ্ধি করে।',
            'লিভার ও কিডনি সুস্থ রাখতে বিশেষভাবে সাহায্য করে।',
            'উচ্চ রক্তচাপ ও কোলেস্টেরল নিয়ন্ত্রণে সহায়তা করে।',
            'ত্বক ও চুলের উজ্জ্বলতা বৃদ্ধি করে এবং বয়সের ছাপ রোধ করে।',
            'শরীরের হজম শক্তি ও মেটাবলিজম বৃদ্ধি করে।',
            'ওজন কমাতে এবং অতিরিক্ত মেদ ঝরাতে সাহায্য করে।',
            'ক্লান্তি দূর করে সারাদিন শরীরকে সতেজ ও কর্মক্ষম রাখে।'
        ];

        $previewReviewImages = [
            'https://images.unsplash.com/photo-1544717305-2782549b5136?w=600&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=600&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=600&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=600&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=600&auto=format&fit=crop&q=80',
        ];
    @endphp

    <main>
        {{-- ══ SECTION 1: HERO ══ --}}
        <section class="bg-[#0D2601] text-white pt-10 pb-16 px-4 text-center">
            <div class="max-w-7xl mx-auto">

                <img src="{{ $setup->logo_url }}" alt="Logo" width="96" height="96"
                    class="mx-auto mb-6 h-12 md:h-16 w-auto object-contain">

                <div class="border border-[#2e8c03] p-4 md:p-6 rounded mb-6">
                    <h1 class="text-2xl sm:text-3xl md:text-[45px] font-bold leading-snug md:leading-tight break-words {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge">Title</span>@endif
                        {{ $landing->title ?? ($product->title ?: ($isPreviewMode ? $previewTitle : '')) }}
                    </h1>
                </div>
                <div class="relative {{ $isPreviewMode ? 'preview-badge-container' : '' }} mb-6">
                    @if($isPreviewMode)<span class="preview-badge !text-[10px] !leading-normal"
                    style="top: -20px;">Short Description</span>@endif
                    <div class="prose prose-invert max-w-none mx-auto leading-relaxed">
                        {!! $landing->short_description ?? ($product->short_description ?: ($isPreviewMode ? $previewShortDesc : '')) !!}
                    </div>
                </div>

                <a href="#order"
                    class="inline-flex items-center justify-center gap-2 md:gap-3 bg-[linear-gradient(90deg,#f2a413_0%,#d8a13a_51%,#f2a413_100%)] bg-[length:200%_auto] hover:bg-[position:100%] hover:brightness-105 text-white mt-6 md:mt-8 mb-6 px-8 sm:px-10 md:px-[50px] py-3 md:py-[15px] border-[3px] border-[#ad7419] rounded-[10px] font-bold text-lg sm:text-xl md:text-[30px] leading-snug md:leading-[45px] shadow-[0_20px_20px_0_rgba(0,0,0,0.1)] hover:shadow-2xl transition-all duration-500 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                    @if($isPreviewMode)<span class="preview-badge" style="top: -20px;">Button Text</span>@endif
                    <span>{{ $landing->button_text ?: ($isPreviewMode ? $previewButtonText : 'অর্ডার করতে ক্লিক করুন') }}</span>
                </a>

                {{-- Video --}}
                <div class="w-full max-w-6xl mx-auto mt-6">
                    <div
                        class="relative rounded-xl overflow-hidden bg-black shadow-2xl {{ $landing->video ? 'border-[8px] sm:border-[12px] md:border-[18px] border-[#35B11E]' : '' }} {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge" style="top: 0; z-index: 50;">Thumbnail /
                        Video</span>@endif
                        @if ($landing->video)
                            <div class="relative aspect-video rounded-2xl overflow-hidden shadow-lg bg-black">
                                <video class="w-full h-full object-cover" controls playsinline poster="{{ $productImg }}">
                                    <source src="{{ $landing->video_url }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        @else
                            <div
                                class="relative rounded-2xl overflow-hidden shadow-lg bg-white flex justify-center items-center p-2">
                                <img id="landing1-main-img" src="{{ $productImg }}" width="800" height="600" loading="eager" decoding="async"
                                    fetchpriority="high" class="w-full h-auto max-h-[60vh] object-contain rounded-xl transition-opacity duration-200"
                                    alt="{{ $landing->name ?? $product->title }}">
                            </div>
                        @endif
                    </div>

                    @php
                        $heroGalleries = [];
                        if ($productImg) $heroGalleries[] = $productImg;
                        if (isset($product) && $product->relationLoaded('galleries') ? $product->galleries : ($product->galleries ?? null)) {
                            foreach ($product->galleries as $g) {
                                if (!empty($g->image_url)) {
                                    $heroGalleries[] = $g->image_url;
                                }
                            }
                        }
                        if (isset($product) && $product->type === 'variation' && $product->variations) {
                            foreach ($product->variations as $v) {
                                if (!empty($v->image_url)) {
                                    $heroGalleries[] = $v->image_url;
                                }
                                if ($v->relationLoaded('galleries') ? $v->galleries : ($v->galleries ?? null)) {
                                    foreach ($v->galleries as $vg) {
                                        if (!empty($vg->image_url)) {
                                            $heroGalleries[] = $vg->image_url;
                                        }
                                    }
                                }
                            }
                        }

                        $displayGalleries = $heroGalleries;
                        if (count($heroGalleries) > 1) {
                            while (count($displayGalleries) < 12) {
                                $displayGalleries = array_merge($displayGalleries, $heroGalleries);
                            }
                        }
                    @endphp

                    @if(count($heroGalleries) > 1)
                        <div class="relative mt-4 mx-auto px-2">
                            {{-- Left Arrow --}}
                            <button type="button" aria-label="Scroll product images left"
                                class="l1-gal-prev {{ count($heroGalleries) > 2 ? 'flex' : 'hidden' }} {{ count($heroGalleries) <= 5 ? 'md:hidden' : 'md:flex' }} absolute -left-2 md:-left-4 top-1/2 -translate-y-1/2 z-20 w-8 h-8 md:w-10 md:h-10 rounded-full bg-[#35B11E] text-white shadow-lg items-center justify-center hover:bg-[#2e8c03] transition-all cursor-pointer">
                                <i class="fa-solid fa-chevron-left text-sm"></i>
                            </button>

                            {{-- Swiper Container (Pure 1-direction infinite loop) --}}
                            <div class="swiper landing1-gallery-swiper w-full">
                                <div class="swiper-wrapper py-2 {{ count($heroGalleries) <= 2 ? '!justify-center' : '' }} {{ count($heroGalleries) <= 5 ? 'md:!justify-center' : 'md:!justify-start' }}">
                                    @foreach ($displayGalleries as $index => $imgUrl)
                                        <div class="swiper-slide !w-auto !flex-shrink-0">
                                            <button type="button" aria-label="View product image {{ $index + 1 }}"
                                                data-img-src="{{ $imgUrl }}"
                                                class="landing1-thumb-btn h-20 w-20 md:h-30 md:w-30 shrink-0 rounded-xl overflow-hidden border-2 border-transparent hover:border-[#35B11E]/70 transition-all p-1 bg-white cursor-pointer block">
                                                <img src="{{ $imgUrl }}" alt="{{ $product->title ?? 'Product' }} image {{ $index + 1 }}"
                                                    class="w-full h-full object-contain rounded-lg pointer-events-none">
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Right Arrow --}}
                            <button type="button" aria-label="Scroll product images right"
                                class="l1-gal-next {{ count($heroGalleries) > 2 ? 'flex' : 'hidden' }} {{ count($heroGalleries) <= 5 ? 'md:hidden' : 'md:flex' }} absolute -right-2 md:-right-4 top-1/2 -translate-y-1/2 z-20 w-8 h-8 md:w-10 md:h-10 rounded-full bg-[#35B11E] text-white shadow-lg items-center justify-center hover:bg-[#2e8c03] transition-all cursor-pointer">
                                <i class="fa-solid fa-chevron-right text-sm"></i>
                            </button>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const galWrap = document.querySelector('.landing1-gallery-swiper');
                                if (galWrap) {
                                    galWrap.addEventListener('click', function (e) {
                                        const btn = e.target.closest('.landing1-thumb-btn');
                                        if (!btn) return;
                                        const src = btn.getAttribute('data-img-src');
                                        const mainImg = document.getElementById('landing1-main-img');
                                        if (mainImg && src) {
                                            mainImg.style.opacity = '0.3';
                                            setTimeout(() => {
                                                mainImg.src = src;
                                                mainImg.style.opacity = '1';
                                            }, 150);
                                        }
                                    });
                                }
                            });
                        </script>
                    @endif
                    </div>
                </div>
            </div>
        </section>


        @php
            $regularPrice = ($landing->regular_price && !is_numeric($landing->regular_price))
                ? $landing->regular_price
                : ($isPreviewMode ? $previewRegularPrice : ($landing->regular_price ?: null));

            $discountPrice = ($landing->discount_price && !is_numeric($landing->discount_price))
                ? $landing->discount_price
                : ($isPreviewMode ? $previewDiscountPrice : ($landing->discount_price ?: null));
        @endphp
        @if($regularPrice || $discountPrice)
            {{-- ══ SECTION 2: PROMO PRICE ══ --}}
            <section class="py-16 md:py-24 px-4">
                <div class="max-w-7xl mx-auto relative">
                    <!-- Main Green Container -->
                    <div class="bg-[#2e8c03] p-8 md:p-24 rounded-2xl shadow-2xl text-center">

                        <!-- Red Header Banner -->
                        @php
                            $phone = $landing->phone_number ?: ($isPreviewMode ? $previewPhone : null);
                            $callText = data_get($landing->extras, 'call_text') ?: ($landing->call_text ?? 'আমাদের থেকে বিস্তারিত জানতে এই নাম্বারে কল করুন');
                        @endphp
                        <div
                            class="bg-[#C00000] text-white py-6 px-4 text-xl sm:text-2xl md:text-4xl font-bold rounded-xl mb-12 shadow-lg w-full leading-normal md:leading-relaxed">
                            <span class="inline-block relative {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                @if($isPreviewMode)<span class="preview-badge" style="top: -20px; z-index: 50;">Call Text</span>@endif
                                {{ $callText }}
                            </span>
                            @if($phone)
                                <span class="inline-block relative ml-2 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                    @if($isPreviewMode)<span class="preview-badge" style="top: -20px; z-index: 50;">Phone Number</span>@endif
                                    <b class="font-bold tracking-wider">{{ $phone }}</b>
                                </span>
                            @endif
                        </div>

                        <!-- White Price Box -->
                        <div class="bg-white rounded-xl overflow-hidden shadow-inner border border-white">
                            <!-- Regular Price -->
                            <div class="py-6 px-8 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                @if($isPreviewMode)<span class="preview-badge" style="top: 0; z-index: 50;">Regular
                                Price</span>@endif
                                <p class="text-[#2e8c03] line-through text-2xl md:text-3xl font-normal decoration-2">
                                    {{ $regularPrice ?? '' }}
                                </p>
                            </div>

                            <!-- Dotted Divider (Matches the image) -->
                            <div class="border-t-2 border-dotted border-[#2e8c03] mx-6"></div>

                            <!-- Offer Price -->
                            <div class="py-6 px-4 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                @if($isPreviewMode)<span class="preview-badge" style="top: 0; z-index: 50;">Discount
                                Price</span>@endif
                                <p class="text-[#2e8c03] font-medium text-2xl md:text-4xl">
                                    {{ $discountPrice ?? '' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Overlapping 3D CTA Button -->
                    <div class="absolute left-1/2 -translate-x-1/2 -bottom-10 w-full flex justify-center">
                        <a href="#order"
                            class="inline-flex items-center justify-center bg-[linear-gradient(90deg,#f2a413_0%,#d8a13a_51%,#f2a413_100%)] bg-[length:200%_auto] hover:bg-[position:100%] hover:brightness-105 text-white px-8 sm:px-10 md:px-[50px] py-3 md:py-[15px] border-[3px] border-[#ad7419] rounded-[10px] font-bold text-lg sm:text-xl md:text-[30px] leading-snug md:leading-[45px] shadow-[0_20px_20px_0_rgba(0,0,0,0.1)] hover:shadow-2xl transition-all duration-500 whitespace-nowrap {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" style="top: -20px;">Button Text</span>@endif
                            <span>{{ $landing->button_text ?: ($isPreviewMode ? $previewButtonText : 'অর্ডার করতে ক্লিক করুন') }}</span>
                        </a>
                    </div>
                </div>
            </section>
        @endif


        @php
            $content1 = !empty($landing->extras['content']) ? $landing->extras['content'] : ($isPreviewMode ? $previewContent1 : null);
            $content2 = !empty($landing->extras['content2']) ? $landing->extras['content2'] : ($isPreviewMode ? $previewContent2 : null);
        @endphp
        @if($content1 || $content2)
            {{-- ══ SECTION 3: BENEFITS ══ --}}
            <section class="Landing__34__benifit_sec px-4 mt-8 md:mt-10">
                <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-10 items-stretch">

                    <div
                        class="Landing__34__benifit_content relative h-full {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge"
                        style="top: -15px; left: 10px; z-index: 50;">Content</span>@endif
                        <div class="prose prose-slate max-w-none text-left">
                            {!! $content1 ?? '' !!}
                        </div>
                    </div>

                    <div
                        class="Landing__34__benifit_content relative h-full {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge" style="top: -15px; left: 10px; z-index: 50;">Content
                        2</span>@endif
                        <div class="prose prose-slate max-w-none text-left">
                            {!! $content2 ?? '' !!}
                        </div>
                    </div>
                </div>

                <div class="text-center mt-12">
                    <a href="#order"
                        class="inline-flex items-center justify-center bg-[linear-gradient(90deg,#f2a413_0%,#d8a13a_51%,#f2a413_100%)] bg-[length:200%_auto] hover:bg-[position:100%] hover:brightness-105 text-white px-8 sm:px-10 md:px-[50px] py-3 md:py-[15px] border-[3px] border-[#ad7419] rounded-[10px] font-bold text-lg sm:text-xl md:text-[30px] leading-snug md:leading-[45px] shadow-[0_20px_20px_0_rgba(0,0,0,0.1)] hover:shadow-2xl transition-all duration-500 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge" style="top: -20px;">Button Text</span>@endif
                        <span>{{ $landing->button_text ?: ($isPreviewMode ? $previewButtonText : 'অর্ডার করতে ক্লিক করুন') }}</span>
                    </a>
                </div>
            </section>
        @endif


        @php
            $desc = !empty($landing->description) ? $landing->description : ($isPreviewMode ? $previewDescription : null);
        @endphp
        @if(!empty($desc))
            {{-- ══ SECTION 4: PRODUCT BANNER ══ --}}
            <section class="py-12 md:py-16 px-4 bg-white">
                <div class="max-w-4xl mx-auto text-center {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                    @if($isPreviewMode)<span class="preview-badge"
                    style="top: -15px; left: 10px; z-index: 50;">Details</span>@endif
                    <div class="Landing__34__Banner2 max-w-3xl mx-auto">
                        <article class="prose prose-slate max-w-none text-center">
                            {!! $desc !!}
                        </article>
                    </div>
                </div>
            </section>
        @endif


        @php
            $features2 = (!empty($landing->extras['features2']) && is_array($landing->extras['features2']))
                ? $landing->extras['features2']
                : ($isPreviewMode ? $previewFeatures2 : []);
        @endphp
        @if(!empty($features2) && count($features2) > 0)
            {{-- ══ SECTION 5: WHY TRUST US ══ --}}
            <section class="bg-[#1a3a1a] text-white py-16 px-4">
                <div class="max-w-7xl mx-auto text-center">
                    <div class="space-y-10">
                        @foreach ($features2 as $feature)
                            <div
                                class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                @if($isPreviewMode)<span class="preview-badge" style="top: 0; z-index: 50;">Features
                                2</span>@endif
                                <h2 class="text-xl sm:text-2xl md:text-4xl font-bold text-[#1a3a1a] leading-normal md:leading-relaxed mb-4">
                                    {{ $feature['title'] ?? '' }}
                                </h2>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                                    <article class="prose prose-slate max-w-none text-lg">
                                        {!! $feature['description'] ?? '' !!}
                                    </article>

                                    @php
                                        $fImg = !empty($feature['image']) ? $feature['image'] : ($isPreviewMode ? $productImg : null);
                                        $fImgSrc = '';
                                        if (is_array($fImg)) {
                                            $fImgSrc = $fImg['previewUrl'] ?? $fImg['url'] ?? $fImg['path'] ?? '';
                                        } elseif (is_string($fImg)) {
                                            $fImgSrc = (str_starts_with($fImg, 'blob:') || str_starts_with($fImg, 'data:') || str_starts_with($fImg, 'http') || str_starts_with($fImg, '/'))
                                                ? $fImg
                                                : \Illuminate\Support\Facades\Storage::disk('r2')->url($fImg);
                                        }
                                    @endphp
                                    @if (!empty($fImgSrc))
                                        <div class="rounded-lg overflow-hidden flex justify-center">
                                            <img src="{{ $fImgSrc }}" alt="{{ $feature['title'] ?? 'Feature' }}" width="400"
                                                height="300" loading="lazy" decoding="async"
                                                class="w-full max-h-[300px] object-contain rounded-lg transform hover:scale-105 transition-transform duration-500">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-10">
                        <a href="#order"
                            class="inline-flex items-center justify-center bg-[linear-gradient(90deg,#f2a413_0%,#d8a13a_51%,#f2a413_100%)] bg-[length:200%_auto] hover:bg-[position:100%] hover:brightness-105 text-white px-8 sm:px-10 md:px-[50px] py-3 md:py-[15px] border-[3px] border-[#ad7419] rounded-[10px] font-bold text-lg sm:text-xl md:text-[30px] leading-snug md:leading-[45px] shadow-[0_20px_20px_0_rgba(0,0,0,0.1)] hover:shadow-2xl transition-all duration-500 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" style="top: -20px;">Button Text</span>@endif
                            <span>{{ $landing->button_text ?: ($isPreviewMode ? $previewButtonText : 'অর্ডার করতে ক্লিক করুন') }}</span>
                        </a>
                    </div>
                </div>
            </section>
        @endif


        @php
            $grid = (!empty($landing->extras['features']) && is_array($landing->extras['features']))
                ? $landing->extras['features']
                : ($isPreviewMode ? $previewGridFeatures : []);
        @endphp
        @if(!empty($grid) && count($grid) > 0)
            {{-- ══ SECTION 6: 8-GRID BENEFITS ══ --}}
            <section class="py-14 px-4 bg-white">
                <div class="max-w-7xl mx-auto text-center {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                    @if($isPreviewMode)<span class="preview-badge" style="top: 0; z-index: 50;">Features</span>@endif
                    <div
                        class="inline-block border-4 md:border-10 border-[#4caf50] px-4 py-4 rounded-lg text-base sm:text-lg md:text-[35px] font-bold mb-10 uppercase">
                        {{ data_get($landing->extras, 'features_title') ?: ($isPreviewMode ? 'এতো কিছু থাকতে সজিনা পাতার গুড়া কেনো খাবেন ?' : 'বিশেষ উপকারিতাসমূহ') }}
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 text-sm sm:text-base md:text-xl">
                        @foreach ($grid as $g)
                            <div
                                class="border-2 border-[#4caf50] p-3 md:p-4 rounded-lg flex items-center justify-center min-h-[90px] md:min-h-[120px] text-center text-[#1a3a1a] hover:bg-[#e8f5e9] transition-all duration-300">
                                {{ is_array($g) ? $g['content'] ?? '' : $g }}
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-10">
                        <a href="#order"
                            class="inline-flex items-center justify-center bg-[linear-gradient(90deg,#f2a413_0%,#d8a13a_51%,#f2a413_100%)] bg-[length:200%_auto] hover:bg-[position:100%] hover:brightness-105 text-white px-8 sm:px-10 md:px-[50px] py-3 md:py-[15px] border-[3px] border-[#ad7419] rounded-[10px] font-bold text-lg sm:text-xl md:text-[30px] leading-snug md:leading-[45px] shadow-[0_20px_20px_0_rgba(0,0,0,0.1)] hover:shadow-2xl transition-all duration-500 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" style="top: -20px;">Button Text</span>@endif
                            <span>{{ $landing->button_text ?: ($isPreviewMode ? $previewButtonText : 'অর্ডার করতে ক্লিক করুন') }}</span>
                        </a>
                    </div>
                </div>
            </section>
        @endif

        @if($regularPrice || $discountPrice)
            {{-- ══ SECTION 7: PRICING ══ --}}
            <section class="py-14 px-4">
                <div
                    class="max-w-7xl mx-auto border-4 md:border-6 border-dashed border-[#1f5e00] rounded-3xl p-6 md:p-14 text-center bg-white shadow-sm">
                    <h2
                        class="text-xl sm:text-2xl md:text-4xl font-bold text-[#1f5e00] leading-normal md:leading-relaxed mb-4 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge" style="top: 0; z-index: 50;">Title</span>@endif
                        {{ $landing->title ?? ($product->title ?: ($isPreviewMode ? $previewTitle : '')) }}
                    </h2>
                    @php
                        $pricingSubtitle = data_get($landing->extras, 'pricing_subtitle') ?: ($landing->pricing_subtitle ?? 'সাশ্রয়ী মূল্যে সেরা পণ্য');
                    @endphp
                    <p class="text-base md:text-2xl text-[#1f5e00] mb-8 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge" style="top: -15px; left: 10px; z-index: 50;">Pricing Subtitle</span>@endif
                        {{ $pricingSubtitle }}
                    </p>
                    <div class="bg-[#C00000] text-white p-6 md:p-10 rounded-xl shadow-lg">
                        @if($regularPrice)
                            <div class="{{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                @if($isPreviewMode)<span class="preview-badge" style="top: -20px; z-index: 50;">Regular
                                Price</span>@endif
                                <p class="text-base md:text-2xl font-medium mb-3 line-through text-white">
                                    {{ $regularPrice }}
                                </p>
                            </div>
                        @endif
                        <div class="{{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" style="top: -20px; z-index: 50;">Discount
                            Price</span>@endif
                            <h3 class="text-xl sm:text-2xl md:text-4xl font-semibold leading-snug">
                                {{ $discountPrice ?: $regularPrice }}
                            </h3>
                        </div>
                    </div>
                </div>
            </section>
        @endif


        @php
            $reviewImgs = (!empty($landing->img_paths) && count($landing->img_paths) > 0)
                ? $landing->img_paths
                : ($isPreviewMode ? $previewReviewImages : []);
        @endphp
        @if(!empty($reviewImgs) && count($reviewImgs) > 0)
            {{-- ══ SECTION 8: REVIEW SLIDER ══ --}}
            <section class="py-14 px-4 md:px-[14%] bg-gray-50 overflow-hidden">
                <div class="container mx-auto">
                    <div class="text-center mb-12">
                        <h2 class="inline-block text-xl sm:text-2xl md:text-4xl font-bold text-[#0D2601] leading-normal md:leading-relaxed relative {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" data-field="review_title" style="top: -16px; left: 50%; transform: translateX(-50%);">Review Title</span>@endif
                            {{ !empty($landing->extras['review_title']) ? $landing->extras['review_title'] : 'সম্মানিত কাস্টমারদের মতামত' }}
                        </h2>
                    </div>

                    <div class="relative px-4 md:px-10 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge" data-field="img_paths" style="top: -12px; z-index: 50;">Review Images</span>@endif
                        <!-- Swiper Container -->
                        <div class="swiper rv-swiper">
                            <div class="swiper-wrapper {{ count($reviewImgs) < 3 ? '!justify-center' : '' }}">
                                @foreach ($reviewImgs as $img)
                                    <div class="swiper-slide !flex !justify-center">
                                        <div
                                            class="w-full max-w-[340px] bg-white rounded-2xl border border-gray-100 overflow-hidden mx-auto mb-10 flex items-center justify-center p-3 shadow-sm h-[320px] md:h-[380px]">
                                            <img src="{{ is_array($img) ? ($img['previewUrl'] ?? '') : (str_starts_with($img, 'blob:') || str_starts_with($img, 'data:') || str_starts_with($img, 'http') || str_starts_with($img, '/') ? $img : \Illuminate\Support\Facades\Storage::disk('r2')->url($img)) }}"
                                                class="w-full h-full object-cover rounded-xl" alt="Customer Review">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="swiper-pagination !-bottom-2"></div>
                    </div>
                </div>
            </section>
        @endif


        {{-- ══ SECTION 9: ORDER FORM ══ --}}
        <section id="order" class="py-10 px-4 bg-[#f8faff] [&_#landing-order-form_h2]:!text-xl sm:[&_#landing-order-form_h2]:!text-2xl md:[&_#landing-order-form_h2]:!text-4xl [&_#landing-order-form_h2]:!font-bold [&_#landing-order-form_h2]:!leading-normal md:[&_#landing-order-form_h2]:!leading-relaxed">
            <x-landing.order-form :landing="$landing" :isPreview="$isPreviewMode" />
        </section>


    </main>
    {{-- ══ FOOTER ══ --}}
    <footer class="bg-gray-50 pt-12 pb-8 px-4 border-t border-gray-100">
        <div class="max-w-5xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-8">
                <div class="flex items-center gap-3 text-gray-700 relative {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                    @if($isPreviewMode)<span class="preview-badge" style="top: -16px; left: 0;">Site Settings &gt; Store Address</span>@endif
                    <div class="w-10 h-10 rounded-full bg-[#2e8c03]/10 flex items-center justify-center text-[#2e8c03]">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <span class="font-medium text-sm md:text-base">{{ $setup->store_address ?? $setup->corporate_address ?? 'Dhaka, Bangladesh' }}</span>
                </div>
                <div class="flex gap-6 font-semibold text-gray-600 text-sm md:text-base relative {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                    @if($isPreviewMode)<span class="preview-badge" style="top: -16px; right: 0; left: auto;">Content Settings &gt; Footer &amp; Legal Tab</span>@endif
                    @php
                        $privacy = \App\Models\ContentSetting::where('company_id', $landing->company_id ?? null)->where('page_type', \App\Models\ContentSetting::PRIVACY_POLICY)->first();
                        $terms = \App\Models\ContentSetting::where('company_id', $landing->company_id ?? null)->where('page_type', \App\Models\ContentSetting::TERMS_AND_CONDITIONS)->first();
                        $privacyLink = $privacy ? ($privacy->icon_url ?? $privacy->text_content) : '#';
                        $termsLink = $terms ? ($terms->icon_url ?? $terms->text_content) : '#';
                    @endphp
                    <a href="{{ $privacyLink }}" target="_blank" rel="noopener noreferrer" class="hover:text-[#2e8c03] transition-colors">Privacy Policy</a>
                    <a href="{{ $termsLink }}" target="_blank" rel="noopener noreferrer" class="hover:text-[#2e8c03] transition-colors">Terms & Conditions</a>
                </div>
            </div>
            <div class="border-t border-gray-200 pt-6 text-center relative {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                @if($isPreviewMode)<span class="preview-badge" style="top: 0; left: 50%; transform: translateX(-50%);">Site Settings &gt; Shop Name</span>@endif
                <p class="text-gray-500 text-sm">
                    &copy; {{ date('Y') }} {{ $setup->shop_name ?? 'Your Shop' }}. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            function startSwiper() {
                const el = document.querySelector('.rv-swiper');
                const galSwiperEl = document.querySelector('.landing1-gallery-swiper');

                const runInit = function (SwiperClass) {
                    if (!SwiperClass) return;

                    // Hero Product Gallery Swiper (Continuous 1-direction infinite loop)
                    if (galSwiperEl && !galSwiperEl.swiper) {
                        new SwiperClass(galSwiperEl, {
                            slidesPerView: 'auto',
                            spaceBetween: 12,
                            loop: true,
                            loopPreventsSliding: false,
                            grabCursor: true,
                            observer: true,
                            observeParents: true,
                            preventClicks: false,
                            preventClicksPropagation: false,
                            autoplay: {
                                delay: 2500,
                                disableOnInteraction: false,
                                pauseOnMouseEnter: false,
                            },
                            navigation: {
                                nextEl: '.l1-gal-next',
                                prevEl: '.l1-gal-prev',
                            },
                        });
                    }

                    // Reviews Swiper
                    if (el) {
                        if (el.swiper) {
                            el.swiper.destroy(true, true);
                        }

                        const totalSlides = el.querySelectorAll('.swiper-slide').length;
                        const paginationEl = el.parentElement.querySelector('.swiper-pagination') || '.swiper-pagination';

                        new SwiperClass(el, {
                            slidesPerView: 1,
                            spaceBetween: 20,
                            loop: totalSlides >= 2,
                            autoplay: {
                                delay: 2500,
                                disableOnInteraction: false,
                                pauseOnMouseEnter: true,
                            },
                            pagination: {
                                el: paginationEl,
                                clickable: true,
                            },
                            navigation: {
                                nextEl: '.swiper-button-next',
                                prevEl: '.swiper-button-prev',
                            },
                            breakpoints: {
                                640: {
                                    slidesPerView: Math.min(2, totalSlides),
                                    spaceBetween: 20,
                                },
                                1024: {
                                    slidesPerView: Math.min(3, totalSlides),
                                    spaceBetween: 30,
                                }
                            }
                        });
                    }
                };

                if (window.Swiper) {
                    runInit(window.Swiper);
                } else if (window.SwiperPromise) {
                    window.SwiperPromise.then(function (SwiperClass) {
                        if (SwiperClass) runInit(SwiperClass);
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', startSwiper);
            } else {
                startSwiper();
            }

            window.addEventListener('load', startSwiper);
            setTimeout(startSwiper, 500);
            setTimeout(startSwiper, 1500);
        })();
    </script>

    @if(!empty($isPreviewMode) || (isset($isPreview) && $isPreview))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.addEventListener('click', function (e) {
                    const badge = e.target.closest('.preview-badge');
                    const container = e.target.closest('.preview-badge-container');
                    const targetEl = badge || container;
                    if (!targetEl) return;

                    const badgeEl = badge || targetEl.querySelector('.preview-badge');
                    if (!badgeEl) return;
                    e.preventDefault();

                    let field = targetEl.getAttribute('data-field') || (badgeEl ? badgeEl.getAttribute('data-field') : null);

                    if (!field) {
                        const text = badgeEl.textContent.toLowerCase().replace(/\s+/g, ' ').trim();
                        if (text.includes('call text') || text === 'call text' || text.includes('call')) {
                            field = 'call_text';
                        } else if (text.includes('pricing subtitle') || text.includes('subtitle')) {
                            field = 'pricing_subtitle';
                        } else if (text.includes('regular price')) {
                            field = 'regular_price';
                        } else if (text.includes('discount price')) {
                            field = 'discount_price';
                        } else if (text === 'title') {
                            field = 'title';
                        } else if (text.includes('short description')) {
                            field = 'short_description';
                        } else if (text.includes('thumbnail') || text.includes('video')) {
                            field = 'thumbnail';
                        } else if (text.includes('review title') || text.includes('review_title')) {
                            field = 'review_title';
                        } else if (text.includes('review')) {
                            field = 'img_paths';
                        } else if (text.includes('details') || text.includes('description')) {
                            field = 'description';
                        } else if (text.includes('phone')) {
                            field = 'phone_number';
                        } else if (text.includes('button text')) {
                            field = 'button_text';
                        } else if (text.includes('features 6') || text.includes('feature 6')) {
                            field = 'features6';
                        } else if (text.includes('features 5') || text.includes('feature 5')) {
                            field = 'features5';
                        } else if (text.includes('features 4') || text.includes('feature 4')) {
                            field = 'features4';
                        } else if (text.includes('features 3') || text.includes('feature 3')) {
                            field = 'features3';
                        } else if (text.includes('features 2') || text.includes('feature 2')) {
                            field = 'features2';
                        } else if (text.includes('features') || text.includes('feature')) {
                            field = 'features';
                        } else if (text.includes('content 2')) {
                            field = 'content2';
                        } else if (text.includes('content')) {
                            field = 'content';
                        }
                    }

                    if (field) {
                        window.parent.postMessage({
                            type: 'focus_field',
                            field: field
                        }, '*');
                    }
                });
            });
        </script>
    @endif

</body>

</html>
