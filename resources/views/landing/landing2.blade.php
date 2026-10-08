<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $landing->title }}</title>
    <meta name="description"
        content="{{ \Illuminate\Support\Str::limit(strip_tags($landing->short_description ?: $landing->description ?: $landing->title), 160) }}">
    <link rel="icon" type="image/x-icon" href="{{ $setup->favicon_url }}">
    @vite('resources/css/landing2.css')

    <style>
        body {
            font-family: {!! $setup->lang === 'bn' ? "'Noto Sans Bengali', sans-serif" : "'Outfit', sans-serif" !!};
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
</head>

<body class="bg-grid-blue text-gray-800">

    <main>
    <section class="main-hero">
        <div class="h-6 md:h-12"></div>

        <div class="flex justify-center relative z-10" style="margin-bottom: -42px;">
            <div class="px-8 py-6">

                <img src={{ $setup->logo_url ?? '' }} alt="KidzFun Logo" width="80" height="80" class="w-20">
            </div>
        </div>
        <div class="main-container bg-[#ebf0fa]/60 p-10">

            {{-- ══ HERO ══ --}}
            <section class="py-4 px-4 text-center">
                <div class="max-w-5xl mx-auto">

                    {{-- Blue badge --}}
                    <div
                        class="inline-block bg-gradient-to-r from-[#005EFF] to-[#003A9C] text-white px-6 py-3 rounded-xl font-bold text-base md:text-3xl mb-8 shadow-lg ">
                        {!! $landing->name !!}
                    </div>

                    {{-- Main headline --}}
                    <div
                        class="prose prose-slate max-w-none mb-4
                        prose-h1:text-6xl lg:prose-h1:text-7xl
                        prose-p:text-3xl lg:prose-p:text-4xl
                        ">
                        {!! $landing->short_description ?? '' !!}
                    </div>

                    {{-- Product image --}}
                    <div class="max-w-3xl mx-auto rounded-xl overflow-hidden shadow-2xl mb-8">
                        @if ($landing->video)
                            <video class="w-full h-[600px] " controls playsinline poster="{{ $landing->thumbnail_url }}">
                                <source src="{{ $landing->video_url }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        @else
                            <img src="{{ $landing->thumbnail_url ?? asset('./images/default-thumbnail.jpg') }}"
                                width="800" height="800" class="w-full aspect-square object-contain"
                                alt="{{ $landing->name }}">
                        @endif
                    </div>

                    {{-- Stock status --}}
                    <div class="space-y-3 mb-10 text-lg md:text-2xl font-bold text-left md:text-center">
                        <p class="mb-4">📱 {{ $landing->extras['features'][0] ?? 'অ্যাডিকশন কমাবে' }}
                        </p>

                        <p>💥 {{ $landing->extras['features'][1] ?? '' }} ⏰
                            <span class="bg-[#fc4124] text-white px-3 py-2 rounded-lg ml-1 inline-block mt-2 md:mt-0">
                                {{ $landing->extras['features'][2] ?? '' }}
                            </span>
                        </p>
                    </div>
                    <div
                        class="inline-block bg-gradient-to-r from-[#EEA727] to-[#FFEF5F] px-6 md:px-8 py-3 rounded-xl font-bold text-xl md:text-3xl mb-14 shadow-lg text-black">
                        {{ $landing->extras['features'][3] ?? 'সাথে ১ বছরের রিপ্লেসমেন্ট' }}😍
                    </div>

                    {{-- Why best section --}}
                    @php
                        $feature2Title =
                            $landing->extras['features2'][0]['title'] ?? 'এটি কেন আপনার সোনামণির জন্য সেরা?';
                        $words = explode(' ', $feature2Title);
                        $lastPart = array_splice($words, -3); //
                        $firstPart = implode(' ', $words);
                    @endphp
                    <div class="mb-10">
                        <h2 class="text-xl md:text-4xl font-semibold leading-snug">
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
                        @endphp
                        <img src="{{ $categoryMap ? is_array($categoryMap) ? ($categoryMap['previewUrl'] ?? '') : (str_starts_with($categoryMap, 'blob:') || str_starts_with($categoryMap, 'data:') ? $categoryMap : \Illuminate\Support\Facades\Storage::disk('r2')->url($categoryMap)) : 'https://kidzfunbd.com/wp-content/uploads/2026/04/web-ak-bg-800x800.webp' }}"
                            alt="Categories" width="800" height="800"
                            class="w-full aspect-square object-contain">
                    </div>

                    <div class="container mx-auto space-y-6 mb-16">
                        @php
                            $features3 = $landing->extras['features3'] ?? [];
                            $paddingClasses = ['md:pl-48', 'md:pl-32', 'md:pl-16', 'md:pl-8', 'md:pl-0'];
                        @endphp

                        @foreach ($features3 as $index => $point)
                            <div
                                class="flex gap-3 text-lg font-bold items-start {{ $paddingClasses[$index] ?? 'md:pl-0' }}">
                                <div class="bg-[#50d084] rounded p-0.5 text-white flex-shrink-0 mt-1 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>

                                <div class="leading-relaxed text-gray-800">
                                    {!! $point !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>


            {{-- ══ BLACK OFFER SECTION ══ --}}
            <section class="max-w-5xl mx-auto bg-dark-grid p-8 px-4 text-center text-white">
                @php
                    $promoData = $landing->extras['features4'][0] ?? null;
                    $promoHeading = $promoData['title'] ?? '⚡ দেরি শেষ!';
                    $promoImage = $promoData['image'] ?? null;
                @endphp
                <!-- Heading -->
                <h3 class="text-2xl md:text-3xl font-bold mb-6 flex items-center justify-center gap-2">
                    {!! $promoHeading !!}
                </h3>

                <!-- Product Image in Frame -->
                <div
                    class="max-w-xl mx-auto rounded-xl overflow-hidden mb-12 border-2 border-white border-dashed shadow-2xl bg-white/5">

                    <img src="{{ $promoImage ? is_array($promoImage) ? ($promoImage['previewUrl'] ?? '') : (str_starts_with($promoImage, 'blob:') || str_starts_with($promoImage, 'data:') ? $promoImage : \Illuminate\Support\Facades\Storage::disk('r2')->url($promoImage)) : $landing->thumbnail_url }}"
                        alt="Offer Product" width="800" height="800"
                        class="w-full aspect-square object-contain">
                </div>

                <!-- Price Section -->
                <div class="space-y-8 mb-12">
                    <h4 class="text-2xl md:text-5xl font-medium leading-tight">
                        {{ $landing->discount_price }}

                    </h4>

                    <h4 class="text-xl md:text-4xl font-medium text-white/90">
                        {{ $landing->regular_price }}
                    </h4>
                </div>
                <!-- 3D Green Button -->


            </section>
            <div class="mt-14 text-center">
                <a href="#order"
                    class="inline-flex items-center gap-3 bg-red-600 hover:border-none text-white px-4 md:px-12 py-3 rounded-xl font-bold text-sm md:text-2xl border-2 border-blue-900 uppercase">
                    <svg aria-hidden="true" class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 7v10m-5-5 5 5 5-5"></path>
                    </svg>
                    আপনারটি নিন এখনই 😍
                </a>
            </div>
        </div>
    </section>


    {{-- ══ ORDER FORM ══ --}}
    <section id="order" class="py-16 px-4 bg-[#f5f6ff] relative z-10 shadow-[0_-20px_50px_-12px_rgba(0,0,0,0.15)]">
        <x-landing.order-form :landing="$landing" />
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
                            Facebook
                        </a>
                    @endforeach
                </span>
                পেইজের সাথে যুক্ত থাকুন 🔥
            </div>
            <img src="{{ $setup->logo_url ?? '' }}" alt="Logo" width="80" height="80"
                class="w-20 mx-auto mb-6 brightness-200">
            <p>© {{ date('Y') }} {{ $setup->title ?? '' }}. All rights reserved.</p>
        </div>
    </footer>

</body>

</html>
