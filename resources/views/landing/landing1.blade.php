<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $landing->title }}</title>
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
        $previewPhone = '01234567890';
        $previewShortDesc = '<p>১০০% খাঁটি ও প্রাকৃতিক উপাদান দিয়ে তৈরি, যা আপনার রোগ প্রতিরোধ ক্ষমতা বৃদ্ধি করে, রক্তচাপ নিয়ন্ত্রণে রাখে এবং শরীরকে রাখে সতেজ ও প্রাণবন্ত।</p>';
        $previewRegularPrice = '৪০০ গ্রাম সজিনা পাতার পাউডারের রেগুলার প্রাইস- ১৫০০/=';
        $previewDiscountPrice = '৪০০ গ্রাম সজিনা পাতার পাউডারের অফার প্রাইস- ১০৫০/=';
        $previewContent1 = '<h2 class="text-xl md:text-2xl font-bold mb-3">আমাদের পণ্যের বিশেষ বৈশিষ্ট্যসমূহ:</h2><ul><li>১০০% ন্যাচারাল ও কেমিক্যাল মুক্ত উপাদান</li><li>অরিজিনাল সজিনা পাতা থেকে বৈজ্ঞানিক উপায়ে প্রক্রিয়াজাত</li><li>সহজে হজমযোগ্য এবং প্রচুর পরিমাণে ভিটামিন ও মিনারেল সমৃদ্ধ</li><li>প্রতিদিনের পুষ্টির চাহিদা মেটাতে অত্যন্ত কার্যকর</li></ul>';
        $previewContent2 = '<h2 class="text-xl md:text-2xl font-bold mb-3">কেন আমাদের থেকে অর্ডার করবেন?</h2><ul><li>সরাসরি সংগৃহীত ফ্রেশ ও প্রিমিয়াম গ্রেড পণ্য</li><li>সারাদেশে দ্রুত ক্যাশ অন ডেলিভারি সুবিধা</li><li>পণ্য হাতে পেয়ে দেখে মূল্য পরিশোধের সুযোগ</li><li>১০০% মানিব্যাক ও রিটার্ন গ্যারান্টি</li></ul>';
        $previewDescription = '<div class="text-center py-4"><h2 class="text-2xl md:text-3xl font-bold mb-3 text-[#1f5e00]">শতভাগ খাঁটি ও নিরাপদ স্বাস্থ্য সুরক্ষায়</h2><p class="text-base md:text-lg text-gray-700 leading-relaxed">আমাদের এই পণ্যটি আপনার দৈনন্দিন জীবনে এনে দেবে প্রাকৃতিক শক্তি ও পুষ্টি। কোনো ক্ষতিকর উপাদান ব্যবহার করা হয়নি। আজই অর্ডার করুন এবং সুস্থ জীবনের সূচনা করুন।</p></div>';

        $previewFeatures2 = [
            [
                'title' => '১০০% বিশুদ্ধ ও পরীক্ষিত মান',
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

        $previewReviewImages = array_filter([$productImg, $productImg, $productImg]);
    @endphp

    <main>
        {{-- ══ SECTION 1: HERO ══ --}}
        <section class="bg-[#0D2601] text-white pt-10 pb-16 px-4 text-center">
            <div class="max-w-7xl mx-auto">

                <img src="{{ $setup->logo_url }}" alt="Logo" width="96" height="96"
                    class="mx-auto mb-6 h-12 md:h-16 w-auto object-contain">

                <div class="border border-[#2e8c03] p-4 md:p-6 rounded mb-6">
                    <h1 class="text-[45px] font-bold mb-4 leading-tight {{ $isPreviewMode ? 'preview-badge-container' : '' }}"
                        style="font-size: 45px;">
                        @if($isPreviewMode)<span class="preview-badge">Title</span>@endif
                        {{ $landing->title ?? ($product->title ?: ($isPreviewMode ? $previewTitle : '')) }}
                    </h1>
                </div>
                <p class="prose prose-invert text-[30px] [&_*]:!text-[30px] mx-auto leading-snug {{ $isPreviewMode ? 'preview-badge-container' : '' }}"
                    style="font-size: 30px;">
                    @if($isPreviewMode)<span class="preview-badge" style="top: -20px;">Short Description</span>@endif
                    {!! $landing->short_description ?? ($product->short_description ?: ($isPreviewMode ? $previewShortDesc : '')) !!}
                </p>

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
                                <img src="{{ $productImg }}" width="800" height="600" loading="eager" decoding="async"
                                    fetchpriority="high" class="w-full h-auto max-h-[60vh] object-contain rounded-xl"
                                    alt="{{ $landing->name ?? $product->title }}">
                            </div>
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
                        @endphp
                        <div
                            class="bg-[#C00000] text-white py-6 text-xl md:text-4xl font-medium rounded-xl mb-12 shadow-lg w-full leading-tight {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" style="top: 0; z-index: 50;">Phone Number</span>@endif
                            আমাদের থেকে বিস্তারিত জানতে এই নাম্বারে কল করুন @if($phone) <b class="font-bold ml-1 tracking-wider">{{ $phone }}</b> @endif
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
                            <div class="py-10 px-4 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
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
            <section class="bg-[#f1fcf1] py-16 px-4 mt-8 md:mt-10">
                <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10">

                    <div class="bg-white rounded-xl overflow-hidden">
                        <div
                            class="benefits-container text-base md:text-xl p-4 md:p-6 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" style="top: 0; z-index: 50;">Content</span>@endif
                            <article class="prose prose-slate max-w-none">
                                {!! $content1 ?? '' !!}
                            </article>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl overflow-hidden">
                        <div
                            class="benefits-container text-base md:text-xl p-4 md:p-6 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" style="top: 0; z-index: 50;">Content
                            2</span>@endif
                            <article class="prose prose-slate max-w-none">
                                {!! $content2 ?? '' !!}
                            </article>
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
            <section class="py-12 px-4">
                <div class="max-w-2xl mx-auto text-center">
                    <article class="prose prose-slate max-w-none">
                        {!! $desc !!}
                    </article>
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
                                <h2 class="text-2xl font-bold text-[#1a3a1a] mb-4">
                                    {{ $feature['title'] ?? '' }}
                                </h2>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                                    <article class="prose prose-slate max-w-none text-lg">
                                        {!! $feature['description'] ?? '' !!}
                                    </article>

                                    @php
                                        $fImg = !empty($feature['image']) ? $feature['image'] : ($isPreviewMode ? $productImg : null);
                                    @endphp
                                    @if (!empty($fImg))
                                        <div class="rounded-lg overflow-hidden flex justify-center">
                                            <img src="{{ is_array($fImg) ? ($fImg['previewUrl'] ?? '') : (str_starts_with($fImg, 'blob:') || str_starts_with($fImg, 'data:') || str_starts_with($fImg, 'http') || str_starts_with($fImg, '/') ? $fImg : \Illuminate\Support\Facades\Storage::disk('r2')->url($fImg)) }}"
                                                alt="{{ $feature['title'] ?? 'Feature' }}" width="400" height="300" loading="lazy"
                                                decoding="async"
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
                        class="inline-block border-4 md:border-10 border-[#4caf50] px-4 py-4 rounded-lg text-base sm:text-lg md:text-2xl font-semibold mb-10 uppercase">
                        {{ data_get($landing->extras, 'features_title') ?: ($isPreviewMode ? 'কেন এই পণ্যটি ব্যবহার করবেন?' : 'বিশেষ উপকারিতাসমূহ') }}
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
                        class="text-2xl sm:text-3xl md:text-5xl font-semibold text-[#1f5e00] mb-2 {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                        @if($isPreviewMode)<span class="preview-badge" style="top: 0; z-index: 50;">Title</span>@endif
                        {{ $landing->title ?? ($product->title ?: ($isPreviewMode ? $previewTitle : '')) }}
                    </h2>
                    <p class="text-base md:text-2xl text-[#1f5e00] mb-8">সাশ্রয়ী মূল্যে সেরা পণ্য</p>
                    <div class="bg-[#C00000] text-white p-6 md:p-10 rounded-xl shadow-lg">
                        @if($regularPrice)
                            <div class="{{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                                @if($isPreviewMode)<span class="preview-badge" style="top: -20px; z-index: 50;">Regular
                                Price</span>@endif
                                <p class="text-base md:text-2xl font-medium mb-3 line-through opacity-80">
                                    {{ $regularPrice }}
                                </p>
                            </div>
                        @endif
                        <div class="{{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                            @if($isPreviewMode)<span class="preview-badge" style="top: -20px; z-index: 50;">Discount
                            Price</span>@endif
                            <h3 class="text-xl sm:text-2xl md:text-4xl font-semibold leading-snug">
                                {{ $discountPrice ?: $regularPrice }} সীমিত সময়ের জন্য
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
                <div class="container mx-auto {{ $isPreviewMode ? 'preview-badge-container' : '' }}">
                    @if($isPreviewMode)<span class="preview-badge" style="top: 0; z-index: 50;">Review Images</span>@endif
                    <h2 class="text-2xl sm:text-3xl md:text-5xl font-black text-center mb-12 text-[#0D2601]">
                        সম্মানিত কাস্টমারদের মতামত
                    </h2>

                    <div class="relative px-4 md:px-10">
                        <!-- Swiper Container -->
                        <div class="swiper rv-swiper">
                            <div class="swiper-wrapper">
                                @foreach ($reviewImgs as $img)
                                    <div class="swiper-slide">
                                        <div
                                            class="bg-white rounded-2xl border border-gray-100 overflow-hidden mx-1 mb-10 flex items-center justify-center p-2 shadow-sm min-h-[220px]">
                                            <img src="{{ is_array($img) ? ($img['previewUrl'] ?? '') : (str_starts_with($img, 'blob:') || str_starts_with($img, 'data:') || str_starts_with($img, 'http') || str_starts_with($img, '/') ? $img : \Illuminate\Support\Facades\Storage::disk('r2')->url($img)) }}"
                                                class="w-auto h-auto max-w-full max-h-[380px] md:max-h-[450px] object-contain rounded-xl mx-auto"
                                                alt="Customer Review">
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
        <section id="order" class="py-14 px-4 bg-[#f8faff]">
            <x-landing.order-form :landing="$landing" :isPreview="$isPreviewMode" />
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
                    <a href="{{ $privacyLink }}" target="_blank" class="hover:text-[#2e8c03] transition-colors">Privacy
                        Policy</a>
                    <a href="{{ $termsLink }}" target="_blank" class="hover:text-[#2e8c03] transition-colors">Terms &
                        Conditions</a>
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
        function initRvSwiper() {
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
        }

        if (window.SwiperPromise) {
            window.SwiperPromise.then(initRvSwiper);
        } else {
            document.addEventListener('DOMContentLoaded', initRvSwiper);
        }
    </script>

    @if(isset($isPreview) && $isPreview)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.addEventListener('click', function (e) {
                    const badge = e.target.closest('.preview-badge');
                    const container = e.target.closest('.preview-badge-container');
                    const targetEl = badge || container;
                    if (!targetEl) return;

                    const badgeEl = badge || targetEl.querySelector('.preview-badge');
                    if (!badgeEl) return;

                    const text = badgeEl.textContent.trim().toLowerCase();
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
                    } else if (text.includes('product details') || text.includes('description')) {
                        field = 'description';
                    } else if (text.includes('features 2')) {
                        field = 'features2';
                    } else if (text.includes('features')) {
                        field = 'features';
                    } else if (text.includes('button text')) {
                        field = 'button_text';
                    } else if (text.includes('phone number')) {
                        field = 'phone_number';
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
