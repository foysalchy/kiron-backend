<!DOCTYPE html>
<html lang="bn">

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
            font-family: 'Hind Siliguri', sans-serif;
            background-color: #f7fbf8;
            color: #1f2937;
        }

        .bg-pattern {
            background-color: #f4fbf5;
            background-image:
                radial-gradient(circle at 15% 15%, rgba(0, 112, 60, 0.04) 0%, transparent 40%),
                radial-gradient(circle at 85% 85%, rgba(0, 112, 60, 0.04) 0%, transparent 40%);
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
            margin-top: 4px !important;
            margin-bottom: 4px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            color: #11311e !important;
        }

        .prose li::marker {
            color: #00703c !important;
        }

        .prose li p {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            display: inline !important;
        }

        .prose h1,
        .prose h2,
        .prose h3,
        .prose h4,
        .prose h5,
        .prose h6 {
            color: #00703c !important;
            font-weight: 900 !important;
            line-height: 1.25 !important;
        }

        .prose p {
            color: #1b3a27 !important;
            line-height: 1.7 !important;
        }

        .prose strong,
        .prose b {
            color: #0c7143 !important;
            font-weight: 700 !important;
        }

        .prose a {
            color: #00703c !important;
            text-decoration: underline !important;
        }

        /* Pill Button matching Reference Image */
        .landing5-pill-btn {
            background-color: #00703c;
            color: #ffffff;
            border-radius: 9999px;
            font-weight: 700;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(0, 112, 60, 0.35);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .landing5-pill-btn:hover {
            background-color: #00582f;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 112, 60, 0.45);
        }

        /* Swiper styling */
        .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background: #ffffff !important;
            opacity: 0.6;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .swiper-pagination-bullet:hover {
            opacity: 0.9;
            transform: scale(1.15);
        }

        .swiper-pagination-bullet-active {
            opacity: 1 !important;
            width: 26px;
            border-radius: 9999px;
            background: #ffffff !important;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.6);
        }

        .benefit-pagination .swiper-pagination-bullet-active {
            background: #10b981;
            opacity: 1;
            width: 20px;
        }

        .swiper-slide {
            height: auto;
            display: flex;
            justify-content: center;
        }

        /* Preview Badge System */
        @if(isset($isPreview) && $isPreview)
            .preview-badge-container {
                position: relative;
                cursor: pointer;
            }

            .preview-badge-container:hover {
                outline: 2px dashed #00703c;
            }

            .preview-badge {
                position: absolute;
                top: 0;
                left: 0;
                background-color: #00703c;
                color: #ffffff;
                font-size: 10px;
                font-weight: bold;
                padding: 2px 6px;
                border-bottom-right-radius: 4px;
                z-index: 50;
                opacity: 0.75;
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

<body class="bg-[#f7fbf8] text-gray-800 antialiased overflow-x-hidden">
    @php
        $isPreviewMode = !empty($isPreview) || request()->is('*landing/preview*') || !empty($landing->is_preview);

        // Multiple Products Resolution
        $multiProducts = isset($products) && $products->isNotEmpty()
            ? $products
            : ($landing->products ?? collect([$product]));

        // Exact Bangla texts matching reference image
        $exactImgTitle = 'খাঁটি দেশি ঘি – পুষ্টি ও স্বাদের পরিপূর্ণতা!';
        $exactImgButtonText = 'অর্ডার করতে চাই';
        $exactImgPhone = '017XXXXXXXX';
        $exactImgShortDesc = '<p>ঘাসখাওয়া, স্বাস্থ্যসম্মত ও নন-ফ্রিজ প্রিকল এবং হাতে বানানো ১০০% খাঁটি দেশি গাভীর ঘি সারা বাংলাদেশ হোম ডেলিভারি।</p>';
        $exactImgTrustDesc = '<h2 class="text-2xl sm:text-3xl md:text-4xl font-black tracking-wide leading-snug mb-3">আপনার পরিবারের জন্য নিরাপদ ও খাঁটি ঘি</h2><p>আমাদের ঘি খাঁটি দুধের সর থেকে তৈরি, যা কোনো কেমিক্যাল বা প্রিজারভেটিভ ছাড়াই প্রস্তুত। এটি বাচ্চা, বয়স্ক, শুঁটকি কিংবা প্রাত্যহিক সাথে ব্যবহার করা স্বাস্থ্য সচেতন পরিবারের সব সদস্যের জন্য পিওর ও পুষ্টিকর এই ঘি আপনার প্রতিদিনের খাবারের আসল স্বাদের নতুন মাত্রা!</p>';

        $exactImgContent1 = '<h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#1b3a27] leading-snug">ঘি এর বিশেষ উপকারিতা যা আমাদের প্রোডাক্টকে অনন্য করে তোলে</h2><p class="text-xs sm:text-sm md:text-base text-gray-600 font-medium mt-2">খাঁটি উপাদান আর স্বাস্থ্যকর প্রস্তুত প্রণালী – জেনে নিন কেন আমাদের ঘি পরিবারের সবার জন্য সেরা।</p>';

        $exactImgContent2 = '<h3>আমাদের থেকে কেন কিনবেন?</h3>
                                                                <ul>
                                                                     <li>মাত্র ৭ দিনে চুল পড়া হ্রাস</li>
                                                                     <li>খুশকি ও মাথার চুলকানি দূর করে</li>
                                                                     <li>চুল ঘন, লম্বা ও ঝলমলে করে</li>
                                                                     <li>১০০% প্রাকৃতিক উপাদান</li>
                                                                     <li>কোনো পার্শ্বপ্রতিক্রিয়া নেই</li>
                                                                     <li>নারী ও পুরুষ উভয়ের জন্য উপযোগী</li>
                                                                 </ul>';

        $exactImgRegularPrice = 'সাধারণ মূল্য: ৫০০ গ্রাম - ৭৪৯ টাকা, ১ কেজি - ১৪৭৫ টাকা';
        $exactImgDiscountPrice = 'অফার মূল্য: মাত্র ৩২০৳!**';

        // High quality preview default product image matching the ghee bottle
        $previewDefaultGheeImg = 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?w=800&auto=format&fit=crop&q=80';

        // Title resolving
        if ($isPreviewMode) {
            $isTitlePlaceholder = empty($landing->title) || str_contains($landing->title, 'Diaper') || str_contains($landing->title, 'Milk Stage') || str_contains($landing->title, 'ডেমো');
            $title = $isTitlePlaceholder ? $exactImgTitle : $landing->title;
        } else {
            $title = $landing->title ?: ($product->title ?: $exactImgTitle);
        }

        // Thumbnail resolving
        if ($isPreviewMode) {
            $productImg = (!empty($landing->thumbnail_url) && !str_contains($landing->thumbnail_url, 'diaper'))
                ? $landing->thumbnail_url
                : $previewDefaultGheeImg;
        } else {
            $productImg = $landing->thumbnail_url ?? $product->display_image_url ?? $product->thumbnail_url ?? $previewDefaultGheeImg;
        }

        // Short Description resolving
        if ($isPreviewMode) {
            $isShortDescPlaceholder = empty($landing->short_description) || str_contains($landing->short_description, 'About This Product') || str_contains($landing->short_description, 'prawns') || str_contains($landing->short_description, 'পাউডার');
            $shortDesc = $isShortDescPlaceholder ? $exactImgShortDesc : $landing->short_description;
        } else {
            $shortDesc = $landing->short_description ?: $exactImgShortDesc;
        }

        $btnText = $landing->button_text ?: $exactImgButtonText;
        $phone = $landing->phone_number ?: ($isPreviewMode ? $exactImgPhone : null);

        // Stock check matching order-form component and other landings
        $isSingleProduct = ($product->type ?? 'single') === 'single';
        $isSingleOutOfStock = $isSingleProduct && (
            ($product->stock_status ?? '') === 'out_of_stock' ||
            (!empty($product->manage_stock) && (($product->available_stock ?? 0) <= 0))
        );

        $allVariationsOutOfStock = true;
        if (!$isSingleProduct && !empty($product->variations)) {
            foreach ($product->variations as $v) {
                $vOutOfStock = (
                    ($v->stock_status ?? '') === 'out_of_stock' ||
                    (!empty($product->manage_stock) && (($v->available_stock ?? 0) <= 0))
                );
                if (!$vOutOfStock) {
                    $allVariationsOutOfStock = false;
                    break;
                }
            }
        }
        $isEntirelyOutOfStock = !$isPreviewMode && ($isSingleProduct ? $isSingleOutOfStock : $allVariationsOutOfStock);

        // Price resolving
        $regularPrice = ($isPreviewMode && (empty($landing->regular_price) || str_contains($landing->regular_price, 'সজিনা'))) ? $exactImgRegularPrice : ($landing->regular_price ?: $exactImgRegularPrice);
        $discountPrice = ($isPreviewMode && (empty($landing->discount_price) || str_contains($landing->discount_price, 'সজিনা'))) ? $exactImgDiscountPrice : ($landing->discount_price ?: $exactImgDiscountPrice);

        // Offer Box Title resolving
        $exactImgOfferTitle = 'আজই অর্ডার করলে পাচ্ছেন বিশেষ মূল্যছাড়!';
        $offerTitle = (!empty($landing->extras['offer_title']))
            ? $landing->extras['offer_title']
            : (!empty($landing->offer_title) ? $landing->offer_title : $exactImgOfferTitle);

        // Trust description resolving
        $desc = ($isPreviewMode && (empty($landing->description) || str_contains($landing->description, 'Diaper') || str_contains($landing->description, 'About This Product'))) ? $exactImgTrustDesc : ($landing->description ?: $exactImgTrustDesc);

        // Content 1 & 2 resolving
        $content1 = ($isPreviewMode && (empty($landing->extras['content']) || str_contains($landing->extras['content'], 'Product Overview')))
            ? $exactImgContent1
            : (!empty($landing->extras['content']) ? $landing->extras['content'] : ($isPreviewMode ? $exactImgContent1 : null));

        $content2 = ($isPreviewMode && (empty($landing->extras['content2']) || str_contains($landing->extras['content2'], 'Product Overview')))
            ? $exactImgContent2
            : (!empty($landing->extras['content2']) ? $landing->extras['content2'] : ($isPreviewMode ? $exactImgContent2 : null));

        // Reviews sample data matching image style (review cards with rating, quote, and product pack thumbnail)
        $previewReviewCards = [
            [
                'name' => 'Khaled Masud',
                'role' => 'Web Designer',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
                'comment' => 'আলহামদুলিল্লাহ ঘির কোয়ালিটি অনেক ভালো ছিল। ঘ্রাণ ও স্বাদ দুটোই নিখুঁত এবং একদম খাঁটি। সময়মতো ডেলিভারি পেয়েছি। যেকোনো রান্নায় বা ভাতে খেলে চমৎকার টেস্ট আসে!',
                'rating' => 5,
                'img' => $productImg
            ],
            [
                'name' => 'Rezaul Karim',
                'role' => 'Customer',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
                'comment' => 'আগে অনেক জায়গা থেকে ঘি কিনে প্রতারিত হয়েছি, কিন্তু আপনাদের ঘি সত্যি অসাধারণ ও খাঁটি। পরিবারে সবাই পছন্দ করেছে। ধন্যবাদ সুন্দর সার্ভিসের জন্য!',
                'rating' => 5,
                'img' => $productImg
            ],
            [
                'name' => 'Tanvir Ahmed',
                'role' => 'Food Enthusiast',
                'avatar' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?w=150&auto=format&fit=crop&q=80',
                'comment' => 'প্রোডাক্ট হাতে পেয়ে চেক করে পেমেন্ট দিয়েছি। প্যাকেজিং খুবই সিকিউর ছিল এবং ঘির সুবাস এক কথায় চমৎকার! বারবার অর্ডার করব ইনশাআল্লাহ।',
                'rating' => 5,
                'img' => $productImg
            ]
        ];

        $previewDefaultReviewImgs = [
            'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1544717305-2782549b5136?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=800&auto=format&fit=crop&q=80',
        ];

        $reviewImgs = (!empty($landing->img_paths) && count($landing->img_paths) > 0)
            ? $landing->img_paths
            : (!empty($landing->extras['img_paths']) ? $landing->extras['img_paths'] : ($isPreviewMode ? $previewDefaultReviewImgs : []));

        // Benefits / Features list matching reference screenshot in Bangla
        $previewBenefits = [
            [
                'title' => '১০০% খাঁটি ও প্রাকৃতিক',
                'desc' => 'ঘাসখাওয়া দেশি গাভীর দুধের খাঁটি সর থেকে প্রস্তুত, যাতে কোনো কেমিক্যাল বা প্রিজারভেটিভ নেই।',
                'image' => 'https://cdn-icons-png.flaticon.com/512/2909/2909765.png'
            ],
            [
                'title' => 'হাতে বানানো ঐতিহ্যবাহী ঘি',
                'desc' => 'দাদী-নানীদের চিরায়ত প্রাচীন পদ্ধতিতে ঘরে তৈরি খাঁটি সুবাস ও পুষ্টিমান অক্ষুণ্ণ রেখে প্রস্তুত।',
                'image' => 'https://cdn-icons-png.flaticon.com/512/1888/1888654.png'
            ],
            [
                'title' => 'উচ্চ পুষ্টিগুণ ও রোগ প্রতিরোধক',
                'desc' => 'শরীরের হজম শক্তি, রোগ প্রতিরোধ ক্ষমতা ও কর্মক্ষমতা বৃদ্ধিতে অতুলনীয় ভূমিকা রাখে।',
                'image' => 'https://cdn-icons-png.flaticon.com/512/2965/2965567.png'
            ],
            [
                'title' => 'আসল সুবাস ও চমৎকার স্বাদ',
                'desc' => 'গরম ভাত, খিচুড়ি কিংবা রান্নায় আসল বাঙালি স্বাদের নতুন মাত্রা এনে দেয়।',
                'image' => 'https://cdn-icons-png.flaticon.com/512/706/706195.png'
            ],
            [
                'title' => 'স্মৃতিশক্তি ও হার্টের যত্ন',
                'desc' => 'শিশুদের মেধা বিকাশ, বয়স্কদের হাড় মজবুত এবং হার্টের সুস্থতা বজায় রাখতে অত্যন্ত কার্যকরী।',
                'image' => 'https://cdn-icons-png.flaticon.com/512/2965/2965567.png'
            ],
            [
                'title' => 'ল্যাব টেস্টেড ও বিশ্বস্ত',
                'desc' => 'সম্পূর্ণ স্বাস্থ্যসম্মত পরিবেশে প্রস্তুত এবং প্রতিটি ব্যাচের শতভাগ বিশুদ্ধতা নিশ্চিতকৃত।',
                'image' => 'https://cdn-icons-png.flaticon.com/512/3588/3588658.png'
            ],
        ];

        $benefitsBadge = data_get($landing->extras, 'benefits_badge') ?: 'আমাদের বিশেষত্ব';
        $benefitsTitle = data_get($landing->extras, 'features_title') ?: (data_get($landing->extras, 'benefits_title') ?: 'ঘি এর বিশেষ উপকারিতাসমূহ যা আমাদের প্রোডাক্টকে অনন্য করে তোলে');
        $benefitsSubtitle = data_get($landing->extras, 'features_subtitle') ?: 'খাঁটি উপাদান আর স্বাস্থ্যকর প্রস্তুত প্রণালী – জেনে নিন কেন আমাদের ঘি পরিবারের সবার জন্য সেরা।';
    @endphp

    <main class="w-full">

        {{-- ══ SECTION 1: HEADER & HERO ══ --}}
        <header class="w-full bg-pattern py-8 md:py-14 px-4 border-b border-emerald-900/10">
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-10 items-center">

                {{-- Left Text Box --}}
                <div class="space-y-4 md:space-y-5 text-center md:text-left flex flex-col justify-center">
                    {{-- Logo matching other landings --}}
                    <div>
                        @if(!empty($setup->logo_url))
                            <img src="{{ $setup->logo_url }}" alt="Logo" width="160" height="48"
                                class="h-10 sm:h-12 md:h-14 w-auto object-contain mx-auto md:mx-0">
                        @else
                            <div class="inline-flex items-center gap-2 text-[#00703c] font-black text-2xl tracking-tight">
                                <i class="fa-solid fa-seedling text-xl"></i>
                                <span>{{ $setup->shop_name ?? ($setup->store_name ?? 'FALAO FOOD') }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Main Headline --}}
                    <h1
                        class="text-3xl sm:text-4xl md:text-[40px] font-black text-[#00703c] leading-tight md:leading-[1.2] {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge">Title</span>@endif
                        {{ $title }}
                    </h1>

                    {{-- Short Description --}}
                    @if($shortDesc)
                        <div
                            class="text-sm sm:text-base text-gray-700 leading-relaxed font-normal {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge">Short Description</span>@endif
                            {!! $shortDesc !!}
                        </div>
                    @endif

                    {{-- Order CTA Button (Wide rectangular shape matching image) --}}
                    <div class="pt-2">
                        @if($isEntirelyOutOfStock)
                            <button type="button" disabled
                                class="w-full inline-flex items-center justify-center gap-2 bg-gray-400 cursor-not-allowed text-white font-bold py-3.5 px-6 rounded-sm sm:rounded-md text-base sm:text-lg shadow-sm opacity-80">
                                <i class="fa-solid fa-ban"></i>
                                <span>স্টক আউট</span>
                            </button>
                        @else
                            <a href="#landing-products-section"
                                class="w-full inline-flex items-center justify-center gap-2 bg-[#0e7747] hover:bg-[#095934] text-white font-bold py-3.5 px-6 rounded-sm sm:rounded-md text-base sm:text-lg shadow-sm hover:shadow transition-all {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                @if($isPreviewMode)<span class="preview-badge" style="top: -12px;">Button Text</span>@endif
                                <i class="fa-solid fa-cart-shopping"></i>
                                <span>{{ $btnText }}</span>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Right Hero Product Image (Larger square, minimal rounded corner with subtle border) --}}
                <div class="flex justify-center md:justify-end">
                    <div
                        class="relative w-full max-w-[480px] aspect-square rounded-md sm:rounded-lg overflow-hidden border border-[#52af82] p-1 bg-white shadow-sm flex items-center justify-center {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge">Thumbnail</span>@endif
                        <img src="{{ $productImg }}" alt="{{ $title }}" width="480" height="480"
                            class="w-full h-full object-cover rounded-sm sm:rounded">
                    </div>
                </div>
            </div>
        </header>

        {{-- ══ MULTIPLE PRODUCTS SECTION (Just After Hero Section) ══ --}}
        @if ($multiProducts && $multiProducts->isNotEmpty())
            <section id="landing-products-section"
                class="w-full py-12 md:py-16 px-4 bg-[#f1f8f3] border-b border-emerald-900/10 scroll-mt-20">
                <div class="max-w-6xl mx-auto">
                    @php
                        $productsSectionLang = data_get($landing->extras ?? null, 'order_form_lang') ?: ($landing->order_form_lang ?? 'bn');
                        $isProductsSectionEn = ($productsSectionLang === 'en');
                    @endphp
                    <div class="text-center mb-8 md:mb-12">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#00703c] leading-tight mb-2">
                            {{ $isProductsSectionEn ? 'Our Popular Products' : 'আমাদের জনপ্রিয় পণ্যসমূহ' }}
                        </h2>
                        <p class="text-sm sm:text-base text-gray-600">
                            {{ $isProductsSectionEn ? 'Choose your desired product and place your order directly' : 'আপনার পছন্দের পণ্যটি বেছে নিন এবং সরাসরি অর্ডার করুন' }}
                        </p>
                    </div>

                    @if(!empty($setup->free_shipping_text))
                        <div class="mb-8 p-3.5 bg-amber-50 border-2 border-dashed border-amber-400 rounded-xl flex items-center justify-center gap-2.5 text-amber-900 text-sm sm:text-base md:text-xl font-bold shadow-xs">
                            <i class="fa-solid fa-gift text-amber-600 text-lg sm:text-xl animate-bounce"></i>
                            <span>{{ $setup->free_shipping_text }}</span>
                        </div>
                    @endif

                    {{-- Products Grid --}}
                    <div
                        class="grid grid-cols-1 {{ $multiProducts->count() === 1 ? 'max-w-md mx-auto' : ($multiProducts->count() === 2 ? 'sm:grid-cols-2 max-w-3xl mx-auto' : 'sm:grid-cols-2 md:grid-cols-3') }} gap-6 md:gap-8">
                        @foreach($multiProducts as $p)
                            <x-landing.landing-product-card-1 :product="$p" :setup="$setup" :landing="$landing" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- ══ SECTION 2: GREEN TRUST BANNER ══ --}}
        <section class="w-full bg-[#00703c] text-white py-8 md:py-12 px-4 text-center shadow-inner">
            <div class="max-w-4xl mx-auto space-y-3">
                @if($desc)
                    <div
                        class="text-sm sm:text-base text-emerald-100 leading-relaxed font-normal {{ $isPreviewMode ? 'preview-badge-container' : '' }} [&_h1]:text-2xl [&_h1]:sm:text-3xl [&_h1]:md:text-4xl [&_h1]:font-black [&_h1]:tracking-wide [&_h1]:leading-snug [&_h1]:text-white [&_h1]:mb-3 [&_h2]:text-2xl [&_h2]:sm:text-3xl [&_h2]:md:text-4xl [&_h2]:font-black [&_h2]:tracking-wide [&_h2]:leading-snug [&_h2]:text-white [&_h2]:mb-3 [&_h3]:text-xl [&_h3]:sm:text-2xl [&_h3]:font-black [&_h3]:tracking-wide [&_h3]:text-white [&_h3]:mb-2">
                        @if($isPreviewMode)<span class="preview-badge" style="top:-5px; left:10px;">Details</span>@endif
                        {!! $desc !!}
                    </div>
                @endif
            </div>
        </section>

        {{-- ══ SECTION 3: BENEFITS SECTION (OUR BENEFITS) ══ --}}
        @php
            // Features data detection (feature1..6, features3..6, features2, features)
            $rawFeatures = [];

            // 1. Check if individual feature card objects exist (feature1..6 or features1..6)
            $tempKeys = [
                ['feature1', 'features1'],
                ['feature2', 'features2_single', 'features'],
                ['feature3', 'features3'],
                ['feature4', 'features4'],
                ['feature5', 'features5'],
                ['feature6', 'features6'],
            ];

            $individualFeatures = [];
            foreach ($tempKeys as $idx => $aliasGroup) {
                $val = null;
                foreach ($aliasGroup as $k) {
                    $item = data_get($landing->extras, $k);
                    if (!empty($item)) {
                        // If card component saved as array with index 0
                        $val = is_array($item) && isset($item[0]) && is_array($item[0]) ? $item[0] : $item;
                        if (!empty($val['title']) || !empty($val['description']) || !empty($val['desc']) || !empty($val['content']) || !empty($val['image'])) {
                            break;
                        }
                    }
                }
                if ($val && (!empty($val['title']) || !empty($val['description']) || !empty($val['desc']) || !empty($val['content']) || !empty($val['image']))) {
                    $individualFeatures[$idx] = $val;
                }
            }

            if (count($individualFeatures) > 0) {
                // If at least one individual feature card has custom text, fill with custom or fallback
                for ($i = 0; $i < 6; $i++) {
                    $rawFeatures[$i] = $individualFeatures[$i] ?? ($previewBenefits[$i] ?? []);
                }
            } elseif (!empty($landing->extras['features2']) && is_array($landing->extras['features2']) && count($landing->extras['features2']) > 1) {
                $rawFeatures = $landing->extras['features2'];
            } elseif (!empty($landing->extras['features']) && is_array($landing->extras['features']) && count($landing->extras['features']) > 1) {
                $rawFeatures = $landing->extras['features'];
            }

            $hasCustomFeatures = count($rawFeatures) > 0;
            $featureItems = $hasCustomFeatures ? $rawFeatures : ($isPreviewMode ? $previewBenefits : []);

            // Split into 2 columns (left and right)
            $leftFeatures = array_slice($featureItems, 0, ceil(count($featureItems) / 2));
            $rightFeatures = array_slice($featureItems, ceil(count($featureItems) / 2));
        @endphp

        @if($content1 || count($featureItems) > 0 || $isPreviewMode)
            <section class="w-full py-12 md:py-20 px-4 bg-[#f8faf8] border-b border-gray-100 overflow-hidden">
                <div class="max-w-6xl mx-auto">

                    {{-- Top Header: Benefits Badge + Content (Title & Subtitle) --}}
                    <div class="text-center max-w-3xl mx-auto mb-10 md:mb-14">

                        <div
                            data-field="benefits_badge"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-emerald-100/70 text-[#00703c] text-xs sm:text-sm font-semibold tracking-wide uppercase mb-3 relative cursor-pointer {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" data-field="benefits_badge" style="top: -12px; left: 0;">Benefits Badge</span>@endif
                            <span>✦</span>
                            <span>{{ $benefitsBadge }}</span>
                        </div>

                        <div class="relative {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" data-field="content" style="top: -12px; left: 0;">Content</span>@endif

                            @if($content1)
                                <div
                                    class="prose max-w-none text-[#1b3a27] [&_h1]:text-2xl [&_h1]:sm:text-3xl [&_h1]:md:text-4xl [&_h1]:font-black [&_h1]:text-[#1b3a27] [&_h1]:leading-snug [&_h1]:mb-2 [&_h2]:text-2xl [&_h2]:sm:text-3xl [&_h2]:md:text-4xl [&_h2]:font-black [&_h2]:text-[#1b3a27] [&_h2]:leading-snug [&_h2]:mb-2 [&_h3]:text-2xl [&_h3]:sm:text-3xl [&_h3]:md:text-4xl [&_h3]:font-black [&_h3]:text-[#1b3a27] [&_h3]:leading-snug [&_h3]:mb-2 [&_p]:text-sm sm:[&_p]:text-base [&_p]:text-gray-600 [&_p]:mt-1.5 [&_ul]:hidden">
                                    {!! $content1 !!}
                                </div>
                            @else
                                <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#1b3a27] leading-snug tracking-tight">
                                    {{ $benefitsTitle }}
                                </h2>
                                @if($benefitsSubtitle)
                                    <p class="text-xs sm:text-sm md:text-base text-gray-500 font-medium tracking-wide mt-2">
                                        {{ $benefitsSubtitle }}
                                    </p>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- 3-Column Layout: Left Features | Center Product Thumbnail | Right Features --}}
                    @if(count($featureItems) > 0)
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-6 items-center">

                            {{-- Left Features (Items) --}}
                            <div class="lg:col-span-4 space-y-6 sm:space-y-8 order-2 lg:order-1">
                                @foreach($leftFeatures as $idx => $feat)
                                    @php
                                        $fTitle = is_array($feat) ? ($feat['title'] ?? '') : $feat;
                                        $fDesc = is_array($feat) ? ($feat['content'] ?? ($feat['desc'] ?? ($feat['description'] ?? ''))) : '';
                                        $featNumber = $idx + 1;

                                        // Resolve image
                                        $fImg = is_array($feat) ? ($feat['image'] ?? null) : null;
                                        $fImgSrc = '';
                                        if (is_array($fImg)) {
                                            $fImgSrc = $fImg['previewUrl'] ?? $fImg['url'] ?? $fImg['path'] ?? '';
                                        } elseif (is_string($fImg)) {
                                            $fImgSrc = (str_starts_with($fImg, 'blob:') || str_starts_with($fImg, 'data:') || str_starts_with($fImg, 'http') || str_starts_with($fImg, '/'))
                                                ? $fImg
                                                : \Illuminate\Support\Facades\Storage::disk('r2')->url($fImg);
                                        }
                                    @endphp
                                    <div
                                        class="flex items-start gap-4 text-left group p-2 rounded-xl transition-all relative {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                        @if($isPreviewMode)<span class="preview-badge" style="top: -12px; left: 0;">Feature
                                        {{ $featNumber }}</span>@endif
                                        <div
                                            class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0 shadow-sm overflow-hidden p-2 transition-transform duration-300 group-hover:scale-105">
                                            @if(!empty($fImgSrc))
                                                <img src="{{ $fImgSrc }}" alt="{{ $fTitle }}" width="56" height="56"
                                                    class="w-full h-full object-contain">
                                            @else
                                                <i class="fa-solid fa-leaf text-[#00703c] text-lg"></i>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-base sm:text-lg font-bold text-[#1b3a27] leading-snug mb-1">
                                                {{ $fTitle }}
                                            </h3>
                                            @if($fDesc)
                                                <div class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                                                    {!! $fDesc !!}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Center: Product Thumbnail Image (Clean without background/border like reference) --}}
                            <div class="lg:col-span-4 flex justify-center items-center order-1 lg:order-2 py-4 lg:py-0">
                                <div
                                    class="relative w-64 sm:w-72 md:w-80 aspect-square flex items-center justify-center {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                    @if($isPreviewMode)<span class="preview-badge"
                                    style="top: 0; left: 10px;">Thumbnail</span>@endif

                                    @if(!empty($productImg))
                                        {{-- Soft background aura/glow --}}
                                        <div class="absolute inset-4 bg-emerald-200/40 rounded-full blur-2xl transform scale-90">
                                        </div>

                                        {{-- Clean Product Thumbnail without card/border/bg --}}
                                        <div
                                            class="relative w-full h-full flex items-center justify-center z-10 hover:scale-105 transition-transform duration-300">
                                            <img src="{{ $productImg }}" alt="Product Thumbnail" width="320" height="320"
                                                class="max-w-full max-h-full object-contain drop-shadow-xl rounded-xl">
                                        </div>
                                    @else
                                        {{-- Fallback placeholder icon box --}}
                                        <div
                                            class="w-52 h-52 flex flex-col items-center justify-center text-emerald-800 p-4 text-center">
                                            <i class="fa-solid fa-box-open text-5xl mb-2 text-emerald-600"></i>
                                            <span class="text-sm font-semibold">Product Thumbnail</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Right Features (Items) --}}
                            <div class="lg:col-span-4 space-y-6 sm:space-y-8 order-3">
                                @foreach($rightFeatures as $idx => $feat)
                                    @php
                                        $fTitle = is_array($feat) ? ($feat['title'] ?? '') : $feat;
                                        $fDesc = is_array($feat) ? ($feat['content'] ?? ($feat['desc'] ?? ($feat['description'] ?? ''))) : '';
                                        $featNumber = count($leftFeatures) + $idx + 1;

                                        // Resolve image
                                        $fImg = is_array($feat) ? ($feat['image'] ?? null) : null;
                                        $fImgSrc = '';
                                        if (is_array($fImg)) {
                                            $fImgSrc = $fImg['previewUrl'] ?? $fImg['url'] ?? $fImg['path'] ?? '';
                                        } elseif (is_string($fImg)) {
                                            $fImgSrc = (str_starts_with($fImg, 'blob:') || str_starts_with($fImg, 'data:') || str_starts_with($fImg, 'http') || str_starts_with($fImg, '/'))
                                                ? $fImg
                                                : \Illuminate\Support\Facades\Storage::disk('r2')->url($fImg);
                                        }
                                    @endphp
                                    <div
                                        class="flex items-start gap-4 text-left group p-2 rounded-xl transition-all relative {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                        @if($isPreviewMode)<span class="preview-badge" style="top: -12px; left: 0;">Feature
                                        {{ $featNumber }}</span>@endif
                                        <div
                                            class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0 shadow-sm overflow-hidden p-2 transition-transform duration-300 group-hover:scale-105">
                                            @if(!empty($fImgSrc))
                                                <img src="{{ $fImgSrc }}" alt="{{ $fTitle }}" width="56" height="56"
                                                    class="w-full h-full object-contain">
                                            @else
                                                <i class="fa-solid fa-leaf text-[#00703c] text-lg"></i>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-base sm:text-lg font-bold text-[#1b3a27] leading-snug mb-1">
                                                {{ $fTitle }}
                                            </h3>
                                            @if($fDesc)
                                                <div class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                                                    {!! $fDesc !!}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    @endif

                </div>
            </section>
        @endif

        {{-- ══ SECTION 4: SPECIAL DISCOUNT & OFFER BOX ══ --}}
        @if($regularPrice || $discountPrice || $isPreviewMode)
            <section class="w-full bg-[#00703c] py-10 px-4 text-center relative">
                <div class="max-w-4xl mx-auto mb-6 md:mb-4">
                    <h2
                        class="text-xl sm:text-2xl md:text-4xl font-black text-white tracking-wide leading-snug {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge" style="top: -15px; left: 10px;">Offer
                        Title</span>@endif
                        {{ $offerTitle }}
                    </h2>
                </div>

                {{-- Offer Card: Relative in document flow on ALL devices with -mb overlap so it perfectly overlaps the green bottom edge without breaking into next sections --}}
                {{-- <div class="max-w-4xl mx-auto relative z-20 -mb-28 sm:-mb-32 md:-mb-36">
                    <div class="bg-white rounded-xl p-5 sm:p-7 md:p-9 shadow-xl border-2 border-[#b5e0c8] w-full mx-auto">
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-5 sm:gap-8 md:gap-12">

                            <div class="relative flex-shrink-0">
                                <div class="absolute -top-3 -right-2 text-[#f59e0b] text-2xl font-bold">
                                    ✦
                                </div>

                                <div class="w-20 h-24 sm:w-24 sm:h-28 md:w-28 md:h-32 flex items-center justify-center">
                                    <svg viewBox="0 0 100 120" class="w-full h-full drop-shadow">
                                        <path d="M34 70 L24 116 L44 106 L47 70 Z" fill="#e05333" />
                                        <path d="M66 70 L76 116 L56 106 L53 70 Z" fill="#c0391b" />
                                        <path
                                            d="M50 4 L58 13 L70 10 L74 22 L85 25 L84 37 L93 44 L88 55 L93 66 L83 72 L83 84 L71 86 L66 97 L54 93 L46 99 L40 90 L28 91 L26 79 L15 76 L18 64 L9 57 L16 46 L11 35 L21 30 L23 18 L35 18 L41 8 Z"
                                            fill="#fbb024" />
                                        <circle cx="50" cy="52" r="26" fill="#fcd34d" />
                                        <circle cx="50" cy="52" r="23" fill="none" stroke="#f59e0b" stroke-width="2"
                                            stroke-dasharray="3,2" />
                                        <text x="50" y="63" font-size="30" font-family="'Arial', sans-serif"
                                            font-weight="900" fill="#ffffff" text-anchor="middle">%</text>
                                    </svg>
                                </div>
                            </div>

                            <div class="space-y-2 text-center sm:text-left flex-1 min-w-0 w-full">
                                @if($regularPrice)
                                    <div
                                        class="text-xs sm:text-sm md:text-[15px] text-[#1b4332] font-semibold tracking-normal pt-1 break-words leading-relaxed {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                        @if($isPreviewMode)<span class="preview-badge" style="top: -18px; left: 0;">Regular
                                        Price</span>@endif
                                        <span>{{ $regularPrice }}</span>
                                    </div>
                                @endif

                                @if($discountPrice)
                                    <div
                                        class="text-xl sm:text-2xl md:text-3xl font-black text-[#006837] tracking-tight leading-snug break-words {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                        @if($isPreviewMode)<span class="preview-badge" style="top: -10px; left: 0;">Discount
                                        Price</span>@endif
                                        <span>{{ $discountPrice }}</span>
                                    </div>
                                @else
                                    <div
                                        class="text-xl sm:text-2xl md:text-3xl font-black text-[#006837] tracking-tight break-words">
                                        **অফার মূল্য:
                                        {{ number_format($product->sale_price ?? $product->regular_price, 2) }}{{ $setup->currency }}**
                                    </div>
                                @endif

                                <div class="pt-2">
                                    @if($isEntirelyOutOfStock)
                                        <button type="button" disabled
                                            class="inline-flex items-center justify-center gap-1.5 px-6 sm:px-8 py-2.5 bg-gray-400 cursor-not-allowed text-white font-bold rounded text-xs sm:text-sm shadow-sm opacity-80">
                                            <i class="fa-solid fa-ban text-xs"></i>
                                            <span>স্টক আউট</span>
                                        </button>
                                    @else
                                        <a href="#landing-products-section"
                                            class="inline-flex items-center justify-center gap-1.5 px-6 sm:px-8 py-2.5 bg-[#0e7747] hover:bg-[#095934] text-white font-bold rounded text-xs sm:text-sm shadow-sm hover:shadow transition-all {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                            @if($isPreviewMode)<span class="preview-badge" style="top: -12px;">Button
                                            Text</span>@endif
                                            <i class="fa-solid fa-cart-shopping text-xs"></i>
                                            <span>{{ $btnText }}</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div> --}}
            </section>
        @endif

        {{-- ══ SECTION 5: WHY CHOOSE US (আমাদের থেকে কেন কিনবেন?) ══ --}}
        @if($content2)
            <section class="w-full pt-10 sm:pt-14 md:pt-16 pb-12 md:pb-20 px-4 bg-pattern border-b border-gray-100">
                <div class="max-w-4xl mx-auto space-y-8">
                    <div
                        class="bg-white/90 backdrop-blur-sm p-6 sm:p-10 rounded-xl shadow-md border border-emerald-100 text-left prose prose-emerald max-w-none text-gray-700 leading-relaxed {{ $isPreviewMode ? 'preview-badge-container [&_h1]:text-2xl [&_h1]:sm:text-3xl [&_h1]:md:text-4xl [&_h1]:font-black [&_h1]:text-[#00703c] [&_h1]:text-center [&_h1]:mb-6 [&_h2]:text-2xl [&_h2]:sm:text-3xl [&_h2]:md:text-4xl [&_h2]:font-black [&_h2]:text-[#00703c] [&_h2]:text-center [&_h2]:mb-6 [&_h3]:text-2xl [&_h3]:sm:text-3xl [&_h3]:md:text-4xl [&_h3]:font-black [&_h3]:text-[#00703c] [&_h3]:text-center [&_h3]:mb-6 [&_img]:max-w-full [&_img]:h-auto [&_img]:rounded-2xl [&_img]:my-5 [&_img]:mx-auto [&_img]:shadow-md [&_ul]:space-y-2 [&_ul]:pl-5 [&_li]:text-sm sm:[&_li]:text-base [&_li]:font-normal' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge">Content 2</span>@endif
                        {!! $content2 !!}
                    </div>
                </div>
            </section>
        @endif

        {{-- ══ SECTION 6: CUSTOMER REVIEWS (আমাদের সন্তুষ্ট ক্রেতাদের মতামত) ══ --}}
        @php
            $displayReviews = (!empty($reviewImgs) && count($reviewImgs) > 0) ? $reviewImgs : ($isPreviewMode ? $previewDefaultReviewImgs : []);
        @endphp
        @if(!empty($displayReviews) && count($displayReviews) > 0)
        <section class="w-full bg-[#00703c] py-12 md:py-20 px-4 text-white relative {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
            @if($isPreviewMode)<span class="preview-badge" style="top: 0; left: 0; z-index: 50;">Review</span>@endif
            <div class="max-w-6xl mx-auto space-y-8">
                <div class="text-center">
                    <h3 class="inline-block text-2xl sm:text-3xl md:text-4xl font-black text-white relative {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge" data-field="review_title" style="top: -16px; left: 50%; transform: translateX(-50%);">Review Title</span>@endif
                        {{ !empty($landing->extras['review_title']) ? $landing->extras['review_title'] : 'আমাদের সন্তুষ্ট ক্রেতাদের মতামত' }}
                    </h3>
                </div>

                {{-- Authentic Testimonial Slider Matching Reference Image --}}
                <div class="relative px-2 sm:px-6">
                    <div class="swiper rv-swiper landing6-swiper max-w-6xl mx-auto">
                        <div class="swiper-wrapper py-6">
                            @foreach ($displayReviews as $index => $imgItem)
                                @php
                                    $imgSrc = is_array($imgItem) ? ($imgItem['previewUrl'] ?? '') : (str_starts_with($imgItem, 'blob:') || str_starts_with($imgItem, 'data:') || str_starts_with($imgItem, 'http') || str_starts_with($imgItem, '/') ? $imgItem : \Illuminate\Support\Facades\Storage::disk('r2')->url($imgItem));
                                    $cardData = $previewReviewCards[$index % count($previewReviewCards)];
                                @endphp
                                <div class="swiper-slide !flex !justify-center">
                                    {{-- Outer Green Border Card with symmetrical top and bottom spacing --}}
                                    <div
                                        class="w-full max-w-[350px] rounded-2xl border border-white/40 bg-[#0e7845]/80 backdrop-blur-sm p-4 sm:p-5 flex flex-col shadow-xl relative text-left">
                                        {{-- Inner White Box: Displaying Customer Review Image (From img_paths like
                                        landing1) --}}
                                        <div
                                            class="bg-white rounded-xl p-2.5 sm:p-3 shadow-sm relative z-10 h-[210px] sm:h-[230px] flex items-center justify-center overflow-hidden border border-gray-100">
                                            <img src="{{ $imgSrc }}" alt="Customer Review" width="320" height="230"
                                                class="w-full h-full object-contain rounded-lg">
                                        </div>

                                        {{-- Bottom Section: Equalized Top and Bottom Spacing --}}
                                        <div class="relative mt-4 flex items-center justify-between min-h-[64px]">
                                            {{-- Left Side: Location & Store Link --}}
                                            <div class="space-y-1 z-20 max-w-[60%] text-left">
                                                <p class="text-xs sm:text-sm text-white font-medium leading-snug">
                                                    {{ $setup->store_address ?? '100% Organic & Pure' }}
                                                </p>
                                                <p
                                                    class="text-[11px] sm:text-xs text-white/95 font-bold tracking-wider font-mono">
                                                    {{ strtolower(preg_replace('/[^a-zA-Z0-9\.]/', '', $setup->shop_name ?? 'falao.shop')) }}
                                                </p>
                                            </div>

                                            {{-- Right Side: Floating Product Polaroid Photo - Locked within card bottom
                                            boundary --}}
                                            <div
                                                class="absolute bottom-0 right-0 w-20 h-20 sm:w-22 sm:h-22 p-1.5 bg-white rounded-md shadow-2xl border border-gray-200 rotate-6 z-30 hover:rotate-0 transition-transform flex items-center justify-center -translate-y-2">
                                                <img src="{{ $productImg }}" alt="Product" width="88" height="88"
                                                    class="w-full h-full object-cover rounded-xs">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        {{-- Symmetrical White Clickable Pagination Dots --}}
                        <div class="swiper-pagination landing6-pagination mt-6 flex justify-center items-center gap-2">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        {{-- ══ SECTION 7: DIRECT CALL / WHATSAPP / MESSENGER BANNER ══ --}}
        @php
            // 1. Resolve Phone: ONLY from landing page phone_number
            $rawPhone = $landing->phone_number ?: ($isPreviewMode ? $exactImgPhone : null);

            // 2. Resolve WhatsApp Link: ONLY from store social settings
            $whatsappSocial = isset($socialLinks) ? $socialLinks->first(function ($item) {
                $name = strtolower($item->icon_name ?? '');
                $link = strtolower($item->link ?? '');
                return str_contains($name, 'whatsapp') || str_contains($link, 'wa.me') || str_contains($link, 'whatsapp');
            }) : null;

            $whatsappUrl = ($whatsappSocial && !empty($whatsappSocial->link))
                ? $whatsappSocial->link
                : ($isPreviewMode ? 'https://wa.me/' : null);

            // 3. Resolve Messenger Link: ONLY from store social settings or setup->facebook
            $messengerSocial = isset($socialLinks) ? $socialLinks->first(function ($item) {
                $name = strtolower($item->icon_name ?? '');
                $link = strtolower($item->link ?? '');
                return str_contains($name, 'messenger') || str_contains($name, 'facebook') || str_contains($link, 'm.me') || str_contains($link, 'facebook.com');
            }) : null;

            $fbLink = $messengerSocial ? $messengerSocial->link : ($setup->facebook ?? '');

            if (!empty($fbLink)) {
                if (str_contains($fbLink, 'm.me')) {
                    $messengerUrl = $fbLink;
                } else {
                    $cleanFbUser = trim(str_replace(['https://www.facebook.com/', 'http://www.facebook.com/', 'https://facebook.com/', 'http://facebook.com/', 'facebook.com/'], '', $fbLink), '/');
                    // Remove any query params
                    $cleanFbUser = explode('?', $cleanFbUser)[0];
                    $messengerUrl = !empty($cleanFbUser) ? 'https://m.me/' . $cleanFbUser : 'https://m.me/';
                }
            } else {
                $messengerUrl = $isPreviewMode ? 'https://m.me/' : null;
            }
        @endphp

        @if($rawPhone || $isPreviewMode || !empty($whatsappUrl) || !empty($messengerUrl))
            <section class="w-full py-10 md:py-14 px-4 bg-white text-center border-b border-gray-100">
                <div class="max-w-3xl mx-auto space-y-3 sm:space-y-4">

                    @php
                        $supportContent = data_get($landing->extras, 'content3') ?: null;
                    @endphp
                    <div class="relative {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge" style="top: -15px; left: 0; z-index: 50;">Content 3</span>@endif
                        @if(!empty($supportContent))
                            <div class="prose max-w-none text-[#00703c] leading-relaxed {{ $isPreviewMode ? '[&_h2]:text-xl [&_h2]:sm:text-2xl [&_h2]:md:text-3xl [&_h2]:font-black [&_h2]:text-[#00703c] [&_h3]:text-xl [&_h3]:sm:text-2xl [&_h3]:md:text-3xl [&_h3]:font-black [&_h3]:text-[#00703c] [&_p]:text-gray-600 [&_p]:text-xs [&_p]:sm:text-sm' : '' }}">
                                {!! $supportContent !!}
                            </div>
                        @else
                            {{-- Green Top Subtitle --}}
                            <p class="text-sm sm:text-base md:text-lg font-bold text-[#00703c] tracking-wide mb-2">
                                অর্ডার করতে কোনো সমস্যা?
                            </p>

                            {{-- Main Headline --}}
                            <h3 class="text-xl sm:text-2xl md:text-3xl font-black text-[#00703c] leading-snug mb-3">
                                কল করুন বা WhatsApp / Messenger-এ মেসেজ দিন
                            </h3>

                            {{-- Helper Notice Text with Phone Icon --}}
                            <p class="text-xs sm:text-sm text-gray-600 font-medium flex items-center justify-center gap-2 pt-1">
                                <i class="fa-solid fa-phone text-gray-700 text-xs"></i>
                                <span>সরাসরি কল করুন, অথবা WhatsApp / Messenger-এ যেকোনো সময় মেসেজ করুন – আমরা দ্রুত রেসপন্স
                                    করি।</span>
                            </p>
                        @endif
                    </div>

                    {{-- Action Buttons Grid: WhatsApp, Messenger, Phone (Shows only if available) --}}
                    @if(!empty($whatsappUrl) || !empty($messengerUrl) || !empty($rawPhone))
                    <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 pt-3">
                        {{-- WhatsApp Button --}}
                        @if(!empty($whatsappUrl))
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
                            data-field="social_settings"
                            class="relative inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-[#075E54] hover:bg-[#05463f] text-white font-bold rounded-lg text-xs sm:text-sm md:text-base shadow-sm hover:shadow transition-all {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" data-field="social_settings" style="top: -12px; background-color: #05463f;">Social: WhatsApp</span>@endif
                            <i class="fa-brands fa-whatsapp text-base sm:text-lg"></i>
                            <span>WhatsApp-এ মেসেজ করুন</span>
                        </a>
                        @endif

                        {{-- Messenger Button --}}
                        @if(!empty($messengerUrl))
                        <a href="{{ $messengerUrl }}" target="_blank" rel="noopener noreferrer"
                            data-field="social_settings"
                            class="relative inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-[#0066d6] hover:bg-[#0052ad] text-white font-bold rounded-lg text-xs sm:text-sm md:text-base shadow-sm hover:shadow transition-all {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" data-field="social_settings" style="top: -12px; background-color: #0052ad;">Social: Messenger</span>@endif
                            <i class="fa-brands fa-facebook-messenger text-base sm:text-lg"></i>
                            <span>Messenger-এ মেসেজ দিন</span>
                        </a>
                        @endif

                        {{-- Phone Call Button with Preview Badge (Only if phone number exists or preview) --}}
                        @if(!empty($rawPhone))
                        <a href="tel:{{ $rawPhone }}"
                            class="relative inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-[#0e2a47] hover:bg-[#091b2e] text-white font-bold rounded-lg text-xs sm:text-sm md:text-base shadow-sm hover:shadow transition-all {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" style="top: -12px;">Phone Number</span>@endif
                            <i class="fa-solid fa-phone text-xs sm:text-sm"></i>
                            <span>Phone-এ কল করুন</span>
                        </a>
                        @endif
                    </div>
                    @endif

                </div>
            </section>
        @endif

        {{-- ══ SECTION 8: ORDER FORM ══ --}}
        <section id="order" class="w-full py-12 md:py-16 px-4 bg-[#f8faff]">
            <div class="max-w-6xl mx-auto">
                <x-landing.order-form :landing="$landing" :products="$multiProducts" :isPreview="$isPreviewMode" />
            </div>
        </section>

        {{-- ══ SECTION 9: FOOTER ══ --}}
        <footer
            class="w-full bg-gray-50 py-8 px-4 text-center border-t border-gray-100 text-xs sm:text-sm text-gray-500 space-y-2">
            @if(!empty($setup->store_address))
                <p class="flex items-center justify-center gap-1.5 font-medium text-gray-600">
                    <i class="fa-solid fa-location-dot text-[#00703c]"></i>
                    <span>{{ $setup->store_address }}</span>
                </p>
            @endif

            <p>
                © {{ date('Y') }} {{ $setup->shop_name ?? 'Your Shop Name' }}. All rights reserved.
            </p>
        </footer>

    </main>

    {{-- Product Card Interactivity Scripts (Variant Selection & Add to Order) --}}
    @php
        $formLang = data_get($landing->extras ?? null, 'order_form_lang') ?: ($landing->order_form_lang ?? 'bn');
        $isCardEn = ($formLang === 'en');
        $jsCardTrans = [
            'stock_out' => $isCardEn ? 'Out of Stock' : 'স্টক আউট',
            'stock_end' => $isCardEn ? 'Out of Stock' : 'স্টক শেষ',
            'add_to_cart' => $isCardEn ? 'Add to Cart' : 'কার্টে যোগ করুন',
            'order_now' => $isCardEn ? 'Order Now' : 'অর্ডার করুন',
            'added' => $isCardEn ? 'Added' : 'যোগ হয়েছে',
            'not_in_stock' => $isCardEn ? 'Sorry! The selected product is currently out of stock.' : 'দুঃখিত! নির্বাচিত পণ্যটি বর্তমানে স্টকে নেই।',
            'option_not_in_stock' => $isCardEn ? 'Sorry! This option is currently out of stock.' : 'দুঃখিত! এই অপশনটি বর্তমানে স্টকে নেই।',
        ];
    @endphp
    <script>
        const cardTrans = @json($jsCardTrans);

        // Card Variation Change Handler
        function onCardVariationChange(selectEl, productId) {
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            if (!selectedOpt) return;

            const card = selectEl.closest('.product-card');
            if (card) {
                const imgEl = card.querySelector('.card-img');
                const salePriceEl = card.querySelector('.card-sale-price');
                const regPriceEl = card.querySelector('.card-reg-price');
                const addBtn = card.querySelector('.card-add-btn');

                if (imgEl && selectedOpt.dataset.img) {
                    imgEl.src = selectedOpt.dataset.img;
                }
                if (salePriceEl && selectedOpt.dataset.price) {
                    const priceFormatted = parseFloat(selectedOpt.dataset.price).toLocaleString(undefined, { minimumFractionDigits: 2 });
                    salePriceEl.innerText = priceFormatted + "{{ $setup->currency }}";
                }
                if (regPriceEl && selectedOpt.dataset.regular) {
                    const regFormatted = parseFloat(selectedOpt.dataset.regular).toLocaleString(undefined, { minimumFractionDigits: 2 });
                    regPriceEl.innerText = regFormatted + "{{ $setup->currency }}";
                }

                // Update discount badge on variation change
                const discountBadgeEl = card.querySelector('.card-discount-badge');
                if (discountBadgeEl) {
                    const dLabel = selectedOpt.dataset.discountLabel || '';
                    if (dLabel) {
                        discountBadgeEl.innerText = dLabel;
                        discountBadgeEl.classList.remove('hidden');
                    } else {
                        discountBadgeEl.classList.add('hidden');
                    }
                }

                // Check out of stock status of selected variant
                const isOutOfStock = selectedOpt.dataset.outOfStock === '1';
                const orderBtn = card.querySelector('.card-order-btn');

                if (isOutOfStock) {
                    if (addBtn) {
                        addBtn.disabled = true;
                        addBtn.className = "card-add-btn w-full inline-flex items-center justify-center gap-1 py-2.5 px-2 bg-gray-300 cursor-not-allowed opacity-75 text-gray-600 font-bold text-xs rounded-lg shadow-sm";
                        addBtn.innerHTML = '<i class="fa-solid fa-ban text-xs"></i> <span>' + cardTrans.stock_out + '</span>';
                    }
                    if (orderBtn) {
                        orderBtn.disabled = true;
                        orderBtn.className = "card-order-btn w-full inline-flex items-center justify-center gap-1 py-2.5 px-2 bg-gray-400 cursor-not-allowed opacity-75 text-white font-bold text-xs rounded-lg shadow-sm";
                        orderBtn.innerHTML = '<span>' + cardTrans.stock_end + '</span>';
                    }
                } else {
                    if (addBtn) {
                        addBtn.disabled = false;
                        addBtn.className = "card-add-btn w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-2 bg-green-50 hover:bg-green-100 text-[#00703c] border-2 border-[#00703c]/40 hover:border-[#00703c] font-bold text-xs rounded-lg transition-all active:scale-[0.98] cursor-pointer shadow-sm";
                        addBtn.innerHTML = '<i class="fa-solid fa-cart-plus text-xs text-[#00703c]"></i> <span class="btn-label">' + cardTrans.add_to_cart + '</span>';
                    }
                    if (orderBtn) {
                        orderBtn.disabled = false;
                        orderBtn.className = "card-order-btn w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-2 bg-[#00703c] hover:bg-[#00582f] text-white font-bold text-xs rounded-lg shadow-md hover:shadow-lg transition-all active:scale-[0.98] cursor-pointer";
                        orderBtn.innerHTML = '<span class="btn-label">' + cardTrans.order_now + '</span> <i class="fa-solid fa-arrow-down text-xs"></i>';
                    }
                }
            }
        }

        // Add Product To Landing Order Function (supports scroll toggle)
        function addProductToLandingOrder(productId, shouldScroll = false) {
            const card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
            let targetCheckbox = null;
            let targetVarId = null;

            if (card) {
                const selectEl = card.querySelector('.card-variant-select');
                if (selectEl) {
                    const selectedOpt = selectEl.options[selectEl.selectedIndex];
                    if (selectedOpt && selectedOpt.dataset.outOfStock === '1') {
                        alert(cardTrans.option_not_in_stock);
                        return;
                    }
                    targetVarId = selectEl.value;
                    targetCheckbox = document.querySelector(`.variant-checkbox[value="${targetVarId}"]`);
                } else {
                    targetCheckbox = document.querySelector(`input[name="product_ids[]"][value="${productId}"]`);
                    if (targetCheckbox && targetCheckbox.dataset.outOfStock === '1') {
                        alert(cardTrans.not_in_stock);
                        return;
                    }
                }

                // Button click feedback on Add to Cart button if staying
                if (!shouldScroll) {
                    const btn = card.querySelector('.card-add-btn');
                    if (btn && !btn.disabled) {
                        const origContent = btn.innerHTML;
                        btn.classList.remove('bg-green-50', 'text-[#00703c]');
                        btn.classList.add('bg-green-600', 'text-white');
                        btn.innerHTML = '<i class="fa-solid fa-check text-xs"></i> <span>' + cardTrans.added + '</span>';
                        setTimeout(() => {
                            btn.classList.remove('bg-green-600', 'text-white');
                            btn.classList.add('bg-green-50', 'text-[#00703c]');
                            btn.innerHTML = origContent;
                        }, 1500);
                    }
                }
            }

            if (targetCheckbox) {
                if (targetCheckbox.dataset.outOfStock === '1') {
                    alert(cardTrans.not_in_stock);
                    return;
                }

                // Show product wrapper in order form if hidden
                const productWrapper = targetCheckbox.closest('[data-order-product-id]');
                if (productWrapper) {
                    productWrapper.classList.remove('hidden');
                }

                targetCheckbox.checked = true;
                targetCheckbox.dispatchEvent(new Event('change'));

                if (!shouldScroll && window.toastr) {
                    toastr.options = {
                        positionClass: 'toast-bottom-right',
                        timeOut: 2500,
                        closeButton: true,
                        progressBar: true
                    };
                    toastr.success('পণ্যটি সফলভাবে অর্ডারে যোগ করা হয়েছে!');
                }
            }

            if (shouldScroll) {
                const orderEl = document.getElementById('order');
                if (orderEl) {
                    orderEl.scrollIntoView({ behavior: 'smooth' });
                }
            }
        }
    </script>

    {{-- Swiper.js Initialization (Using Vite Bundled Swiper) --}}
    <script>
        (function () {
            function startSwiper() {
                const runInit = function (SwiperClass) {
                    if (!SwiperClass) return;

                    // Section 3: Benefit Image Slider (if present)
                    const benefitEl = document.querySelector('.benefit-swiper');
                    if (benefitEl && !benefitEl.swiper) {
                        new SwiperClass(benefitEl, {
                            slidesPerView: 1,
                            spaceBetween: 0,
                            loop: true,
                            autoplay: {
                                delay: 3000,
                                disableOnInteraction: false,
                            },
                            pagination: {
                                el: '.benefit-pagination',
                                clickable: true,
                            },
                        });
                    }

                    // Section 6: Review Images Slider
                    const reviewEl = document.querySelector('.landing6-swiper');
                    if (reviewEl && !reviewEl.swiper) {
                        const totalSlides = reviewEl.querySelectorAll('.swiper-slide').length;
                        new SwiperClass(reviewEl, {
                            slidesPerView: 1,
                            spaceBetween: 20,
                            loop: totalSlides >= 2,
                            grabCursor: true,
                            observer: true,
                            observeParents: true,
                            autoplay: {
                                delay: 4000,
                                disableOnInteraction: false,
                                pauseOnMouseEnter: true,
                            },
                            pagination: {
                                el: '.landing6-pagination',
                                clickable: true,
                            },
                            breakpoints: {
                                640: {
                                    slidesPerView: Math.min(2, totalSlides),
                                    spaceBetween: 20,
                                },
                                1024: {
                                    slidesPerView: Math.min(3, totalSlides),
                                    spaceBetween: 24,
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

    {{-- Interactive Live Preview Messenger (Parent Admin Support) --}}
    @if(!empty($isPreviewMode) || (isset($isPreview) && $isPreview))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.addEventListener('click', function (e) {
                    const badge = e.target.closest('.preview-badge');
                    const container = e.target.closest('.preview-badge-container');
                    const targetEl = badge || container;
                    if (!targetEl) return;

                    const badgeEl = badge || targetEl.querySelector('.preview-badge');
                    e.preventDefault();

                    let field = targetEl.getAttribute('data-field') || (badgeEl ? badgeEl.getAttribute('data-field') : null);

                    if (!field && badgeEl) {
                        const text = badgeEl.textContent.toLowerCase().replace(/\s+/g, ' ').trim();

                    if (text.includes('regular price')) {
                        field = 'regular_price';
                    } else if (text.includes('discount price')) {
                        field = 'discount_price';
                    } else if (text.includes('offer title')) {
                        field = 'offer_title';
                    } else if (text === 'title') {
                        field = 'title';
                    } else if (text.includes('short description')) {
                        field = 'short_description';
                    } else if (text.includes('thumbnail')) {
                        field = 'thumbnail';
                    } else if (text.includes('review title') || text.includes('review_title')) {
                        field = 'review_title';
                    } else if (text.includes('review')) {
                        field = 'img_paths';
                    } else if (text.includes('details') || text.includes('description')) {
                        field = 'description';
                    } else if (text.includes('phone')) {
                        field = 'phone_number';
                    } else if (text.includes('features 6') || text.includes('feature 6')) {
                        field = 'feature6';
                    } else if (text.includes('features 5') || text.includes('feature 5')) {
                        field = 'feature5';
                    } else if (text.includes('features 4') || text.includes('feature 4')) {
                        field = 'feature4';
                    } else if (text.includes('features 3') || text.includes('feature 3')) {
                        field = 'feature3';
                    } else if (text.includes('features 2') || text.includes('feature 2')) {
                        field = 'feature2';
                    } else if (text.includes('features 1') || text.includes('feature 1')) {
                        field = 'feature1';
                    } else if (text.includes('features') || text.includes('feature')) {
                        field = 'feature1';
                    } else if (text.includes('benefits badge')) {
                        field = 'benefits_badge';
                    } else if (text.includes('button text')) {
                        field = 'button_text';
                    } else if (text.includes('content 3') || text.includes('content3')) {
                        field = 'content3';
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
