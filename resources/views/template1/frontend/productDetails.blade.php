@extends('template1.layouts.front')

@section('content')
    <section class="container mx-auto px-4">

        <!-- 1. Breadcrumb -->
        <nav
            class="flex items-center space-x-2 text-sm text-gray-500 mb-6 overflow-x-auto whitespace-nowrap pb-2 no-scrollbar">
            <a href="/" class="hover:text-[#FF6A00]">Home</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <a href="#" class="hover:text-[#FF6A00]">Electronics</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-[#F97316]">G63 Speaker Lamp - Multi-Function Bluetooth Speaker With RGB Light</span>
        </nav>

        <!-- 2. Product Top Info Card (Image & Details) -->
        <div class="overflow-hidden mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">

                <!-- Left: Image Gallery -->
                <div class="border-r border-gray-50">
                    <div
                        class="aspect-square mb-4 overflow-hidden rounded-xl bg-[#E8F0FE] border border-blue-50 relative group">
                        <img id="mainImage"
                            src="https://orenmart.sgp1.digitaloceanspaces.com/product/f612deb4-7d5c-401e-8b82-d9d7458b5c49.jpg"
                            alt="G63 Speaker"
                            class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="grid grid-cols-4 gap-3">
                        <button class="aspect-square rounded-lg border-2 border-[#FF6A00] p-1 overflow-hidden bg-white">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/f612deb4-7d5c-401e-8b82-d9d7458b5c49.jpg"
                                class="w-full h-full object-contain">
                        </button>
                        <button
                            class="aspect-square rounded-lg border border-gray-200 p-1 overflow-hidden bg-white hover:border-[#FF6A00] transition-colors">
                            <img src="https://via.placeholder.com/200x200/E8F0FE/000000?text=Product+2"
                                class="w-full h-full object-contain">
                        </button>
                    </div>
                </div>

                <!-- Right: Product Purchase Details -->
                <div class="p-6 md:p-8">
                    <span class="inline-block bg-[#FFCF00] text-black text-[13px] font-bold px-3 py-1 rounded-full mb-4">১৩%
                        ছাড়</span>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 leading-tight">G63 Speaker Lamp -
                        Multi-Function Bluetooth Speaker With RGB Light</h1>
                    <div class="flex items-center gap-2 mb-6 text-sm text-gray-600"><span
                            class="uppercase font-bold">SKU</span>:<span class=" font-mono">G63-SL-001-BL-SM</span></div>

                    <div class="flex items-baseline gap-4 mb-6">
                        <span class="text-gray-400 text-lg line-through">৳৭৯৩</span>
                        <span class="text-3xl font-black text-[#00A651]">৳৬৯০</span>
                    </div>

                    <div class="mb-5">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">রঙ নির্বাচন করুন:</h3>
                        <div class="flex flex-wrap gap-2">
                            <!-- Active Color -->
                            <button
                                class="px-3 py-2 rounded-lg border-2 border-[#FF6A00] bg-[#FFF8F4] min-w-[90px] text-center">
                                <div class="text-md font-medium text-[#FF6A00]">নীল</div>
                                <div class="text-xs text-gray-400">G63-SL-001-BL</div>
                            </button>
                            <!-- Inactive Colors -->
                            <button
                                class="px-3 py-2 rounded-lg border-2 border-gray-100 bg-white min-w-[90px] text-center hover:border-gray-300 transition-colors">
                                <div class="text-md font-medium text-gray-700">লাল</div>
                                <div class="text-xs text-gray-400">G63-SL-001-RD</div>
                            </button>
                            <button
                                class="px-3 py-2 rounded-lg border-2 border-gray-100 bg-white min-w-[90px] text-center hover:border-gray-300 transition-colors">
                                <div class="text-md font-medium text-gray-700">সাদা</div>
                                <div class="text-xs text-gray-400">G63-SL-001-WH</div>
                            </button>
                            <button
                                class="px-3 py-2 rounded-lg border-2 border-gray-100 bg-white min-w-[90px] text-center hover:border-gray-300 transition-colors">
                                <div class="text-md font-medium text-gray-700">কালো</div>
                                <div class="text-xs text-gray-400">G63-SL-001-BK</div>
                            </button>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">সাইজ নির্বাচন করুন:</h3>
                        <div class="flex flex-wrap gap-2">
                            <!-- Active Size -->
                            <button
                                class="px-4 py-2 rounded-lg border-2 border-[#FF6A00] bg-[#FFF8F4] min-w-[75px] text-center">
                                <div class="text-md font-medium text-[#FF6A00]">ছোট</div>
                                <div class="text-sm text-gray-500">৳৬৯০</div>
                                <div class="text-xs text-gray-400">SM</div>
                            </button>
                            <!-- Inactive Sizes -->
                            <button
                                class="px-4 py-2 rounded-lg border-2 border-gray-100 bg-white min-w-[75px] text-center hover:border-gray-300 transition-colors">
                                <div class="text-md font-medium text-gray-700">মাঝারি</div>
                                <div class="text-sm text-gray-500">৳৭৯০</div>
                                <div class="text-xs text-gray-400">MD</div>
                            </button>
                            <button
                                class="px-4 py-2 rounded-lg border-2 border-gray-100 bg-white min-w-[75px] text-center hover:border-gray-300 transition-colors">
                                <div class="text-md] font-medium text-gray-700">বড়</div>
                                <div class="text-sm text-gray-500">৳৮৯০</div>
                                <div class="text-xs text-gray-400">LG</div>
                            </button>
                        </div>
                    </div>

                    <!-- Quantity -->
                    <div class="flex items-center gap-6 mb-8">
                        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden h-10">
                            <button class="px-3 hover:bg-gray-50 transition-colors border-r border-gray-100"><i
                                    class="fas fa-minus text-[10px] text-gray-400"></i></button>
                            <span class="px-5 text-sm font-bold text-gray-700">১</span>
                            <button class="px-3 hover:bg-gray-50 transition-colors border-l border-gray-100"><i
                                    class="fas fa-plus text-[10px] text-gray-400"></i></button>
                        </div>
                        <span class="text-sm text-gray-400">১৫ টি স্টকে আছে</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8 text-sm">
                        <button
                            class="bg-[#00A651] hover:bg-green-700 text-white h-12 rounded-xl font-bold flex items-center justify-center gap-3 shadow-md shadow-green-50"><i
                                class="fas fa-shopping-cart text-sm"></i> Add To Cart</button>
                        <button
                            class="bg-[#FFCF00] hover:bg-yellow-500 text-black h-12 rounded-xl font-bold flex items-center justify-center gap-3 shadow-md shadow-yellow-50">Order
                            Now</button>
                    </div>


                    <!-- Trust Icons (From image) -->
                    <div class="space-y-3 text-md mb-6">
                        <div class="text-[#00A651] flex items-center gap-2"><i class="fas fa-shipping-fast"></i> ১৫০০ টাকার
                            উপর অর্ডার করলে ফ্রি ডেলিভারি</div>
                        <div class="text-[#3B82F6] flex items-center gap-2"><i class="fas fa-check-circle"></i> স্টক শেষ
                            হওয়ার আগেই অর্ডার করুন!</div>
                        <div class="text-[#9333EA] flex items-center gap-2"><i class="fas fa-hand-holding-usd"></i>
                            প্রোডাক্ট হাতে পেয়ে মূল্য পরিশোধ করুন!</div>
                        <div class="text-[#F15A24] flex items-center gap-2"><i class="fas fa-star"></i> ৭২ ঘণ্টার মধ্যে সারা
                            বাংলাদেশ এ হোম ডেলিভারি</div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg text-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-rotate-ccw h-5 w-5 text-orange-500">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <path d="M3 3v5h5"></path>
                            </svg>
                            <div class="text-[12px] md:text-sm font-medium">Easy Return</div>
                        </div>
                        <div class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg text-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-star h-5 w-5 text-orange-500">
                                <path
                                    d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z">
                                </path>
                            </svg>
                            <div class="text-[12px] md:text-sm font-medium">Best Quality</div>
                        </div>
                        <div class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg text-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-truck h-5 w-5 text-orange-500">
                                <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                                <path d="M15 18H9"></path>
                                <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14">
                                </path>
                                <circle cx="17" cy="18" r="2"></circle>
                                <circle cx="7" cy="18" r="2"></circle>
                            </svg>
                            <div class="text-[12px] md:text-sm font-medium">Fast Shipping</div>
                        </div>
                    </div>
                    <div class="text-center mb-4 text-gray-700">সরাসরি অর্ডার করতে কল অথবা হোয়াটসঅ্যাপ করুন</div>

                    <div class="grid grid-cols-2 gap-3">
                        <a href="#"
                            class="bg-[#EE4D2D] text-white h-11 rounded-xl flex items-center justify-center gap-3 font-bold"><i
                                class="fas fa-phone-alt"></i> কল করুন</a>
                        <a href="#"
                            class="bg-[#25D366] text-white h-11 rounded-xl flex items-center justify-center gap-3 font-bold"><i
                                class="fab fa-whatsapp text-lg"></i> হোয়াটসঅ্যাপ</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. TABS SECTION -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Tab Buttons -->
            <div class="flex items-center justify-center border-b border-gray-100 bg-[#F9FAFB]" id="tabs-nav">
                <button onclick="switchTab('description')" id="tab-btn-description"
                    class="tab-btn px-8 py-4 text-sm transition-all border-b-2 border-[#FF6A00] text-gray-900 bg-white">বিবরণ</button>
                <button onclick="switchTab('specification')" id="tab-btn-specification"
                    class="tab-btn px-8 py-4 text-sm transition-all border-b-2 border-transparent text-gray-600 hover:text-gray-900">স্পেসিফিকেশন</button>
                <button onclick="switchTab('review')" id="tab-btn-review"
                    class="tab-btn px-8 py-4 text-sm transition-all border-b-2 border-transparent text-gray-600 hover:text-gray-900">রিভিউ
                    (42)</button>
            </div>

            <!-- Tab Content Area -->
            <div class="p-6 md:p-10">

                <!-- Section: Description -->
                <div id="tab-content-description" class="tab-content block">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">পণ্যের বিবরণ</h3>
                    <p class="text-gray-600 leading-relaxed mb-6">স্মার্ট লাইট সাউন্ড মেশিন - ওয়্যারলেস চার্জিং, ৯ রঙের
                        অপশন, FM রেডিও, দীর্ঘ স্ট্যান্ডবাই টাইম এবং অ্যালার্ম ক্লক সহ।</p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-gray-700 font-medium"><i
                                class="fas fa-circle text-[6px] text-[#FF6A00]"></i> ওয়্যারলেস চার্জিং সুবিধা</li>
                        <li class="flex items-center gap-3 text-gray-700 font-medium"><i
                                class="fas fa-circle text-[6px] text-[#FF6A00]"></i> ৯টি রঙের LED লাইট অপশন</li>
                        <li class="flex items-center gap-3 text-gray-700 font-medium"><i
                                class="fas fa-circle text-[6px] text-[#FF6A00]"></i> FM রেডিও ও ব্লুটুথ কানেক্টিভিটি</li>
                        <li class="flex items-center gap-3 text-gray-700 font-medium"><i
                                class="fas fa-circle text-[6px] text-[#FF6A00]"></i> দীর্ঘ ব্যাটারি লাইফ</li>
                        <li class="flex items-center gap-3 text-gray-700 font-medium"><i
                                class="fas fa-circle text-[6px] text-[#FF6A00]"></i> অ্যালার্ম ক্লক ফিচার</li>
                        <li class="flex items-center gap-3 text-gray-700 font-medium"><i
                                class="fas fa-circle text-[6px] text-[#FF6A00]"></i> টাচ কন্ট্রোল সিস্টেম</li>
                    </ul>
                </div>

                <!-- Section: Specification  -->
                <div id="tab-content-specification" class="tab-content hidden">
                    <h3 class="text-xl font-bold text-gray-900 mb-8">পণ্যের স্পেসিফিকেশন</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4 text-md text-gray-800">
                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <span>ব্র্যান্ড:</span>
                            <span>G63</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <span>কালার:</span>
                            <span>মাল্টি কালার RGB</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <span>কানেক্টিভিটি:</span>
                            <span>ব্লুটুথ ৫.০, FM রেডিও</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <span>ব্যাটারি:</span>
                            <span>২০০০mAh লিথিয়াম</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <span>চার্জিং:</span>
                            <span>ওয়্যারলেস + USB-C</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <span>ওয়ারেন্টি:</span>
                            <span>১ বছর সার্ভিস ওয়ারেন্টি</span>
                        </div>
                    </div>
                </div>

                <!-- Section: Review -->
                <div id="tab-content-review" class="tab-content hidden">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">কাস্টমার রিভিউ</h3>

                    <!-- Review Summary Card -->
                    <div class="bg-gray-50/50 rounded-xl p-6 mb-10 border border-gray-100">
                        <div class="text-3xl font-bold text-orange-500 mb-1">4.8</div>
                        <div class="flex text-yellow-400 text-sm mb-1 gap-0.5">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                class="fas fa-star"></i><i class="fas fa-star-half-alt text-gray-300"></i>
                        </div>
                        <div class="text-sm text-gray-800 font-medium">42 রিভিউ</div>
                    </div>

                    <!-- Individual Reviews List -->
                    <div class="space-y-0">

                        <!-- Review 1 -->
                        <div class="py-8 border-b border-gray-100 last:border-0">
                            <div class="flex items-start gap-4">
                                <!-- Avatar -->
                                <div
                                    class="w-10 h-10 bg-orange-500 text-white rounded-full flex items-center justify-center text-md font-bold shrink-0">
                                    রহ</div>

                                <div class="flex-1">
                                    <!-- Name, Stars and Date on Same Line -->
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <h4 class="text-md text-gray-800">রহিম উদ্দিন</h4>
                                        <div class="flex text-yellow-400 text-[10px] gap-0.5">
                                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                                class="fas fa-star"></i><i class="fas fa-star"></i><i
                                                class="fas fa-star"></i>
                                        </div>
                                        <span class="text-sm text-gray-500">২ দিন আগে</span>
                                    </div>

                                    <!-- Comment -->
                                    <p class="text-md text-gray-600 leading-relaxed mb-3">অসাধারণ পণ্য! সাউন্ড কোয়ালিটি
                                        খুবই ভালো এবং লাইট খুব সুন্দর। দাম অনুযায়ী খুবই ভালো পণ্য। সবার কাছে রিকমেন্ড করব।
                                    </p>

                                    <!-- Variant Tag -->
                                    <span
                                        class="inline-block bg-gray-100 text-gray-500 text-xs px-2.5 py-1 rounded-sm font-medium">নীল
                                        - মাঝারি</span>
                                </div>
                            </div>
                        </div>

                        <!-- Review 2 -->
                        <div class="py-8 border-b border-gray-100 last:border-0">
                            <div class="flex items-start gap-4">
                                <!-- Avatar -->
                                <div
                                    class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center text-md font-bold shrink-0">
                                    ফা</div>

                                <div class="flex-1">
                                    <!-- Name, Stars and Date -->
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <h4 class="text-md text-gray-900">ফাতেমা খাতুন</h4>
                                        <div class="flex text-yellow-400 text-[10px] gap-0.5">
                                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                                class="fas fa-star"></i><i class="fas fa-star"></i><i
                                                class="far fa-star text-gray-300"></i>
                                        </div>
                                        <span class="text-sm text-gray-500">১ সপ্তাহ আগে</span>
                                    </div>

                                    <!-- Comment -->
                                    <p class="text-md text-gray-600 leading-relaxed mb-3">ভালো পণ্য। তবে ব্যাটারি লাইফ
                                        আরেকটু বেশি হলে ভালো হতো। তবুও সন্তুষ্ট।</p>

                                    <!-- Variant Tag -->
                                    <span
                                        class="inline-block bg-gray-100 text-gray-500 text-xs px-2.5 py-1 rounded-sm font-medium">লাল
                                        - ছোট</span>
                                </div>
                            </div>
                        </div>
                        <!-- Review 2 -->
                        <div class="py-8 border-b border-gray-100 last:border-0">
                            <div class="flex items-start gap-4">
                                <!-- Avatar -->
                                <div
                                    class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center text-md font-bold shrink-0">
                                    ফা</div>

                                <div class="flex-1">
                                    <!-- Name, Stars and Date -->
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <h4 class="text-md text-gray-900">ফাতেমা খাতুন</h4>
                                        <div class="flex text-yellow-400 text-[10px] gap-0.5">
                                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                                class="fas fa-star"></i><i class="fas fa-star"></i><i
                                                class="far fa-star text-gray-300"></i>
                                        </div>
                                        <span class="text-sm text-gray-500">১ সপ্তাহ আগে</span>
                                    </div>

                                    <!-- Comment -->
                                    <p class="text-md text-gray-600 leading-relaxed mb-3">ভালো পণ্য। তবে ব্যাটারি লাইফ
                                        আরেকটু বেশি হলে ভালো হতো। তবুও সন্তুষ্ট।</p>

                                    <!-- Variant Tag -->
                                    <span
                                        class="inline-block bg-gray-100 text-gray-500 text-xs px-2.5 py-1 rounded-sm font-medium">লাল
                                        - ছোট</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </section>
@endsection
@push('scripts')
    <script>
        function switchTab(tabId) {
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.add('hidden'));
            contents.forEach(content => content.classList.remove('block'));

            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => {
                btn.classList.remove('text-gray-900', 'border-[#FF6A00]', 'bg-white');
                btn.classList.add('text-gray-400', 'border-transparent');
            });

            const activeContent = document.getElementById('tab-content-' + tabId);
            activeContent.classList.remove('hidden');
            activeContent.classList.add('block');

            const activeBtn = document.getElementById('tab-btn-' + tabId);
            activeBtn.classList.remove('text-gray-400', 'border-transparent');
            activeBtn.classList.add('text-gray-900', 'border-[#FF6A00]', 'bg-white');
        }
    </script>
@endpush
