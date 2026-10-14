<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $landing->title ?? ($product->title ?? 'Landing Page') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ $setup->favicon_url }}">
    @include('components.fontawesome')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            width: 8px;
            height: 8px;
            background: #ffffff;
            opacity: 0.4;
        }

        .swiper-pagination-bullet-active {
            opacity: 1;
            width: 24px;
            border-radius: 9999px;
            background: #ffffff;
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
        
        // Exact Bangla texts matching reference image
        $exactImgTitle = 'খাঁটি দেশি ঘি – পুষ্টি ও স্বাদের পরিপূর্ণতা!';
        $exactImgButtonText = 'অর্ডার করতে চাই';
        $exactImgPhone = '017XXXXXXXX';
        $exactImgShortDesc = '<p>ঘাসখাওয়া, স্বাস্থ্যসম্মত ও নন-ফ্রিজ প্রিকল এবং হাতে বানানো ১০০% খাঁটি দেশি গাভীর ঘি সারা বাংলাদেশ হোম ডেলিভারি।</p>';
        $exactImgTrustDesc = '<p>আমাদের ঘি খাঁটি দুধের সর থেকে তৈরি, যা কোনো কেমিক্যাল বা প্রিজারভেটিভ ছাড়াই প্রস্তুত। এটি বাচ্চা, বয়স্ক, শুঁটকি কিংবা প্রাত্যহিক সাথে ব্যবহার করা স্বাস্থ্য সচেতন পরিবারের সব সদস্যের জন্য পিওর ও পুষ্টিকর এই ঘি আপনার প্রতিদিনের খাবারের আসল স্বাদের নতুন মাত্রা!</p>';
        
        $exactImgContent1 = '<ul>
            <li>রোগ প্রতিরোধ ক্ষমতা বৃদ্ধি করে</li>
            <li>ত্বক ও চুল সুন্দর রাখে</li>
            <li>শিশুর বৃদ্ধি ও মস্তিষ্কের গঠনে সহায়তা</li>
            <li>শরীরে শক্তি বাড়ায়</li>
            <li>স্মৃতিশক্তি ও মানসিক একাগ্রতা উন্নত করে</li>
            <li>হাড় ও পেশির শক্তি বজায় রাখতে সাহায্য করে</li>
            <li>হজম শক্তি বৃদ্ধি ও পেটের সমস্যা দূর করতে সাহায্য করে</li>
            <li>ত্বকের লাবণ্য ফিরিয়ে আনে</li>
        </ul>';

        $exactImgContent2 = '<ul>
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

        // Price resolving
        $regularPrice = ($isPreviewMode && (empty($landing->regular_price) || str_contains($landing->regular_price, 'সজিনা'))) ? $exactImgRegularPrice : ($landing->regular_price ?: $exactImgRegularPrice);
        $discountPrice = ($isPreviewMode && (empty($landing->discount_price) || str_contains($landing->discount_price, 'সজিনা'))) ? $exactImgDiscountPrice : ($landing->discount_price ?: $exactImgDiscountPrice);

        // Trust description resolving
        $desc = ($isPreviewMode && (empty($landing->description) || str_contains($landing->description, 'Diaper') || str_contains($landing->description, 'About This Product'))) ? $exactImgTrustDesc : ($landing->description ?: $exactImgTrustDesc);
        
        // Content 1 & 2 resolving
        $content1 = ($isPreviewMode && (empty($landing->extras['content']) || str_contains($landing->extras['content'], 'Product Overview'))) ? $exactImgContent1 : (!empty($landing->extras['content']) ? $landing->extras['content'] : $exactImgContent1);
        $content2 = ($isPreviewMode && (empty($landing->extras['content2']) || str_contains($landing->extras['content2'], 'Product Overview'))) ? $exactImgContent2 : (!empty($landing->extras['content2']) ? $landing->extras['content2'] : $exactImgContent2);

        // Reviews sample data matching image style (review cards with rating, quote, and product pack thumbnail)
        $previewReviewCards = [
            [
                'name' => 'Khaled Masud',
                'role' => 'Web Designer',
                'comment' => 'আলহামদুলিল্লাহ ঘির কোয়ালিটি অনেক ভালো ছিল। ঘ্রাণ ও স্বাদ দুটোই নিখুঁত এবং একদম খাঁটি। সময়মতো ডেলিভারি পেয়েছি। যেকোনো রান্নায় বা ভাতে খেলে চমৎকার টেস্ট আসে!',
                'rating' => 5,
                'img' => $productImg
            ],
            [
                'name' => 'Rezaul Karim',
                'role' => 'Customer',
                'comment' => 'আগে অনেক জায়গা থেকে ঘি কিনে প্রতারিত হয়েছি, কিন্তু আপনাদের ঘি সত্যি অসাধারণ ও খাঁটি। পরিবারে সবাই পছন্দ করেছে। ধন্যবাদ সুন্দর সার্ভিসের জন্য!',
                'rating' => 5,
                'img' => $productImg
            ],
            [
                'name' => 'Tanvir Ahmed',
                'role' => 'Food Enthusiast',
                'comment' => 'প্রোডাক্ট হাতে পেয়ে চেক করে পেমেন্ট দিয়েছি। প্যাকেজিং খুবই সিকিউর ছিল এবং ঘির সুবাস এক কথায় চমৎকার! বারবার অর্ডার করব ইনশাআল্লাহ।',
                'rating' => 5,
                'img' => $productImg
            ]
        ];

        $reviewImgs = (!empty($landing->img_paths) && count($landing->img_paths) > 0)
            ? $landing->img_paths
            : (!empty($landing->extras['img_paths']) ? $landing->extras['img_paths'] : []);
    @endphp

    <main class="w-full">

        {{-- ══ SECTION 1: HEADER & HERO ══ --}}
        <header class="w-full bg-pattern py-8 md:py-16 px-4 border-b border-emerald-900/10">
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-8 md:gap-12 items-center">
                
                {{-- Left Text Box --}}
                <div class="md:col-span-7 space-y-5 text-center md:text-left">
                    {{-- Logo --}}
                    <div>
                        <div class="inline-flex items-center gap-2 text-[#00703c] font-black text-2xl tracking-tight">
                            <i class="fa-solid fa-seedling text-xl"></i>
                            <span>{{ $setup->store_name ?? 'FALAO FOOD' }}</span>
                        </div>
                    </div>

                    {{-- Main Headline --}}
                    <h1 class="text-3xl sm:text-4xl md:text-[42px] font-black text-[#00703c] leading-tight md:leading-[1.2] {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge">Title</span>@endif
                        {{ $title }}
                    </h1>

                    {{-- Short Description --}}
                    @if($shortDesc)
                        <div class="text-sm sm:text-base text-gray-700 leading-relaxed font-normal max-w-xl mx-auto md:mx-0 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge">Short Description</span>@endif
                            {!! $shortDesc !!}
                        </div>
                    @endif

                    {{-- Order CTA Button --}}
                    <div class="pt-2">
                        <a href="#order" class="px-10 py-3 landing5-pill-btn text-base sm:text-lg {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" style="top: -12px;">Button Text</span>@endif
                            <span>:: {{ $btnText }}</span>
                        </a>
                    </div>
                </div>

                {{-- Right Hero Product Image --}}
                <div class="md:col-span-5 flex justify-center">
                    <div class="relative w-full max-w-[380px] aspect-square rounded-3xl overflow-hidden border-2 border-emerald-100 shadow-xl bg-white flex items-center justify-center {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge">Thumbnail</span>@endif
                        <img src="{{ $productImg }}" alt="{{ $title }}" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </header>

        {{-- ══ SECTION 2: GREEN TRUST BANNER ══ --}}
        <section class="w-full bg-[#00703c] text-white py-8 md:py-12 px-4 text-center shadow-inner">
            <div class="max-w-4xl mx-auto space-y-3">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-black tracking-wide leading-snug">
                    আপনার পরিবারের জন্য নিরাপদ ও খাঁটি ঘি
                </h2>
                @if($desc)
                    <div class="text-sm sm:text-base text-emerald-100 leading-relaxed font-normal {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge" style="top:-5px; left:10px;">Details</span>@endif
                        {!! $desc !!}
                    </div>
                @endif
            </div>
        </section>

        {{-- ══ SECTION 3: BENEFITS SECTION (ঘি এর উপকারিতা) ══ --}}
        @if($content1)
            <section class="w-full py-12 md:py-20 px-4 bg-white border-b border-gray-100">
                <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-8 md:gap-12 items-center">
                    
                    {{-- Benefits Bullet List --}}
                    <div class="md:col-span-7 space-y-6">
                        <h3 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#00703c] pb-2 border-b-2 border-emerald-200 inline-block">
                            ঘি এর উপকারিতা
                        </h3>

                        <div class="prose prose-emerald max-w-none text-gray-700 leading-relaxed {{ $isPreviewMode ? 'preview-badge-container' : '' }} [&_ul]:space-y-3 [&_li]:flex [&_li]:items-start [&_li]:gap-3 [&_li]:text-sm sm:[&_li]:text-base [&_li]:font-medium [&_li]:before:content-['✔'] [&_li]:before:text-[#00703c] [&_li]:before:font-black [&_li]:before:text-base">
                            @if($isPreviewMode)<span class="preview-badge">Content</span>@endif
                            {!! $content1 !!}
                        </div>
                    </div>

                    {{-- Benefits Image --}}
                    <div class="md:col-span-5 flex justify-center">
                        <div class="relative w-full max-w-[360px] aspect-square rounded-3xl overflow-hidden border border-emerald-100 shadow-xl bg-gray-50 flex items-center justify-center">
                            <img src="{{ $productImg }}" alt="Benefits" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ══ SECTION 4: SPECIAL DISCOUNT & OFFER BOX ══ --}}
        @if($regularPrice || $discountPrice || $isPreviewMode)
            <section class="w-full bg-[#00703c] py-12 md:py-16 px-4">
                <div class="max-w-4xl mx-auto text-center space-y-6">
                    <h3 class="text-2xl sm:text-3xl md:text-4xl font-black text-white">
                        আজই অর্ডার করলে পাচ্ছেন বিশেষ মূল্যছাড়!
                    </h3>

                    {{-- White Card Inside Green Background (Exact Match with Image) --}}
                    <div class="bg-white rounded-3xl p-6 sm:p-10 text-center max-w-2xl mx-auto shadow-2xl space-y-6">
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-8">
                            
                            {{-- Orange Rosette Badge (% Icon) --}}
                            <div class="w-24 h-24 flex-shrink-0 bg-amber-500 text-white rounded-full flex items-center justify-center shadow-lg relative">
                                <i class="fa-solid fa-percent text-4xl font-black"></i>
                                <div class="absolute -bottom-2 -left-1 w-5 h-8 bg-amber-600 rounded-sm transform -rotate-45"></div>
                                <div class="absolute -bottom-2 -right-1 w-5 h-8 bg-amber-600 rounded-sm transform rotate-45"></div>
                            </div>

                            {{-- Price Details --}}
                            <div class="space-y-2 text-center sm:text-left">
                                @if($regularPrice)
                                    <div class="text-sm sm:text-base text-gray-500 font-semibold {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                        @if($isPreviewMode)<span class="preview-badge">Regular Price</span>@endif
                                        <span>{{ $regularPrice }}</span>
                                    </div>
                                @endif

                                @if($discountPrice)
                                    <div class="text-3xl sm:text-4xl font-black text-[#00703c] tracking-tight {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                        @if($isPreviewMode)<span class="preview-badge">Discount Price</span>@endif
                                        {{ $discountPrice }}
                                    </div>
                                @else
                                    <div class="text-3xl sm:text-4xl font-black text-[#00703c] tracking-tight">
                                        অফার মূল্য: {{ number_format($product->sale_price ?? $product->regular_price, 2) }}{{ $setup->currency }}
                                    </div>
                                @endif

                                @if($regularPrice)
                                    <div class="text-xs sm:text-sm text-gray-500 font-medium">
                                        {{ $regularPrice }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Pill Action Button --}}
                        <div class="pt-2">
                            <a href="#order" class="px-10 py-3 landing5-pill-btn text-base sm:text-lg">
                                <span>:: {{ $btnText }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ══ SECTION 5: WHY CHOOSE US (আমাদের থেকে কেন কিনবেন?) ══ --}}
        @if($content2)
            <section class="w-full py-12 md:py-20 px-4 bg-pattern border-b border-gray-100">
                <div class="max-w-2xl mx-auto space-y-8">
                    <div class="text-center">
                        <h3 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#00703c] pb-2 border-b-2 border-emerald-200 inline-block">
                            আমাদের থেকে কেন কিনবেন?
                        </h3>
                    </div>

                    <div class="bg-white/80 backdrop-blur-sm p-6 sm:p-8 rounded-3xl shadow-md border border-emerald-100 text-left prose prose-emerald max-w-none text-gray-700 leading-relaxed {{ $isPreviewMode ? 'preview-badge-container' : '' }} [&_ul]:space-y-3 [&_li]:flex [&_li]:items-start [&_li]:gap-3 [&_li]:text-sm sm:[&_li]:text-base [&_li]:font-medium [&_li]:before:content-['✔'] [&_li]:before:text-[#00703c] [&_li]:before:font-black [&_li]:before:text-base">
                        @if($isPreviewMode)<span class="preview-badge">Content 2</span>@endif
                        {!! $content2 !!}
                    </div>
                </div>
            </section>
        @endif

        {{-- ══ SECTION 6: CUSTOMER REVIEWS (আমাদের সন্তুষ্ট ক্রেতাদের মতামত) ══ --}}
        <section class="w-full bg-[#00703c] py-12 md:py-20 px-4 text-white">
            <div class="max-w-6xl mx-auto space-y-8">
                <div class="text-center">
                    <h3 class="text-2xl sm:text-3xl md:text-4xl font-black {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge">Review</span>@endif
                        আমাদের সন্তুষ্ট ক্রেতাদের মতামত
                    </h3>
                </div>

                @if(!empty($reviewImgs) && count($reviewImgs) > 0)
                    {{-- Standard image review slider if uploaded --}}
                    <div class="swiper rv-swiper landing5-swiper max-w-3xl mx-auto">
                        <div class="swiper-wrapper">
                            @foreach ($reviewImgs as $img)
                                <div class="swiper-slide !flex !justify-center">
                                    <div class="w-full max-w-[300px] bg-white rounded-3xl border border-gray-200 overflow-hidden mb-6 shadow-xl h-[300px] flex items-center justify-center p-3">
                                        <img src="{{ is_array($img) ? ($img['previewUrl'] ?? '') : (str_starts_with($img, 'blob:') || str_starts_with($img, 'data:') || str_starts_with($img, 'http') || str_starts_with($img, '/') ? $img : \Illuminate\Support\Facades\Storage::disk('r2')->url($img)) }}"
                                            class="w-full h-full object-cover rounded-2xl" alt="Customer Review">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination landing5-pagination"></div>
                    </div>
                @else
                    {{-- Exact Green/White Testimonial Cards matching Screenshot --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                        @foreach($previewReviewCards as $rev)
                            <div class="bg-white text-gray-800 rounded-3xl p-5 shadow-xl text-left flex flex-col justify-between border border-white">
                                <div>
                                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                                        <div>
                                            <p class="font-bold text-sm text-gray-900 leading-tight">{{ $rev['name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $rev['role'] }}</p>
                                        </div>
                                        <div class="flex items-center text-amber-500 text-xs">
                                            @for($i = 0; $i < $rev['rating']; $i++)
                                                <i class="fa-solid fa-star"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-600 leading-relaxed italic mb-4">
                                        "{{ $rev['comment'] }}"
                                    </p>
                                </div>
                                <div class="w-full h-32 rounded-2xl overflow-hidden border border-gray-100 bg-gray-50 flex items-center justify-center">
                                    <img src="{{ $rev['img'] }}" alt="Product Review" class="w-full h-full object-cover">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        {{-- ══ SECTION 7: DIRECT CALL / WHATSAPP BANNER ══ --}}
        @if($phone)
            <section class="w-full py-8 px-4 bg-white text-center border-b border-gray-100 space-y-4">
                <p class="text-base sm:text-lg font-bold text-gray-800">কল করুন বা WhatsApp / Messenger-এ মেসেজ দিন</p>
                <div class="flex flex-wrap items-center justify-center gap-4 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                    @if($isPreviewMode)<span class="preview-badge" style="top: -20px;">Phone Number</span>@endif
                    <a href="tel:{{ $phone }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#00703c] text-white font-bold rounded-full text-sm sm:text-base hover:bg-[#00582f] transition-all shadow-md">
                        <i class="fa-solid fa-phone"></i>
                        <span>কল করুন</span>
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phone) }}" target="_blank" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#25D366] text-white font-bold rounded-full text-sm sm:text-base hover:brightness-105 transition-all shadow-md">
                        <i class="fa-brands fa-whatsapp text-base"></i>
                        <span>WhatsApp</span>
                    </a>
                </div>
            </section>
        @endif

        {{-- ══ SECTION 8: ORDER FORM ══ --}}
        <section id="order" class="w-full py-12 md:py-16 px-4 bg-[#f8faff]">
            <div class="max-w-5xl mx-auto">
                <x-landing.order-form :landing="$landing" :products="collect([$product])" :isPreview="$isPreviewMode" />
            </div>
        </section>

        {{-- ══ SECTION 9: FOOTER ══ --}}
        <footer class="w-full bg-gray-50 py-8 px-4 text-center border-t border-gray-100 text-xs sm:text-sm text-gray-500 space-y-2">
            @if(!empty($setup->store_address))
                <p class="flex items-center justify-center gap-1.5 font-medium text-gray-600">
                    <i class="fa-solid fa-location-dot text-[#00703c]"></i>
                    <span>{{ $setup->store_address }}</span>
                </p>
            @endif

            <p>© {{ date('Y') }} {{ $setup->store_name ?? 'Falao Food' }} - সর্বস্বত্ব সংরক্ষিত।</p>
        </footer>

    </main>

    {{-- Swiper.js Scripts (Both CDN & window fallback) --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swiper !== 'undefined') {
                new Swiper('.landing5-swiper', {
                    slidesPerView: 1,
                    spaceBetween: 16,
                    loop: true,
                    autoplay: {
                        delay: 3500,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: '.landing5-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        640: {
                            slidesPerView: 2,
                            spaceBetween: 16,
                        }
                    }
                });
            }
        });
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
                    if (!badgeEl) return;
                    e.preventDefault();

                    const text = badgeEl.textContent.toLowerCase().replace(/\s+/g, ' ').trim();
                    let field = null;

                    if (text.includes('regular price')) {
                        field = 'regular_price';
                    } else if (text.includes('discount price')) {
                        field = 'discount_price';
                    } else if (text === 'title') {
                        field = 'title';
                    } else if (text.includes('short description')) {
                        field = 'short_description';
                    } else if (text.includes('thumbnail')) {
                        field = 'thumbnail';
                    } else if (text.includes('review')) {
                        field = 'img_paths';
                    } else if (text.includes('details') || text.includes('description')) {
                        field = 'description';
                    } else if (text.includes('phone')) {
                        field = 'phone_number';
                    } else if (text.includes('button text')) {
                        field = 'button_text';
                    } else if (text.includes('content 2')) {
                        field = 'content2';
                    } else if (text.includes('content')) {
                        field = 'content';
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
