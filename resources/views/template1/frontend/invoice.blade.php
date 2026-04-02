@extends('template1.layouts.front')
@section('content')
    <!-- Top Sticky Toolbar (Hidden on Print) -->
    <div class="bg-white border-b border-gray-50 shadow-sm sticky top-0 z-40 print:hidden">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="./order-details.html"
                    class="flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-arrow-left h-4 w-4 mr-2">
                        <path d="m12 19-7-7 7-7"></path>
                        <path d="M19 12H5"></path>
                    </svg>
                    ফিরে যান
                </a>
                <h1 class="text-xl font-black text-[#1D2128]">ইনভয়েস</h1>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()"
                    class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-printer h-4 w-4 mr-2">
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"></path>
                        <rect x="6" y="14" width="12" height="8" rx="1"></rect>
                    </svg>
                    প্রিন্ট করুন
                </button>
                <button
                    class="px-4 py-2 bg-[#1D2128] text-white rounded-lg text-sm hover:bg-black transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-download h-4 w-4 mr-2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" x2="12" y1="15" y2="3"></line>
                    </svg>
                    ডাউনলোড করুন
                </button>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-10 print:p-0">
        <!-- Invoice Card -->
        <div
            class="max-w-4xl mx-auto bg-white rounded-lg border border-gray-200 shadow-xs print:shadow-none print:border-none overflow-hidden">
            <div class="p-8 md:p-12">

                <!-- Header: Logo & Invoice Info -->
                <div class="flex flex-col md:flex-row justify-between items-start gap-8 mb-10">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="bg-[#FF6A00] w-10 h-10 flex items-center justify-center rounded-lg">
                                <span class="text-white text-xl font-bold">O</span>
                            </div>
                            <span class="text-2xl font-semibold tracking-tight text-[#1D2128]">OrenMart</span>
                        </div>
                        <div class="text-sm text-gray-500 space-y-1 font-medium">
                            <p>১২ শপিং স্ট্রিট, ঢাকা-১২০০</p>
                            <p>ফোন: +৮৮০২-১২৩৪৫6৭৮</p>
                            <p>ইমেইল: support@orenmart.com</p>
                            <p>ওয়েবসাইট: www.orenmart.com</p>
                        </div>
                    </div>
                    <div class="md:text-right">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4 uppercase tracking-tighter">ইনভয়েস</h2>
                        <div class="text-sm space-y-1.5 ">
                            <p class="text-gray-500"><span class="font-semibold">ইনভয়েস নম্বর:</span> <span
                                    class="font-base">INV-2024-001</span></p>
                            <p class="text-gray-500"><span class="font-semibold">অর্ডার নম্বর:</span> <span
                                    class="font-base">ORD-001</span></p>
                            <p class="text-gray-500"><span class="font-semibold">তারিখ:</span> <span class="font-base">১৫
                                    জানুয়ারি, ২০২৪</span></p>
                            <p class="text-gray-500"><span class="font-semibold">পেমেন্ট স্ট্যাটাস:</span> <span
                                    class="font-base text-green-500">পেইড</span></p>
                        </div>
                    </div>
                </div>

                <div class="h-px bg-gray-200 w-full mb-10"></div>

                <!-- Billing & Payment Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-12">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 uppercase tracking-widest mb-4">বিল টু:</h3>
                        <div class="text-sm text-gray-600 space-y-1 font-medium">
                            <p>জন ডো</p>
                            <p>১২৩ মেইন স্ট্রিট, ঢাকা-১২০০</p>
                            <p>ফোন: +৮৮০১৭১২৩৪৫৬৭৮</p>
                            <p>ইমেইল: john@example.com</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 uppercase tracking-widest mb-4">পেমেন্ট তথ্য:</h3>
                        <div class="text-sm text-gray-600 space-y-1 font-medium">
                            <p><span class="text-gray-600 font-semibold">পেমেন্ট মেথড:</span> ক্যাশ অন ডেলিভারি</p>
                            <p><span class="text-gray-600 font-semibold">পেমেন্ট স্ট্যাটাস:</span> <span
                                    class="text-green-600">পেইড</span></p>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="mb-10 overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b-2 border-gray-200 text-md text-gray-900">
                                <th class="text-left py-4 px-2  uppercase tracking-widest">পণ্যের বিবরণ</th>
                                <th class="text-center py-4 px-2 uppercase tracking-widest">পরিমাণ</th>
                                <th class="text-right py-4 px-2 uppercase tracking-widest">একক মূল্য</th>
                                <th class="text-right py-4 px-2 uppercase tracking-widest">মোট</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="text-gray-700 font-medium">
                                <td class="py-5 px-2">
                                    G63 Speaker Lamp - Multi-Function Bluetooth Speaker
                                </td>
                                <td class="py-5 px-2 text-center">1</td>
                                <td class="py-5 px-2 text-right">৳690</td>
                                <td class="py-5 px-2 text-right">৳690</td>
                            </tr>
                            <tr class="text-gray-700 font-medium">
                                <td class="py-5 px-2">
                                    Premium Car Seat Cover Set - Sport Edition
                                </td>
                                <td class="py-5 px-2 text-center">1</td>
                                <td class="py-5 px-2 text-right">৳450</td>
                                <td class="py-5 px-2 text-right">৳450</td>
                            </tr>
                            <tr class="text-gray-700 font-medium">
                                <td class="py-5 px-2">
                                    Car Shape Mobile Holder - Universal
                                </td>
                                <td class="py-5 px-2 text-center">1</td>
                                <td class="py-5 px-2 text-right">৳110</td>
                                <td class="py-5 px-2 text-right">৳110</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Summary -->
                <div class="flex justify-end mb-12">
                    <div class="w-full max-w-[280px] space-y-3">
                        <div class="flex justify-between text-md font-medium">
                            <span class="text-gray-500">সাবটোটাল:</span>
                            <span class="text-gray-900">৳1,250</span>
                        </div>
                        <div class="flex justify-between text-md font-medium">
                            <span class="text-gray-500">ডেলিভারি চার্জ:</span>
                            <span class="text-gray-900">৳0</span>
                        </div>
                        <div class="h-px bg-gray-200"></div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-lg font-semibold text-gray-900">মোট পরিশোধ:</span>
                            <span class="text-lg font-semibold text-[#FF6A00]">৳1,250</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Notes -->
                <div class="border-t border-gray-200 pt-8 mb-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                        <div>
                            <h4 class="text-md font-black text-gray-900 uppercase tracking-widest mb-3">নিয়ম ও শর্তাবলী:
                            </h4>
                            <ul class="text-sm text-gray-500 space-y-1.5 font-medium leading-relaxed">
                                <li>• পণ্য ফেরত দেওয়ার জন্য ৭ দিনের মধ্যে যোগাযোগ করুন</li>
                                <li>• ক্ষতিগ্রস্ত পণ্যের জন্য ১ বছর ওয়ারেন্টি</li>
                                <li>• সকল দাম বাংলাদেশী টাকায় উল্লেখিত</li>
                            </ul>
                        </div>
                        <div class="md:text-right text-gray-700">
                            <p class="text-sm  mb-2">কোনো প্রশ্ন থাকলে যোগাযোগ করুন:</p>
                            <p class="text-sm"><span class="font-semibold">সাপোর্ট:</span> +৮৮০২-১২৩৪৫৬৭৮</p>
                            <p class="text-sm"><span class="font-semibold">ইমেইল:</span> support@orenmart.com</p>
                        </div>
                    </div>
                </div>

                <!-- Thank you Message -->
                <div class="text-center py-6 border-t border-gray-200">
                    <p class="text-lg font-black text-gray-900 mb-1">আপনার অর্ডারের জন্য ধন্যবাদ!</p>
                    <p class="text-sm text-gray-600 font-medium">OrenMart এর সাথে কেনাকাটা করার জন্য আপনাকে ধন্যবাদ।</p>
                </div>

            </div>
        </div>
    </div>
@endsection
