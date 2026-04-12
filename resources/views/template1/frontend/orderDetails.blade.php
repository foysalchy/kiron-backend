@extends('template1.layouts.front')

@section('content')
    <section class="container mx-auto py-6">
        <!-- Header Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-2 gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.dashboard') }}?section=orders"
                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all">
                    <i class="fas fa-arrow-left text-[10px]"></i> Dashboard
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Order Details</h1>
            </div>
            <div class="flex flex-col items-end">
                @php
                    $statusClasses = [
                        'pending' => 'bg-orange-500',
                        'delivered' => 'bg-green-500',
                        'cancelled' => 'bg-red-500',
                    ];
                @endphp
                <span
                    class="px-4 py-1 {{ $statusClasses[$order->status] ?? 'bg-blue-500' }} text-white text-md font-bold rounded-lg mb-1">
                    {{ App\Enums\Status::from($order->status)->label() }}
                </span>
                <p class="text-sm text-gray-500 font-medium">Order Date: {{ $order->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
        <p class="text-md text-gray-500 mb-8">Order Number: #{{ $order->order_no }}</p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left Column (Products & Tracking) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Ordered Products Card -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">Ordered Items</h3>

                    <div class="space-y-4">
                        @foreach($order->orderDetails as $item)
                        <div class="flex flex-col sm:flex-row items-center gap-6 p-4 border border-gray-200 rounded-lg bg-white hover:shadow-sm transition-all">
                            <div class="w-20 h-20 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-50 shrink-0 overflow-hidden">
                                <img src="{{ $item->product->thumbnail_url ?? asset('images/placeholder.jpg') }}" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 text-center sm:text-left">
                                <h4 class="text-md font-bold text-gray-800 mb-1 leading-tight">{{ $item->product->title }}</h4>

                                @if($item->variation)
                                <div class="flex flex-wrap justify-center sm:justify-start gap-2 mb-2">
                                    @foreach($item->variation->attributes as $attr)
                                        <span class="px-2 py-0.5 text-[10px] font-bold bg-gray-100 rounded text-gray-600 uppercase">{{ $attr->attributeValue->name }}</span>
                                    @endforeach
                                </div>
                                <p class="text-[10px] text-gray-400 font-bold mb-2 uppercase tracking-tighter">SKU: {{ $item->variation->sku ?? 'N/A' }}</p>
                                @endif

                                <p class="text-gray-900 font-medium">
                                    <span class="text-sm">৳{{ number_format($item->unit_price) }} × {{ $item->quantity }}</span>
                                    <span class="text-lg font-bold text-[#FF6A00] ml-3">৳{{ number_format($item->total) }}</span>
                                </p>
                            </div>

                            @if($order->status == 'delivered')
                                <button class="px-4 py-2 border border-[#FF6A00] text-[#FF6A00] rounded-lg text-xs font-bold hover:bg-orange-50 transition-all flex items-center gap-2">
                                    <i class="far fa-star"></i> Write Review
                                </button>
                            @else
                                <button disabled class="px-4 py-2 bg-gray-50 text-gray-300 rounded-lg text-[10px] font-bold flex items-center gap-2 cursor-not-allowed">
                                    <i class="fas fa-lock"></i> Locked
                                </button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order Tracking Section -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden p-6">
                    <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-8">Order Tracking</h3>

                    <!-- Vertical Timeline -->
                    <div class="relative pl-8 space-y-8  before:bg-green-500">
                        <!-- Step 1 -->
                        <div class="relative">
                            <div
                                class="absolute -left-8 top-2 w-4 h-4 rounded-full bg-green-500 flex items-center justify-center z-10">
                            </div>
                            <p class="font-medium text-md text-green-700 leading-none mb-1">Order Placed</p>
                            <p class="text-sm text-gray-600 font-base">2024-01-15 10:30 AM</p>
                        </div>
                        <!-- Step 2 -->
                        <div class="relative">
                            <div
                                class="absolute -left-8 top-2 w-4 h-4 rounded-full bg-green-500 flex items-center justify-center z-10">
                            </div>
                            <p class="font-medium text-md text-green-700 leading-none mb-1">Order Confirmed</p>
                            <p class="text-sm text-gray-600 font-base">2024-01-15 11:00 AM</p>
                        </div>
                        <!-- Step 3 -->
                        <div class="relative">
                            <div
                                class="absolute -left-8 top-2 w-4 h-4 rounded-full bg-green-500 flex items-center justify-center z-10">
                            </div>
                            <p class="font-medium text-md text-green-700 leading-none mb-1">Processing</p>
                            <p class="text-sm text-gray-600 font-base">2024-01-15 02:00 PM</p>
                        </div>
                        <!-- Step 4 -->
                        <div class="relative">
                            <div
                                class="absolute -left-8 top-2 w-4 h-4 rounded-full bg-green-500 flex items-center justify-center z-10">
                            </div>
                            <p class="font-medium text-md text-green-700 leading-none mb-1">Shipped</p>
                            <p class="text-sm text-gray-600 font-base">2024-01-16 09:00 AM</p>
                        </div>
                        <!-- Step 5 -->
                        <div class="relative">
                            <div
                                class="absolute -left-8 top-2 w-4 h-4 rounded-full bg-green-500 flex items-center justify-center z-10">
                            </div>
                            <p class="font-medium text-md text-green-700 leading-none mb-1">Out for Delivery</p>
                            <p class="text-sm text-gray-600 font-base">2024-01-18 08:00 AM</p>
                        </div>
                        <!-- Step 6 (Final) -->
                        <div class="relative">
                            <div
                                class="absolute -left-8 top-2 w-4 h-4 rounded-full bg-green-500 flex items-center justify-center z-10">
                            </div>
                            <p class="font-medium text-md text-green-700 leading-none mb-1">Delivered</p>
                            <p class="text-sm text-gray-600 font-base">2024-01-18 03:30 PM</p>
                        </div>
                    </div>

                    <!-- Courier Box -->
                    <div
                        class="mt-10 p-4 bg-blue-50 border border-blue-100 rounded-xl flex flex-col md:flex-row items-center justify-between gap-4">
                        <div>
                            <h5 class="text-blue-800 text-md mb-1">কুরিয়ার ট্র্যাকিং</h5>
                            <p class="text-sm text-blue-700">Sundarban Courier - SA123456789BD</p>
                        </div>
                        <button
                            class="w-full md:w-auto px-6 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-700 transition-all flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-external-link h-4 w-4 mr-2">
                                <path d="M15 3h6v6"></path>
                                <path d="M10 14 21 3"></path>
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            </svg>
                            ট্র্যাক করুন
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column (Info Cards) -->
            <div class="space-y-6">

                <!-- Customer Information -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                    <h3 class="text-lg md:text-2xl font-bold text-gray-900 mb-6">Customer Information</h3>
                    <div class="space-y-4">
                        <div class="flex items-start ">
                            <div class="w-10 h-10 flex items-center justify-center text-gray-400 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-map-pin h-4 w-4 text-gray-500">
                                    <path
                                        d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0">
                                    </path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-900 text-md">{{ $order->customer->name }}</p>
                                <p class="text-sm text-gray-500 leading-relaxed font-medium">{{ $order->customer->address }}</p>
                            </div>
                        </div>
                        <div class="flex items-center ">
                            <div class="w-10 h-10 flex items-center justify-center text-gray-400 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-phone h-4 w-4 text-gray-500">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                    </path>
                                </svg>
                            </div>
                            <p class="text-gray-900 text-md">{{ $order->customer->phone }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary -->
                 <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                    <h3 class="text-lg md:text-2xl font-bold text-gray-800 mb-6 border-b border-gray-50 pb-3">Payment Summary</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between text-md text-gray-600 font-medium">
                            <span>Subtotal:</span>
                            <span class="text-gray-900 font-bold">৳{{ number_format($order->subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-md text-gray-600 font-medium">
                            <span>Shipping Charge:</span>
                            <span class="text-gray-900 font-bold">৳{{ number_format($order->other_charges) }}</span>
                        </div>
                        @if($order->coupon_discount > 0)
                        <div class="flex justify-between text-md text-green-600 font-medium">
                            <span>Discount:</span>
                            <span class="font-bold">- ৳{{ number_format($order->coupon_discount) }}</span>
                        </div>
                        @endif
                        <div class="pt-4 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-gray-800 font-black">Total:</span>
                            <span class="text-xl font-black text-[#FF6A00]">৳{{ number_format($order->grand_total) }}</span>
                        </div>
                        <p class="text-[11px] text-gray-400 font-bold uppercase mt-2">Method: {{ str_replace('_', ' ', $order->payment_method ?? 'COD') }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                    <h3 class="text-lg md:text-2xl font-bold text-gray-900 mb-6">Action</h3>
                    <div class="space-y-3">
                        <a href="{{ route('invoice.download', $order->id) }}"
                            class="w-full py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-800 hover:border-[#FF6A00] hover:text-[#FF6A00] transition-all flex items-center justify-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-download h-4 w-4 mr-2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" x2="12" y1="15" y2="3"></line>
                            </svg>
                            Invoice Download
                        </a>
                        <a href="{{ route('support.index') }}"
                            class="w-full py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-800 hover:border-[#FF6A00] hover:text-[#FF6A00] transition-all flex items-center justify-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-message-square h-4 w-4 mr-2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            Support
                        </a>
                        <button
                            class="w-full py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-800 hover:border-[#FF6A00] hover:text-[#FF6A00] transition-all flex items-center justify-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-package h-4 w-4 mr-2">
                                <path
                                    d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z">
                                </path>
                                <path d="M12 22V12"></path>
                                <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                                <path d="m7.5 4.27 9 5.15"></path>
                            </svg>
                            Return Request
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
