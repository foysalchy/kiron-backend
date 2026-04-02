@extends('template1.layouts.front')

@section('content')
    <section class="container mx-auto ">
        <h1 class="text-2xl font-black text-gray-900 mb-8 tracking-tight">চেকআউট</h1>
        <form>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- LEFT COLUMN: Customer & Payment Info -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- 1. Customer Information Card -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                        <div class="p-6  ">
                            <h2 class="text-2xl font-semibold text-gray-800 leading-tight">
                                অর্ডারটি কনফার্ম করতে আপনার নাম, ঠিকানা, মোবাইল নম্বর, নিয়ে অর্ডার কনফার্ম বাটনে ক্লিক করুন
                            </h2>
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">আপনার নাম <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" placeholder="আপনার পূর্ণ নাম লিখুন" required
                                        class="w-full px-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#FF6A00] focus:ring-4 focus:ring-orange-50 transition-all">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">ফোন নম্বর <span
                                            class="text-red-500">*</span></label>
                                    <input type="tel" placeholder="আপনার মোবাইল নম্বর" required
                                        class="w-full px-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#FF6A00] focus:ring-4 focus:ring-orange-50 transition-all">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">আপনার ঠিকানা</label>
                                <textarea placeholder="আপনার ঠিকানা" rows="3" required
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 outline-none focus:border-[#FF6A00] focus:ring-4 focus:ring-orange-50 transition-all"></textarea>
                            </div>

                            <!-- Checkboxes -->
                            <div class="space-y-3 pt-2">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" checked class="w-4 h-4 accent-black rounded-full">
                                    <span class="text-sm font-medium text-gray-600 group-hover:text-gray-900">Billing
                                        address</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" class="w-4 h-4 accent-[#FF6A00]">
                                    <span class="text-sm font-medium text-gray-600 group-hover:text-gray-900">Create an
                                        account?</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Payment Method Card -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-xl font-bold text-gray-800">পেমেন্ট মেথড নির্বাচন করুন</h2>
                        </div>
                        <div class="space-y-3 p-6">
                            <!-- Option 1: Cash on Delivery -->
                            <label
                                class="flex items-center space-x-4 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-all group">
                                <input type="radio" name="payment" value="cod" checked
                                    class="w-4 h-4 text-black border-gray-300 focus:ring-0 accent-black">

                                <!-- Icon Placeholder (Grey Box like image) -->
                                <div
                                    class="w-7 h-7 bg-gray-100 rounded flex items-center justify-center border border-gray-50">
                                    <div class="w-1.5 h-1.5 bg-gray-300 rounded-full"></div>
                                </div>

                                <span class="text-[15px] font-medium text-gray-900">ক্যাশ অন ডেলিভারি</span>
                            </label>

                            <!-- Option 2: bKash -->
                            <label
                                class="flex items-center space-x-4 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-all group">
                                <input type="radio" name="payment" value="bkash"
                                    class="w-4 h-4 text-black border-gray-300 focus:ring-0 accent-black">

                                <!-- Icon Placeholder -->
                                <div
                                    class="w-7 h-7 bg-gray-100 rounded flex items-center justify-center border border-gray-50">
                                    <div class="w-1.5 h-1.5 bg-gray-300 rounded-full"></div>
                                </div>

                                <span class="text-[15px] font-medium text-gray-900">bKash</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Delivery & Order Summary (Exact Image Match) -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6 sticky top-24">

                        <!-- 1. Delivery Method Selection -->
                        <div class="mb-8">
                            <h2 class="text-2xl font-semibold leading-none tracking-tight mb-6">ডেলিভারি মেথড নির্বাচন করুন
                            </h2>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="delivery_method" checked class="w-4 h-4 accent-black">
                                    <span
                                        class="text-sm font-medium text-gray-700 group-hover:text-black transition-colors">ফ্রি
                                        ডেলিভারি</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="delivery_method" class="w-4 h-4 accent-black">
                                    <span
                                        class="text-sm font-medium text-gray-700 group-hover:text-black transition-colors">এক্সপ্রেস
                                        ডেলিভারি (৳১০০)</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="delivery_method" class="w-4 h-4 accent-black">
                                    <span
                                        class="text-sm font-medium text-gray-700 group-hover:text-black transition-colors">স্ট্যান্ডার্ড
                                        ডেলিভারি (৳৬০)</span>
                                </label>
                            </div>
                        </div>

                        <!-- 2. Product Row in Summary -->
                        <div class="bg-[#F9FAFB] rounded-xl p-4 flex items-center gap-4 mb-8">
                            <!-- Product Placeholder Icon/Image -->
                            <div class="w-16 h-16 bg-[#E5E7EB] rounded-lg flex items-center justify-center shrink-0">
                                <div class="w-1.5 h-1.5 bg-gray-300 rounded-full shadow-[0_0_0_4px_rgba(209,213,219,0.3)]">
                                </div>
                            </div>

                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-800 leading-tight mb-1">Car Shape Mobile Holder -
                                    Universal</h4>
                                <p class="text-[#FF6A00] font-semibold text-md">৳750</p>
                            </div>

                            <!-- Quantity & Delete -->
                            <div class="flex items-center gap-4">
                                <div class="flex items-center overflow-hidden">
                                    <button type="button"
                                        class="p-2 hover:bg-gray-50 text-xs font-bold text-gray-600 border bg-white border-gray-200 rounded-md">
                                        <i class="fas fa-minus "></i>
                                    </button>
                                    <span class="px-3 text-sm font-bold text-gray-700">1</span>

                                    <button type="button"
                                        class="p-2 hover:bg-gray-50 text-xs font-bold text-gray-600 border bg-white border-gray-200 rounded-md">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <button type="button" class="text-red-500 hover:text-red-700 transition-colors">
                                    <i class="far fa-trash-alt text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 3. Cost Breakdown -->
                        <div class="space-y-4 border-t border-gray-100 pt-6">
                            <div class="flex justify-between items-center text-gray-700">
                                <span class="text-md font-medium">সবমোট:</span>
                                <span class="text-md font-medium text-gray-900">৳ 750</span>
                            </div>
                            <div class="flex justify-between items-center text-gray-700">
                                <span class="text-md font-medium">ডেলিভারি চার্জ:</span>
                                <span class="text-md font-medium text-gray-900">৳ 0</span>
                            </div>
                            <div class="flex justify-between items-center border-t border-gray-100 pt-4">
                                <span class="text-lg font-black text-gray-900">পরিশোধ করতে হবে:</span>
                                <span class="text-xl  text-[#FF6A00]">৳ 750</span>
                            </div>
                        </div>

                        <!-- 4. Confirm Button -->
                        <button type="submit"
                            class="w-full bg-[#EF4444] hover:bg-red-600 text-white font-semibold py-3 text-sm rounded-lg mt-8 shadow-lg shadow-red-100 transition-all active:scale-[0.98]">
                            অর্ডার কনফার্ম করুন
                        </button>

                    </div>
                </div>

            </div>
            </div>
        </form>
    </section>
@endsection
