@props([
    'order' => null,
    'setup' => null,
    'relatedProducts' => collect(),
    'templatePrefix' => 'template1'
])

@php
    $setup = $setup ?? \App\Models\SiteSetting::where('company_id', getCurrentCompany()?->company_id)->first();
    $helplinePhone = $setup->phone_number ?? $setup->corporate_phone ?? '01700000000';
    $shopName = $setup->shop_name ?? config('app.name');
@endphp

<div class="px-3 md:px-0 container mx-auto pt-8 md:pt-12 pb-12">
    <!-- Notice Card -->
    <div class="text-center bg-white p-6 md:p-10 rounded-2xl shadow-sm border border-amber-200/80 mb-8 md:mb-12 max-w-3xl mx-auto">
        
        <!-- Warning / Pending Icon -->
        <div class="w-16 h-16 md:w-20 md:h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4 text-amber-600 shadow-inner">
            <svg class="w-8 h-8 md:w-10 md:h-10 text-amber-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs md:text-sm font-semibold bg-amber-50 text-amber-700 border border-amber-200 mb-3">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            অপেক্ষারত অর্ডার (Order Already Placed)
        </span>

        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-2">
            আপনার অর্ডারটি ইতিপূর্বেই গ্রহণ করা হয়েছে!
        </h1>

        @if($order)
            <p class="text-sm md:text-base text-gray-600 mb-6">
                অর্ডার আইডি: <span class="bg-amber-100/80 text-amber-900 font-bold px-2.5 py-0.5 rounded border border-amber-200">#{{ $order->order_no ?? $order->id }}</span>
            </p>
        @else
            <p class="text-sm md:text-base text-gray-600 mb-6">
                আপনি সম্প্রতি আমাদের ওয়েবসাইটে একটি অর্ডার সম্পন্ন করেছেন।
            </p>
        @endif

        <!-- Reassurance & Warning Banner -->
        <div class="bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border border-amber-200 rounded-xl p-5 md:p-6 text-left mb-6 shadow-xs">
            <div class="flex items-start gap-3.5">
                <div class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center shrink-0 font-bold text-sm shadow-xs mt-0.5">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-base md:text-lg font-bold text-amber-900 mb-1">
                        অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করুন
                    </h3>
                    <p class="text-sm md:text-base text-gray-700 leading-relaxed mb-3 font-normal">
                        খুব শীঘ্রই আমাদের কাস্টমার প্রতিনিধি আপনাকে ফোনে কল দিয়ে অর্ডারটি কনফার্ম করবে। বারবার একই অর্ডার সাবমিট করার প্রয়োজন নেই।
                    </p>
                    <div class="text-xs md:text-sm text-amber-800 bg-white/80 p-3 rounded-lg border border-amber-200/70 flex items-start gap-2">
                        <span class="text-amber-600 font-bold shrink-0">💡 তথ্য:</span>
                        <span>আপনার অর্ডারে কোনো পরিবর্তন, নতুন কোনো প্রোডাক্ট যুক্ত করা অথবা ডেলিভারি সম্পর্কিত কোনো প্রশ্ন থাকলে সরাসরি আমাদের হেল্পলাইনে যোগাযোগ করতে পারেন।</span>
                    </div>
                </div>
            </div>
        </div>

        @if($order)
            <!-- Order Snapshot Box -->
            <div class="bg-gray-50 border border-gray-200/80 rounded-xl p-4 md:p-5 mb-8 text-left">
                <h4 class="text-xs md:text-sm font-bold text-gray-500 uppercase tracking-wider mb-3 border-b border-gray-200 pb-2">
                    সাম্প্রতিক অর্ডারের বিবরণ
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 md:gap-4 text-xs md:text-sm">
                    <div>
                        <span class="text-gray-500 block">গ্রাহকের নাম:</span>
                        <span class="font-bold text-gray-800">{{ $order->customer?->name ?? ($order->shipping_address['name'] ?? 'N/A') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">মোবাইল নম্বর:</span>
                        <span class="font-bold text-gray-800">{{ $order->customer?->phone ?? ($order->shipping_address['phone'] ?? 'N/A') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">মোট মূল্য:</span>
                        <span class="font-bold text-[var(--primary-color,#13565e)] text-sm md:text-base">
                            {{ ($setup->currency_position ?? 'left') == 'left' ? ($setup->currency ?? '৳') . number_format($order->grand_total) : number_format($order->grand_total) . ($setup->currency ?? '৳') }}
                        </span>
                    </div>
                    @if(!empty($order->shipping_address['address']))
                        <div class="sm:col-span-2 md:col-span-3">
                            <span class="text-gray-500 block">ঠিকানা:</span>
                            <span class="font-medium text-gray-700">{{ $order->shipping_address['address'] }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 md:gap-4 pt-2">
            @if(!empty($helplinePhone))
                <a href="tel:{{ $helplinePhone }}"
                    class="w-full sm:w-auto px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md shadow-amber-200 transition-all flex items-center justify-center gap-2 text-sm md:text-base">
                    <i class="fas fa-phone-alt"></i>
                    <span>হেল্পলাইনে কল করুন ({{ $helplinePhone }})</span>
                </a>
            @endif

            <a href="{{ route('shop.index') }}"
                class="w-full sm:w-auto px-6 py-3 bg-[var(--primary-color,#13565e)] hover:bg-opacity-90 text-white font-semibold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 text-sm md:text-base">
                <i class="fas fa-shopping-bag"></i>
                <span>আরও কেনাকাটা করুন</span>
            </a>

            @if($order)
                <a href="{{ route('order.invoice', $order->id) }}" target="_blank"
                    class="w-full sm:w-auto px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold rounded-xl transition-all flex items-center justify-center gap-2 text-sm md:text-base border border-gray-200">
                    <i class="fas fa-file-invoice"></i>
                    <span>ইনভয়েস দেখুন</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Related / Recommended Products Section -->
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
    <div class="mt-8 md:mt-12 px-2 md:px-0">
        <div class="flex items-center justify-between mb-4 md:mb-6">
            <h2 class="text-lg md:text-2xl font-bold text-gray-900">অন্যান্য জনপ্রিয় পণ্যসমূহ</h2>
            <a href="{{ route('shop.index') }}" class="text-[var(--primary-color,#13565e)] hover:underline text-xs md:text-sm font-semibold">সবগুলো দেখুন &rarr;</a>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 md:gap-6">
            @foreach($relatedProducts as $product)
                <x-template1.product-card :product="$product" />
            @endforeach
        </div>
    </div>
    @endif
</div>
