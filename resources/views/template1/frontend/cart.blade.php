@extends('template1.layouts.front')

@section('content')
    <section class="container mx-auto py-8 px-4">
        @if (\Gloudemans\Shoppingcart\Facades\Cart::count() > 0)
            <!-- Top Header -->
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-800">
                    শপিং কার্ট ({{ \Gloudemans\Shoppingcart\Facades\Cart::count() }} টি পণ্য)
                </h1>
                <a href="{{ route('shop.index') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded text-sm font-medium text-gray-600 hover:bg-gray-50 transition-all">
                    ← শপিং চালিয়ে যান
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">
                        <div class="p-5">
                            <h2 class="text-xl font-bold text-gray-800">কার্টের পণ্যসমূহ</h2>
                        </div>

                        <div class="p-5 space-y-6">
                            @foreach ($cartContent as $item)
                                <div class="flex flex-col md:flex-row items-center gap-6 p-4 border border-gray-200 rounded-lg relative group">
                                    <!-- Image -->
                                    <div class="w-24 h-24 bg-gray-50 rounded-lg overflow-hidden shrink-0 ">
                                        <img src="{{ $item->options->thumbnail }}"
                                             onerror="this.src='{{ asset('./images/template1/frontend/default.webp') }}'"
                                             class="w-full h-full object-cover">
                                    </div>

                                    <!-- Details -->
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-800 text-lg leading-tight mb-1">{{ $item->name }}</h3>
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="bg-orange-50 text-[#FF6A00] text-[10px] font-bold px-2 py-0.5 rounded uppercase">
                                                {{ $item->options->variant ?? 'Product' }}
                                            </span>
                                        </div>
                                        <div class="flex items-baseline gap-2">
                                            <p class="font-bold text-gray-700 text-lg">
                                                ৳{{ number_format($item->price, 0) }}
                                            </p>

                                            @if(isset($item->options['regular_price']) && (float)$item->options['regular_price'] > (float)$item->price)
                                                <span class="text-sm text-gray-400 line-through font-normal pl-1">
                                                    ৳{{ number_format($item->options['regular_price'], 0) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Qty Controls -->
                                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white shadow-xs">
                                        <button onclick="updateCartQty('{{ $item->rowId }}', {{ $item->qty - 1 }})" class="px-3 py-2 text-gray-600 text-xl hover:text-[#FF6A00] hover:bg-gray-50 transition-colors">-</button>
                                        <span class="w-10 text-center font-bold text-gray-800">{{ $item->qty }}</span>
                                        <button onclick="updateCartQty('{{ $item->rowId }}', {{ $item->qty + 1 }})" class="px-3 py-2 text-gray-600 text-xl hover:text-[#FF6A00] hover:bg-gray-50 transition-colors">+</button>
                                    </div>

                                    <!-- Price & Delete -->
                                    <div class="flex flex-col items-center md:items-end gap-4 min-w-[100px]">
                                        <p class="font-black text-xl text-[#FF6A00]">৳{{ number_format($item->subtotal, 0) }}</p>
                                        <a href="{{ route('cart.remove', $item->rowId) }}"
                                        class="text-red-400 hover:text-red-600 transition-colors">
                                            <i class="far fa-trash-alt text-lg"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6 sticky top-24">
                        <h2 class="text-xl font-bold text-gray-800 mb-6">অর্ডার সামারি</h2>

                        <!-- Coupon Section -->
                        <form action="{{ route('coupon.apply') }}" method="POST" class="mb-6">
                            @csrf
                            <label class="text-sm font-bold text-gray-600 block mb-2">কুপন কোড</label>
                            <div class="flex gap-2">
                                <input type="text" name="coupon_code" placeholder="কুপন কোড লিখুন"
                                       value="{{ session()->has('coupon_code') ? session('coupon_code')['coupon_code'] : '' }}"
                                       class="flex-1 border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:border-[#FF6A00] transition-all"
                                       {{ session()->has('coupon') ? 'readonly' : '' }}>

                                @if(session()->has('coupon'))
                                    <a href="{{ route('coupon.remove') }}" class="bg-red-500 text-white rounded-lg px-4 py-2.5 hover:bg-red-600 flex items-center">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @else
                                    <button type="submit" class="bg-white border border-gray-200 rounded-lg px-4 py-2.5 hover:bg-gray-50 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-600"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"></path><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle></svg>
                                    </button>
                                @endif
                            </div>
                        </form>

                        <div class="space-y-4 border-t border-gray-50 pt-6 mb-6">
                            <div class="flex justify-between font-bold text-gray-600">
                                <span>সাবটোটাল:</span>
                                <span>৳{{ number_format($subtotal, 0) }}</span>
                            </div>

                            {{-- discount --}}
                            @if($discount > 0)
                                <div class="flex justify-between font-bold text-green-600">
                                    <span>ডিসকাউন্ট ({{ session('coupon')['coupon_code'] }}):</span>
                                    <span>- ৳{{ number_format($discount, 0) }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between font-bold text-gray-600">
                                <span>ডেলিভারি চার্জ:</span>
                                <span>৳{{ number_format($shipping, 0) }}</span>
                            </div>

                            <!-- Shipping Progress Message -->
                            <div class="flex items-center gap-2 text-sm font-bold text-orange-600">
                                <i class="fas fa-truck"></i>
                                @if($subtotal >= 1000)
                                    <span>অভিনন্দন! আপনি ফ্রি ডেলিভারি পাচ্ছেন।</span>
                                @else
                                    <span>আরো ৳{{ number_format(1000 - $subtotal, 0) }} কিনলে ফ্রি ডেলিভারি!</span>
                                @endif
                            </div>
                        </div>

                                        <div class="border-t border-gray-200 pt-6 mb-8 flex justify-between items-center">
                            <span class="text-lg font-black text-gray-800">মোট:</span>
                            <span class="text-2xl font-black text-[#FF6A00]">৳{{ number_format($total, 0) }}</span>
                        </div>

                        <!-- Checkout Button -->
                        <a href="{{ url('/checkout') }}"
                        class="block w-full text-center bg-[#FF6A00] hover:bg-[#e65f00] text-white py-3.5 rounded-xl font-bold text-lg shadow-lg transition-all mb-4">
                            চেকআউট করুন
                        </a>

                        <p class="text-center text-gray-500 text-sm font-medium flex items-center justify-center gap-2 mb-8">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck h-4 w-4"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path><path d="M15 18H9"></path><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path><circle cx="17" cy="18" r="2"></circle><circle cx="7" cy="18" r="2"></circle></svg>
                             ২-৩ দিনে ডেলিভারি
                        </p>

                        <!-- Trust Badges -->
                        <div class="grid grid-cols-2 gap-y-4 gap-x-2 border-t border-gray-50 pt-8">
                            <div class="flex items-center gap-2 text-[11px] font-bold text-gray-500 uppercase">
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span> নিরাপদ পেমেন্ট
                            </div>
                            <div class="flex items-center gap-2 text-[11px] font-bold text-gray-500 uppercase">
                                <span class="w-2 h-2 bg-blue-400 rounded-full"></span> ফ্রি রিটার্ন
                            </div>
                            <div class="flex items-center gap-2 text-[11px] font-bold text-gray-500 uppercase">
                                <span class="w-2 h-2 bg-orange-400 rounded-full"></span> ২৪/৭ সাপোর্ট
                            </div>
                            <div class="flex items-center gap-2 text-[11px] font-bold text-gray-500 uppercase">
                                <span class="w-2 h-2 bg-purple-400 rounded-full"></span> গুণগত মান
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-24 bg-white rounded-3xl border border-dashed border-gray-200">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-shopping-basket text-3xl text-gray-200"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">আপনার কার্টটি বর্তমানে খালি!</h2>
                <a href="{{ route('shop.index') }}" class="inline-block mt-8 bg-[#FF6A00] text-white px-10 py-3 rounded-xl font-bold">কেনাকাটা শুরু করুন</a>
            </div>
        @endif
    </section>

    <!-- Hidden form for updates -->
    <form id="update-cart-form" action="{{ route('cart.update') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="rowId" id="update-row-id">
        <input type="hidden" name="qty" id="update-qty">
    </form>
@endsection

@push('scripts')
    <script>
        function updateCartQty(rowId, newQty) {
            if (newQty < 1) return;
            document.getElementById('update-row-id').value = rowId;
            document.getElementById('update-qty').value = newQty;
            document.getElementById('update-cart-form').submit();
        }
    </script>
@endpush
