@extends('template3.layouts.front')
@section('meta')
     <x-meta-info.meta />
@endsection
@section('content')
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
        <div class="max-w-2xl mx-auto">

            <!-- Page Title (Design same) -->
            <div class="text-center mb-10">
                <h1 class="text-2xl md:text-3xl font-black text-gray-900 mb-4">Track Your Order</h1>
                <p class="text-gray-600 font-medium">Check your order status using your order number</p>
            </div>

            <!-- Tracking Card (Form Updated) -->
            <div class="bg-white rounded-2xl   shadow-xs mb-8 overflow-hidden">
                <h2 class="text-xl md:text-2xl font-bold text-gray-800 text-center p-4">Order Tracking</h2>
                <div class="p-6">
                    <form action="{{ route('order.track') }}" method="GET" class="space-y-5">
                        <div>
                            <label for="orderId" class="block text-gray-700 font-bold mb-2">Order Number</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" name="order_no" id="orderId" placeholder="e.g. SALE-20240115-0001"
                                    required value="{{ request('order_no') }}"
                                    class="w-full pl-11 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#FF6A00]/20 focus:border-[#016738] outline-none transition-all">
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full primary-bg text-primary  font-bold py-3 rounded-lg   transition-all active:scale-[0.98]">
                            Track Order
                        </button>
                    </form>
                </div>
            </div>

            {{-- Tracking Result --}}
            @if ($order)
                <div class="bg-white rounded-2xl border border-[#FF6A00]/20 shadow-xl p-6 mb-8">
                    <div class="flex flex-wrap justify-between items-start gap-3 mb-6 border-b pb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">Current Status:
                                <span class="text-[#FF6A00]">{{ $order->status_label }}</span>
                            </h3>
                            <p class="text-sm text-gray-500">#{{ $order->order_no }}</p>
                        </div>
                        <a href="{{ route('user.order.details', $order->id) }}"
                            class="text-xs font-bold text-blue-600 hover:underline">
                            Detailed View →
                        </a>
                    </div>

                    {{-- Tracking Timeline --}}
                    <div class="relative pl-8 space-y-4">
                        <div class="relative">
                            <div class="absolute -left-8 top-1 w-3 h-3 rounded-full bg-green-500"></div>
                            <p class="font-bold text-sm text-green-700">Order Placed</p>
                            <p class="text-[11px] text-gray-500">{{ $order->created_at->format('d M, Y - h:i A') }}</p>
                        </div>
                        <div class="relative">
                            <div class="absolute -left-8 top-1 w-3 h-3 rounded-full bg-orange-500 animate-pulse"></div>
                            <p class="font-bold text-sm text-orange-600">{{ $order->status_label }}</p>
                            <p class="text-[11px] text-gray-500">Last Update:
                                {{ $order->updated_at->format('d M, Y - h:i A') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Instruction Steps (Design same) -->
            <div class="bg-white rounded-2xl  shadow-xs  mb-8">
                <div class="p-6 border-b border-gray-50 pb-0">
                    <h3 class="text-lg md:text-2xl font-bold text-gray-800">How to Track?</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="flex items-start gap-4">
                        <div
                            class="w-6 h-6  primary-bg text-primary rounded-full flex items-center justify-center text-sm font-bold shrink-0">
                            1</div>
                        <div>
                            <h4 class="font-bold text-gray-900">Find Your Order Number</h4>
                            <p class="text-sm text-gray-600 font-medium">You will find your order number in your order
                                confirmation email or SMS</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div
                            class="w-6 h-6  primary-bg text-primary rounded-full flex items-center justify-center text-sm font-bold shrink-0">
                            2</div>
                        <div>
                            <h4 class="font-bold text-gray-900">Enter Order Number</h4>
                            <p class="text-sm text-gray-600 font-medium">Enter your order number in the box above (e.g.
                                ORD-001)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Guide Section (Design same) -->
            {{-- <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 md:p-12">
                <h2 class="text-lg md:text-2xl font-bold text-gray-900 mb-10">Order Status Guide</h2>
                <div class="space-y-8">
                    <div class="flex items-start gap-3">
                        <div class="text-gray-400 mt-1 shrink-0"><i class="far fa-clock h-5 w-5"></i></div>
                        <div>
                            <h4 class="text-md font-bold text-gray-900">Order Placed</h4>
                            <p class="text-sm text-gray-600 font-medium">Your order has been successfully placed</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="text-[#FF6A00] mt-1 shrink-0"><i class="fas fa-cube h-5 w-5"></i></div>
                        <div>
                            <h4 class="text-md font-bold text-gray-900">Processing</h4>
                            <p class="text-sm text-gray-600 font-medium">Your order is being prepared</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="text-green-500 mt-1 shrink-0"><i class="far fa-check-circle h-5 w-5"></i></div>
                        <div>
                            <h4 class="text-md font-bold text-gray-900">Delivered</h4>
                            <p class="text-sm text-gray-600 font-medium">Your order has been successfully delivered</p>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    </section>
@endsection
