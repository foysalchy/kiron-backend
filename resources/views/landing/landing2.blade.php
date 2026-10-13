<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $landing->title }}</title>
    <meta name="description"
        content="{{ \Illuminate\Support\Str::limit(strip_tags($landing->short_description ?: $landing->description ?: $landing->title), 160) }}">
    <link rel="icon" type="image/x-icon" href="{{ $setup->favicon_url }}">
    @vite(['resources/css/app.css', 'resources/css/landing2.css'])

    @include('components.meta-info.pixel', ['landing' => $landing, 'setup' => $setup])

    <style>
        body {
            font-family:
                {!! $setup->lang === 'bn' ? "'Noto Sans Bengali', sans-serif" : "'Outfit', sans-serif" !!}
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

        .bg-grid-blue {

            background-image:
                linear-gradient(rgba(100, 160, 230, 0.1) 3px, transparent 1px),
                linear-gradient(90deg, rgba(100, 160, 230, 0.1) 3px, transparent 1px);
            background-size: 60px 38px;
        }

        /* Circle sketch SVG underline */
        .circle-sketch {
            position: relative;
            display: inline-block;
        }

        .circle-sketch svg {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 120%;
            height: 220%;
            pointer-events: none;
            fill: none;
            stroke-width: 8;
            stroke-dasharray: 1500;
            stroke-dashoffset: 1500;
            animation: draw-circle 1.2s ease forwards 0.3s;
        }

        @keyframes draw-circle {
            to {
                stroke-dashoffset: 0;
            }
        }

        /* Wavy underline */
        .wavy-underline {
            position: relative;
            display: inline-block;
        }

        .wavy-underline svg {
            position: absolute;
            bottom: -12px;
            left: 0;
            width: 100%;
            height: 20px;
            fill: none;
            stroke-width: 6;
            stroke-dasharray: 800;
            stroke-dashoffset: 800;
            animation: draw-circle 1s ease forwards 0.6s;
        }

        .bg-dark-grid {
            background-color: #05053c;
            /* Deep Navy Blue */
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.068) 3px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 3px, transparent 1px);
            background-size: 60px 60px;
        }

        /* Circle Sketch for Price */
        .price-circle {
            position: relative;
            display: inline-block;
        }

        .price-circle svg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 130%;
            height: 120%;
            pointer-events: none;
        }

        /* Pulse badge */
        @keyframes pulse-badge {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.04);
            }
        }

        .pulse {
            animation: pulse-badge 2s ease-in-out infinite;
        }

        /* Bounce arrow */
        @keyframes bounce-down {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(6px);
            }
        }

        .bounce {
            animation: bounce-down 1.4s ease-in-out infinite;
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

<body class="bg-grid-blue text-gray-800">
    @php
        $isPreviewMode = !empty($isPreview) || request()->is('*landing/preview*') || !empty($landing->is_preview);
        $productImg = $landing->thumbnail_url ?? $product->display_image_url ?? $product->thumbnail_url ?? null;

        $previewTitle = 'বাচ্চাদের স্মার্ট লার্নিং ও ড্রয়িং বুক সেট';
        $previewShortDesc = '<h1>আপনার সোনামণির মেধা বিকাশ ও </h1><p class="text-3xl lg:text-4xl">ডিজিটাল আসক্তি দূর করতে সেরা উপহার!</p>';
        $previewRegularPrice = '৳ ১,৫০০';
        $previewDiscountPrice = '৳ ৯৫০';
        $previewButtonText = 'আপনারটি নিন এখনই 😍';
    @endphp

    <main>
        <section class="main-hero">
            <div class="h-6 md:h-12"></div>

            <div class="flex justify-center relative z-10" style="margin-bottom: -42px;">
                <div class="px-8 py-6">

                    <img src="{{ $setup->logo_url ?? '' }}" alt="Logo" width="220" height="48" loading="eager"
                        decoding="async" fetchpriority="high" class="mx-auto h-12 md:h-16 w-auto object-contain">
                </div>
            </div>
            <div class="main-container bg-[#ebf0fa]/60 p-4 sm:p-6 md:p-10">

                {{-- ══ HERO ══ --}}
                <section class="py-4 px-4 text-center">
                    <div class="max-w-6xl mx-auto">

                        {{-- Blue badge Hero Title --}}
                        <h1
                            class="inline-block bg-gradient-to-r from-[#005EFF] to-[#003A9C] text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl font-bold text-2xl sm:text-3xl md:text-[38px] leading-snug md:leading-tight mb-8 shadow-lg max-w-full break-words {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge"
                            style="top: -20px; color: white;">Title</span>@endif
                            {{ $landing->title ?? ($product->title ?: ($isPreviewMode ? $previewTitle : '')) }}
                        </h1>

                        {{-- Main headline --}}
                        <div
                            class="prose max-w-none mx-auto mb-4 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" style="top: -20px; z-index: 50;">Short
                            Description</span>@endif
                            {!! $landing->short_description ?? ($product->short_description ?: ($isPreviewMode ? $previewShortDesc : '')) !!}
                        </div>

                        {{-- Product image --}}
                        <div
                            class="max-w-2xl mx-auto rounded-2xl overflow-hidden shadow-2xl mb-8 flex justify-center items-center bg-white/40 p-2 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" style="top: 0; z-index: 50;">Thumbnail /
                            Video</span>@endif
                            @if ($landing->video)
                                <div class="w-full relative aspect-video rounded-xl overflow-hidden bg-black">
                                    <video class="w-full h-full object-contain" controls playsinline
                                        poster="{{ $productImg }}">
                                        <source src="{{ $landing->video_url }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            @else
                                <img src="{{ $productImg }}" width="800" height="600" loading="eager" decoding="async"
                                    fetchpriority="high"
                                    class="w-auto h-auto max-w-full max-h-[550px] object-contain rounded-xl shadow-sm"
                                    alt="{{ $landing->name ?? $product->title }}">
                            @endif
                        </div>

                        {{-- Stock status --}}
                        @if(!empty($landing->extras['features']) || $isPreviewMode)
                            <div
                                class="space-y-3 mb-10 text-lg md:text-2xl font-bold text-left md:text-center {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                @if($isPreviewMode)<span class="preview-badge"
                                style="top: -20px; z-index: 50;">Features</span>@endif
                                @php
                                    $featuresTitle = $landing->extras['features_title'] ?? ($isPreviewMode ? 'আপনার সোনামণির জন্য সেরা কেন?' : '');
                                    $fWords = explode(' ', $featuresTitle);
                                    $fLastPart = count($fWords) > 2 ? array_splice($fWords, -2) : array_splice($fWords, -1);
                                    $fFirstPart = implode(' ', $fWords);
                                @endphp
                                <div class="mb-10 text-center">
                                    <h2 class="text-xl sm:text-2xl md:text-4xl font-bold leading-normal md:leading-relaxed">
                                        {{ $fFirstPart }}
                                        <span class="wavy-underline text-[#fc4124] px-1 relative inline-block">
                                            {{ implode(' ', $fLastPart) }}
                                            <svg class="absolute left-0 bottom-[-10px] w-full" viewBox="0 0 500 40"
                                                preserveAspectRatio="none">
                                                <path d="M3,20c49.3-3,150.7-7.6,199.7-7.4c121.9,0.4,189.9,5,282.3,7.2"
                                                    stroke="#fc4124" fill="transparent" stroke-width="4" />
                                            </svg>
                                        </span>
                                    </h2>
                                </div>

                                <p class="mb-4">
                                    {{ $landing->extras['features'][0] ?? ($isPreviewMode ? '📱 মোবাইল আসক্তি কমাবে' : '') }}
                                </p>

                                <p>
                                    @if(empty($landing->extras['features'][1]) && empty($landing->extras['features'][2]) && !$isPreviewMode)
                                        💥 ⏰
                                    @else
                                        {{ $landing->extras['features'][1] ?? ($isPreviewMode ? '💥 দ্রুত বুদ্ধি ও মেধা বিকাশে দারুণ সহায়ক' : '') }}
                                    @endif

                                    @if(!empty($landing->extras['features'][2]) || $isPreviewMode)
                                        <span
                                            class="bg-[#c9250b] text-white px-3 py-2 rounded-lg ml-1 inline-block mt-2 md:mt-0 font-medium">
                                            {{ $landing->extras['features'][2] ?? ($isPreviewMode ? '১০০% নিরাপদ ও টেকসই' : '') }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div
                                class="inline-block bg-gradient-to-r from-[#EEA727] to-[#FFEF5F] px-6 md:px-8 py-3 rounded-xl font-bold text-xl md:text-3xl mb-14 shadow-lg text-black">
                                {{ $landing->extras['features'][3] ?? ($isPreviewMode ? 'সাথে ১ বছরের রিপ্লেসমেন্ট গ্যারান্টি 😍' : 'সাথে ১ বছরের রিপ্লেসমেন্ট 😍') }}
                            </div>
                        @endif

                        {{-- Why best section --}}
                        @if(!empty($landing->extras['features2']) || $isPreviewMode)
                            @php
                                $feature2Title =
                                    $landing->extras['features2'][0]['title'] ?? ($isPreviewMode ? 'এটি কেন আপনার সোনামণির জন্য সেরা?' : '');
                                $words = explode(' ', $feature2Title);
                                $lastPart = array_splice($words, -3); //
                                $firstPart = implode(' ', $words);
                            @endphp
                            <div class="mb-10 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                @if($isPreviewMode)<span class="preview-badge" style="top: -20px; z-index: 50;">Features
                                2</span>@endif
                                <h2 class="text-xl sm:text-2xl md:text-4xl font-bold leading-normal md:leading-relaxed">
                                    {{ $firstPart }}
                                    <span class="wavy-underline text-[#fc4124] px-1 relative inline-block">
                                        {{ implode(' ', $lastPart) }}
                                        <svg class="absolute left-0 bottom-[-10px] w-full" viewBox="0 0 500 40"
                                            preserveAspectRatio="none">
                                            <path d="M3,20c49.3-3,150.7-7.6,199.7-7.4c121.9,0.4,189.9,5,282.3,7.2"
                                                stroke="#fc4124" fill="transparent" stroke-width="4" />
                                        </svg>
                                    </span>
                                </h2>
                            </div>

                            {{-- Category map image --}}
                            <div class="max-w-3xl mx-auto mb-14 overflow-hidden rounded-xl">
                                @php
                                    $categoryMap = $landing->extras['features2'][0]['image'] ?? null;
                                    $categoryDesc = $landing->extras['features2'][0]['description'] ?? ($isPreviewMode ? '<p>স্মার্ট লার্নিং কার্ড ও আকর্ষণীয় ছবি দিয়ে তৈরি, যা বাচ্চাদের খেলাচ্ছলে নতুন বিষয় শিখতে উৎসাহিত করে। এতে করে তাদের শেখার আগ্রহ বহুগুণ বেড়ে যায়।</p>' : null);
                                    if (is_array($categoryMap)) {
                                        $catMapSrc = $categoryMap['previewUrl'] ?? $categoryMap['url'] ?? $categoryMap['path'] ?? '';
                                    } elseif (is_string($categoryMap)) {
                                        $catMapSrc = (str_starts_with($categoryMap, 'blob:') || str_starts_with($categoryMap, 'data:') || str_starts_with($categoryMap, 'http') || str_starts_with($categoryMap, '/'))
                                            ? $categoryMap
                                            : \Illuminate\Support\Facades\Storage::disk('r2')->url($categoryMap);
                                    } else {
                                        $catMapSrc = $productImg;
                                    }
                                @endphp
                                <img src="{{ $catMapSrc ?: $productImg }}" alt="Categories" width="600" height="600"
                                    loading="lazy" decoding="async"
                                    class="w-auto h-auto max-w-full max-h-[550px] mx-auto object-contain rounded-xl">

                                @if($categoryDesc)
                                    <div
                                        class="mt-6 prose prose-slate max-w-none text-gray-700 leading-relaxed text-center md:text-left">
                                        {!! $categoryDesc !!}
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(!empty($landing->extras['features3']) || $isPreviewMode)
                            <div
                                class="w-full max-w-4xl mx-auto space-y-4 md:space-y-6 mb-16 text-left {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                @if($isPreviewMode)<span class="preview-badge" style="top: -20px; z-index: 50;">Features
                                3</span>@endif
                                @php
                                    $features3 = $landing->extras['features3'] ?? [];
                                    $paddingClasses = ['md:pl-48', 'md:pl-32', 'md:pl-16', 'md:pl-8', 'md:pl-0'];
                                @endphp

                                @if(empty($features3))
                                    <!-- Point 1 -->
                                    <div class="flex gap-2.5 sm:gap-3 text-base md:text-lg font-bold items-start md:pl-48">
                                        <div class="bg-[#50d084] rounded p-0.5 text-white flex-shrink-0 mt-1 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <p class="leading-relaxed text-gray-800 min-w-0 flex-1 break-words">
                                            বাজারের সবচেয়ে লেটেস্ট আপডেটে <span class="text-[#fc4124]">৫১০ টি কার্ডে</span>
                                            রয়েছে ৪২টি ক্যাটাগরির শব্দ
                                        </p>
                                    </div>

                                    <!-- Point 2 -->
                                    <div class="flex gap-2.5 sm:gap-3 text-base md:text-lg font-bold items-start md:pl-32">
                                        <div class="bg-[#50d084] rounded p-0.5 text-white flex-shrink-0 mt-1 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <p class="leading-relaxed text-gray-800 min-w-0 flex-1 break-words">
                                            স্মার্ট ও টেকসই ডিজাইন লেমিনেটেড কাগজ, <span class="text-[#fc4124]">সম্পূর্ণ
                                                ওয়াটারপ্রুফ খুবই মজবুত,</span> সহজে ছিঁড়ে যায় না বা নষ্ট হয় না
                                        </p>
                                    </div>

                                    <!-- Point 3 -->
                                    <div class="flex gap-2.5 sm:gap-3 text-base md:text-lg font-bold items-start md:pl-16">
                                        <div class="bg-[#50d084] rounded p-0.5 text-white flex-shrink-0 mt-1 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <p class="leading-relaxed text-gray-800 min-w-0 flex-1 break-words">
                                            ভয়েস রিপিট ফিচার, <span class="text-[#fc4124]">শিশু যা বলবে, বইটি তা-ই রিপিট
                                                করবে।</span> এতে করে শেখা হবে আরও মজাদার, বাড়বে আত্মবিশ্বাস ও পড়ার আগ্রহ.
                                        </p>
                                    </div>

                                    <!-- Point 4 -->
                                    <div class="flex gap-2.5 sm:gap-3 text-base md:text-lg font-bold items-start md:pl-8">
                                        <div class="bg-[#50d084] rounded p-0.5 text-white flex-shrink-0 mt-1 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <p class="leading-relaxed text-gray-800 min-w-0 flex-1 break-words">
                                            প্রাথমিক শিক্ষার সকল কিছু যেমন <span class="text-[#fc4124]">বাংলা বর্ণমালা,
                                                Alphabet, আরবি বর্ণমালা, বাংলা সাংখ্যা, ইংরেজি সংখ্যা</span> রয়েছে এই বইটিতে
                                        </p>
                                    </div>

                                    <!-- Point 5 -->
                                    <div class="flex gap-2.5 sm:gap-3 text-base md:text-lg font-bold items-start md:pl-0">
                                        <div class="bg-[#50d084] rounded p-0.5 text-white flex-shrink-0 mt-1 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <p class="leading-relaxed text-gray-800 min-w-0 flex-1 break-words">
                                            পবিত্র কোরআনের ১৫টি সুরা ১৫টি শ্রুতিমধুর ইসলামিক গজল নতুন সংযোজন হয়েছে, <span
                                                class="text-[#fc4124]">এতে আপনার শিশু</span> ধর্মীয় শিক্ষায় সুশিক্ষা লাভ করবে
                                        </p>
                                    </div>
                                @else
                                    @foreach ($features3 as $index => $point)
                                        <div
                                            class="flex gap-2.5 sm:gap-3 text-base md:text-lg font-bold items-start {{ $paddingClasses[$index] ?? 'md:pl-0' }}">
                                            <div class="bg-[#50d084] rounded p-0.5 text-white flex-shrink-0 mt-1 shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                        d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>

                                            <div class="leading-relaxed text-gray-800 min-w-0 flex-1 break-words">
                                                {!! $point !!}
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        @endif
                    </div>
                </section>


                {{-- ══ BLACK OFFER SECTION ══ --}}
                @if(!empty($landing->extras['features4']) || $isPreviewMode || ($landing->regular_price || $landing->discount_price))
                    <section
                        class="max-w-5xl mx-auto bg-dark-grid p-8 px-4 text-center text-white {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge" style="top: 0; z-index: 50;">Features 4</span>@endif
                        @php
                            $promoData = $landing->extras['features4'][0] ?? null;
                            $promoHeading = $promoData['title'] ?? ($isPreviewMode ? '⚡ সীমিত সময়ের বিশেষ ধামাকা অফার!' : '⚡ দেরি শেষ!');
                            $promoImage = $promoData['image'] ?? null;
                        @endphp
                        <!-- Heading -->
                        <h3
                            class="text-xl sm:text-2xl md:text-4xl font-bold mb-6 flex items-center justify-center gap-2 leading-normal md:leading-relaxed">
                            {!! $promoHeading !!}
                        </h3>

                        @php
                            $promoImage = $promoData['image'] ?? null;
                            if (is_array($promoImage)) {
                                $promoImgSrc = $promoImage['previewUrl'] ?? $promoImage['url'] ?? $promoImage['path'] ?? '';
                            } elseif (is_string($promoImage)) {
                                $promoImgSrc = (str_starts_with($promoImage, 'blob:') || str_starts_with($promoImage, 'data:') || str_starts_with($promoImage, 'http') || str_starts_with($promoImage, '/'))
                                    ? $promoImage
                                    : \Illuminate\Support\Facades\Storage::disk('r2')->url($promoImage);
                            } else {
                                $promoImgSrc = $productImg;
                            }
                        @endphp
                        <!-- Product Image in Frame -->
                        <div
                            class="max-w-xl mx-auto rounded-xl overflow-hidden mb-8 border-2 border-white border-dashed shadow-2xl bg-white/5">

                            <img src="{{ $promoImgSrc ?: $productImg }}" alt="Offer Product" width="600" height="600"
                                loading="lazy" decoding="async"
                                class="w-auto h-auto max-w-full max-h-[500px] mx-auto object-contain">
                        </div>

                        @php
                            $promoDesc = $promoData['description'] ?? ($isPreviewMode ? '<p>স্টক সীমিত! আপনার সোনামণির উজ্জ্বল ভবিষ্যতের জন্য এখনই অর্ডার সম্পন্ন করুন।</p>' : null);
                        @endphp
                        @if($promoDesc)
                            <div class="mb-12 prose prose-invert max-w-none text-white/90 leading-relaxed text-center">
                                {!! $promoDesc !!}
                            </div>
                        @endif

                        <!-- Price Section -->
                        <div class="space-y-8 mb-12">
                            <h4
                                class="text-2xl md:text-5xl font-medium leading-tight {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                @if($isPreviewMode)<span class="preview-badge" style="top: -20px; z-index: 50;">Discount
                                Price</span>@endif
                                {{ $landing->discount_price ?: ($isPreviewMode ? $previewDiscountPrice : '') }}

                            </h4>

                            <h4
                                class="text-xl md:text-4xl font-medium text-white/90 line-through opacity-80 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                @if($isPreviewMode)<span class="preview-badge" style="top: -20px; z-index: 50;">Regular
                                Price</span>@endif
                                {{ $landing->regular_price ?: ($isPreviewMode ? $previewRegularPrice : '') }}
                            </h4>
                        </div>
                        <!-- 3D Green Button -->


                    </section>
                @endif
                <div class="mt-14 text-center">
                    <a href="#order"
                        class="inline-flex items-center gap-3 bg-red-600 hover:border-none text-white px-4 md:px-12 py-3 rounded-xl font-bold text-sm md:text-2xl border-2 border-blue-900 uppercase {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge" style="top: -20px; z-index: 50;">Button
                        Text</span>@endif
                        <svg aria-hidden="true" class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 7v10m-5-5 5 5 5-5"></path>
                        </svg>
                        <span>{{ $landing->button_text ?: ($isPreviewMode ? $previewButtonText : 'আপনারটি নিন এখনই 😍') }}</span>
                    </a>
                </div>
            </div>
        </section>


        {{-- ══ ORDER FORM ══ --}}
        <section id="order" class="py-16 px-4 bg-[#f5f6ff] relative z-10 shadow-[0_-20px_50px_-12px_rgba(0,0,0,0.15)]">
            <x-landing.order-form :landing="$landing" :isPreview="$isPreviewMode" />
        </section>

    </main>

    {{-- ══ FOOTER ══ --}}
    <footer class="bg-grid-dark py-16 px-4 text-center text-white bg-dark-grid border-gray-800">
        <div class="max-w-5xl mx-auto">
            <div class="text-lg md:text-3xl font-bold mb-8">
                📢 আমাদের অফিশিয়াল
                <span>
                    @foreach ($socialLinks as $link)
                        <a href="{{ $link->link }}" target="_blank"
                            class="text-white hover:text-[#7DF9FF] transition-colors">
                            {{ $link->icon_name }}
                        </a>{{ $loop->remaining == 1 ? (app()->getLocale() == 'bn' ? ' এবং ' : ' and ') : ($loop->last ? '' : ', ') }}
                    @endforeach
                </span>
                পেইজের সাথে যুক্ত থাকুন 🔥
            </div>
            <img src="{{ $setup->logo_url ?? '' }}" alt="Logo" width="96" height="96"
                class="mx-auto h-12 md:h-16 w-auto mb-6 object-contain brightness-200">
            <p>© {{ date('Y') }} {{ $setup->shop_name ?? '' }}. All rights reserved.</p>
        </div>
    </footer>

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
                    // Normalize whitespace and newlines so multi-line text matches single-space strings
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
                    } else if (text.includes('thumbnail') || text.includes('video')) {
                        field = 'thumbnail';
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