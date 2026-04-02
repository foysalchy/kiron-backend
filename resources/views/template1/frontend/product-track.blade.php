@extends('template1.layouts.front')

@section('content')
    <section class="container mx-auto">
        <div class="max-w-2xl mx-auto">

            <!-- Page Title -->
            <div class="text-center mb-10">
                <h1 class="text-2xl md:text-3xl font-black text-gray-900 mb-4">অর্ডার ট্র্যাক করুন</h1>
                <p class="text-gray-600">আপনার অর্ডার নম্বর দিয়ে অর্ডারের বর্তমান অবস্থা জানুন</p>
            </div>

            <!-- Tracking Card -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xl shadow-gray-200/50 mb-8 overflow-hidden">

                <h2 class="text-xl md:text-2xl font-bold text-gray-800 text-center p-4">অর্ডার ট্র্যাকিং</h2>
                <div class="p-4 text-sm">
                    <form class="space-y-5">
                        <div>
                            <label for="orderId" class="block  text-gray-700 mb-2">অর্ডার নম্বর</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" id="orderId" placeholder="যেমন: ORD-12345"
                                    class="w-full pl-11 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#FF6A00]/20 focus:border-[#FF6A00] outline-none transition-all
                                            ">
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full bg-[#FF6A00] hover:bg-orange-600 text-white font-medium py-2 rounded-md shadow-lg shadow-orange-200 transition-all active:scale-[0.98]">
                            অর্ডার ট্র্যাক করুন
                        </button>
                    </form>
                </div>
            </div>

            <!-- Instruction Steps -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-8">
                <div class="p-6 border-b border-gray-50">
                    <h3 class="text-lg md:text-2xl font-bold text-gray-800">কিভাবে ট্র্যাক করবেন?</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="flex items-start gap-4">
                        <div
                            class="w-6 h-6 bg-[#FF6A00] text-white rounded-full flex items-center justify-center text-sm font-bold shrink-0">
                            ১</div>
                        <div>
                            <h4 class="font-medium text-gray-900">অর্ডার নম্বর খুঁজুন</h4>
                            <p class="text-sm text-gray-600">আপনার অর্ডার কনফার্মেশন ইমেইল বা SMS এ অর্ডার নম্বর পাবেন</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div
                            class="w-6 h-6 bg-[#FF6A00] text-white rounded-full flex items-center justify-center text-sm font-bold shrink-0">
                            ২</div>
                        <div>
                            <h4 class="font-medium text-gray-900">অর্ডার নম্বর লিখুন</h4>
                            <p class="text-sm text-gray-600">উপরের বক্সে আপনার অর্ডার নম্বর লিখুন (যেমন: ORD-001)</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div
                            class="w-6 h-6 bg-[#FF6A00] text-white rounded-full flex items-center justify-center text-sm font-bold shrink-0">
                            ৩</div>
                        <div>
                            <h4 class="font-medium text-gray-900">ট্র্যাক করুন</h4>
                            <p class="text-sm text-gray-600">"অর্ডার ট্র্যাক করুন" বাটনে ক্লিক করুন এবং আপনার অর্ডারের
                                বিস্তারিত দেখুন</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Guide Section -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 md:p-12">
                <!-- Title -->
                <h2 class="text-lg md:text-2xl font-bold text-gray-900 mb-10">অর্ডার স্ট্যাটাস গাইড</h2>

                <!-- Status List -->
                <div class="space-y-8">

                    <!-- ১. অর্ডার প্লেস -->
                    <div class="flex items-start gap-3">
                        <div class="text-gray-500 mt-1 shrink-0">
                            <i class="far fa-clock h-5 w-5"></i>
                        </div>
                        <div>
                            <h4 class="text-md font-medium text-gray-900">অর্ডার প্লেস</h4>
                            <p class="text-sm text-gray-600">আপনার অর্ডার সফলভাবে প্লেস হয়েছে</p>
                        </div>
                    </div>

                    <!-- ২. প্রসেসিং -->
                    <div class="flex items-start gap-3">
                        <div class="text-[#FF6A00] mt-1 shrink-0">
                            <i class="fas fa-cube h-5 w-5"></i>
                        </div>
                        <div>
                            <h4 class="text-md font-medium text-gray-900">প্রসেসিং</h4>
                            <p class="text-sm text-gray-600">আপনার অর্ডার প্রস্তুত করা হচ্ছে</p>
                        </div>
                    </div>

                    <!-- ৩. শিপড -->
                    <div class="flex items-start gap-3">
                        <div class="text-blue-500 mt-1 shrink-0">
                            <i class="fas fa-truck h-5 w-5"></i>
                        </div>
                        <div>
                            <h4 class="text-md font-medium text-gray-900">শিপড</h4>
                            <p class="text-sm text-gray-600">আপনার অর্ডার পাঠানো হয়েছে</p>
                        </div>
                    </div>

                    <!-- ৪. ডেলিভার -->
                    <div class="flex items-start gap-3">
                        <div class="text-green-500 mt-1 shrink-0">
                            <i class="far fa-check-circle h-5 w-5"></i>
                        </div>
                        <div>
                            <h4 class="text-md font-medium text-gray-900">ডেলিভার</h4>
                            <p class="text-sm text-gray-600">আপনার অর্ডার সফলভাবে ডেলিভার হয়েছে</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </section>
@endsection
