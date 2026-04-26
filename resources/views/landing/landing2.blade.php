<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KidzFun - Smart Learning Cards</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;800;900&family=Noto+Sans+Bengali:wght@400;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Noto Sans Bengali', 'Outfit', sans-serif;
        }

        .bg-grid-blue {
            background-color: #f4f9ff;
            background-image:
                linear-gradient(rgba(100, 160, 230, 0.1) 2px, transparent 1px),
                linear-gradient(90deg, rgba(100, 160, 230, 0.1) 2px, transparent 1px);
            background-size: 60px 60px;
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

        /* Grid dark background */
        .bg-grid-dark {
            background-color: #0b1221;
            background-image:
                linear-gradient(rgba(50, 66, 99, 0.4) 1px, transparent 1px),
                linear-gradient(90deg, rgba(30, 60, 120, 0.4) 1px, transparent 1px);
            background-size: 40px 40px;
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

    {{-- ══ LOGO ══ --}}
    <div class="py-5 flex justify-center bg-white shadow-sm">
        <img src="https://kidzfunbd.com/wp-content/uploads/2025/11/Asset-3-scaled-1-1300x281.png" alt="KidzFun Logo"
            class="w-44 md:w-56">
    </div>


    {{-- ══ HERO ══ --}}
    <section class="py-12 px-4 text-center">
        <div class="max-w-5xl mx-auto">

            {{-- Blue badge --}}
            <div
                class="inline-block bg-gradient-to-r from-[#005EFF] to-[#003A9C] text-white px-6 py-3 rounded-xl font-bold text-base md:text-3xl mb-8 shadow-lg ">
                শিশুর মেধা 🧠 এবং সৃজনশীলতা বিকাশে সাহায্য করবে
            </div>

            {{-- Main headline --}}
            <h1 class="text-2xl md:text-5xl font-bold text-[#003A9C] leading-snug mb-10">
                📢 বাংলাদেশে একমাত্র আমরাই দিচ্ছি<br>
                <span class="circle-sketch text-[#003A9C] px-3">
                    <span class="text-red-500">২৫৫ টি কার্ডে ৫১০ টি লেসন</span>📚 একটা ডিভাইসেই বাংলা + ইংরেজি + আরবি অংক সহ অনেক কিছু 👉
            </h1>

            {{-- Product image --}}
            <div class="max-w-2xl mx-auto rounded-3xl overflow-hidden shadow-2xl mb-8">
                <img src="https://kidzfunbd.com/wp-content/uploads/2026/04/255-as-800x800.jpg" alt="Product"
                    class="w-full">
            </div>

            {{-- Stock status --}}
            <div class="space-y-3 mb-8 text-lg md:text-xl font-bold">
                <p>📱 বাচ্চার মোবাইল অ্যাডিকশন কমাবে</p>
                <p>💥 সীমিত স্টক ⏰
                    <span class="bg-[#fc4124] text-white px-3 py-2 rounded-lg ml-1">দেরি করলে মিস</span>
                </p>
            </div>

            {{-- Warranty badge --}}
            <div
                class="inline-block bg-gradient-to-r from-[#EEA727] to-[#FFEF5F] text-black px-8 py-3 rounded-xl font-bold text-2xl mb-14 shadow-lg">
                সাথে ১ বছরের রিপ্লেসমেন্ট ওয়ারেন্টি 😍
            </div>

            {{-- Why best section --}}
            <div class="mb-10">
                <h2 class="text-3xl md:text-5xl font-black leading-snug">
                    এটি কেন আপনার
                    <span class="wavy-underline text-[#fc4124] px-1">
                        সোনামণির জন্য সেরা?
                        <svg viewBox="0 0 500 40" preserveAspectRatio="none">
                            <path d="M3,20c49.3-3,150.7-7.6,199.7-7.4c121.9,0.4,189.9,5,282.3,7.2" stroke="#fc4124" />
                        </svg>
                    </span>
                </h2>
            </div>

            {{-- Category map image --}}
            <div class="max-w-2xl mx-auto mb-14 rounded-2xl overflow-hidden shadow-xl">
                <img src="https://kidzfunbd.com/wp-content/uploads/2026/04/web-ak-bg-800x800.webp" alt="Categories"
                    class="w-full">
            </div>

            {{-- Bullet points --}}
            <div
                class="max-w-3xl mx-auto text-left space-y-4 mb-14 bg-white/60 backdrop-blur-sm p-6 md:p-8 rounded-3xl shadow-sm">
                <div class="flex gap-3 text-base md:text-lg font-bold items-start">
                    <i class="fa-solid fa-circle-check text-green-600 mt-1 flex-shrink-0"></i>
                    ✅ বাজারের সবচেয়ে লেটেস্ট আপডেটে <span class="text-[#fc4124] font-black">৫১০ টি কার্ডে রয়েছে ৪২টি
                        ক্যাটাগরির শব্দ</span>
                </div>
                <div class="flex gap-3 text-base md:text-lg font-bold items-start">
                    <i class="fa-solid fa-circle-check text-green-600 mt-1 flex-shrink-0"></i>
                    ✅ স্মার্ট ও টেকসই ডিজাইন <span class="text-[#005EFF] font-black">লেমিনেটেড কাগজ, সম্পূর্ণ
                        ওয়াটারপ্রুফ</span> খুবই মজবুত
                </div>
                <div class="flex gap-3 text-base md:text-lg font-bold items-start">
                    <i class="fa-solid fa-circle-check text-green-600 mt-1 flex-shrink-0"></i>
                    ✅ ভয়েস রিপিট ফিচার, শিশু যা বলবে বইটি তা-ই রিপিট করবে
                </div>
                <div class="flex gap-3 text-base md:text-lg font-bold items-start">
                    <i class="fa-solid fa-circle-check text-green-600 mt-1 flex-shrink-0"></i>
                    ✅ প্রাথমিক শিক্ষার সকল কিছু যেমন <span class="text-[#005EFF]">বাংলা ও ইংরেজি বর্ণমালা</span> রয়েছে
                </div>
            </div>

        </div>
    </section>


    {{-- ══ BLACK OFFER SECTION ══ --}}
    <section class="bg-grid-dark py-16 px-4 text-center text-white">
        <div class="max-w-3xl mx-auto">

            <h3 class="text-3xl md:text-4xl font-black mb-10">⚡ দেরি করলেই শেষ!</h3>

            <div class="max-w-md mx-auto rounded-3xl overflow-hidden mb-10 border-4 border-[#1f2d4a] shadow-2xl">
                <img src="https://kidzfunbd.com/wp-content/uploads/2026/04/Smart-set-1-800x800.jpg" alt="Final Set"
                    class="w-full">
            </div>

            <div class="space-y-6 mb-12">
                <h4 class="text-2xl md:text-4xl font-black">
                    📢 বর্তমান অফার প্রাইজ
                    <span class="circle-sketch text-yellow-400 px-4">
                        999/=
                        <svg viewBox="0 0 500 150" preserveAspectRatio="none">
                            <path
                                d="M325,18C228.7-8.3,118.5,8.3,78,21C22.4,38.4,4.6,54.6,5.6,77.6c1.4,32.4,52.2,54,142.6,63.7c66.2,7.1,212.2,7.5,273.5-8.3c64.4-16.6,104.3-57.6,33.8-98.2C386.7-4.9,179.4-1.4,126.3,20.7"
                                stroke="#FFEF5F" stroke-width="10" />
                        </svg>
                    </span> টাকা 🔥
                </h4>

                <h4 class="text-xl md:text-3xl font-bold">
                    এবং সারা বাংলাদেশে ডেলিভারি চার্জ
                    <span class="wavy-underline text-red-500 px-1">
                        100/= টাকা
                        <svg viewBox="0 0 500 40" preserveAspectRatio="none">
                            <path d="M5,30c80-10,200-15,300-10s180,8,195,5" stroke="#fc4124" stroke-width="7" />
                        </svg>
                    </span>
                </h4>
            </div>

            <a href="#order"
                class="inline-flex items-center gap-3 bg-[#1f8a54] hover:bg-[#176840] text-white px-6 md:px-10 py-5 rounded-full font-black text-base md:text-xl shadow-xl hover:scale-105 transition-all border-b-8 border-[#124d2f] active:border-b-2 active:translate-y-1">
                <i class="fa-solid fa-circle-arrow-down text-2xl bounce"></i>
                ৫২০০০+ বাবা মা তার বাচ্চার জন্য নিয়েছে ! আপনারটি নিন এখনই 😍
            </a>

        </div>
    </section>


    {{-- ══ ORDER FORM ══ --}}
    <section id="order" class="py-16 px-4 bg-white">
        <x-landing.order-form />
    </section>


    {{-- ══ FOOTER ══ --}}
    <footer class="bg-grid-dark py-16 px-4 text-center text-white border-t border-gray-800">
        <div class="max-w-4xl mx-auto">
            <div class="text-lg md:text-2xl font-black mb-8">
                📢 আমাদের অফিশিয়াল
                <span class="text-blue-400 underline italic">Facebook</span>
                পেইজের সাথে যুক্ত থাকুন 🔥
            </div>
            <img src="https://kidzfunbd.com/wp-content/uploads/2025/11/Asset-3-scaled-1-1300x281.png" alt="Logo"
                class="w-44 mx-auto mb-6 brightness-200">
            <p class="text-gray-400 text-sm">Copyright © 2025 KidzFun. All Rights Reserved.</p>
            <p class="text-gray-500 text-xs mt-2">Designed by Funnel Liner</p>
        </div>
    </footer>




</body>

</html>
