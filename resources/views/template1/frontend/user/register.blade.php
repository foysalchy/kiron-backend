@extends('template1.layouts.front')

@section('content')
    <section class="container py-6 mx-auto">
        <div class="max-w-xl mx-auto">
            <!-- Registration Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">

                <!-- Header -->
                <div class="p-8 text-center border-b border-gray-50">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">নিবন্ধন করুন</h1>
                    <p class="text-gray-500 font-medium">নতুন অ্যাকাউন্ট তৈরি করুন</p>
                </div>

                <!-- Form -->
                <div class="p-8">
                    <form class="space-y-5">

                        <!-- Full Name -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 ml-1">পূর্ণ নাম</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="far fa-user text-sm"></i>
                                </span>
                                <input type="text" placeholder="আপনার পূর্ণ নাম" required
                                    class="w-full pl-11 pr-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#FF6A00] focus:ring-4 focus:ring-orange-50 transition-all text-sm">
                            </div>
                        </div>

                        <!-- Email Address -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 ml-1">ইমেইল ঠিকানা</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="far fa-envelope text-sm"></i>
                                </span>
                                <input type="email" placeholder="user@example.com" required
                                    class="w-full pl-11 pr-4 py-2 rounded-lg border bg-blue-100 border-gray-200 outline-none focus:border-[#FF6A00] focus:ring-4 focus:ring-orange-50 transition-all text-sm">
                            </div>
                        </div>

                        <!-- Mobile Number -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 ml-1">মোবাইল নম্বর</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-phone-alt text-sm"></i>
                                </span>
                                <input type="tel" placeholder="আপনার মোবাইল নম্বর" required
                                    class="w-full pl-11 pr-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#FF6A00] focus:ring-4 focus:ring-orange-50 transition-all text-sm">
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 ml-1">ঠিকানা</label>
                            <div class="relative">
                                <span class="absolute left-4 top-4 text-gray-400">
                                    <i class="fas fa-map-marker-alt text-sm"></i>
                                </span>
                                <textarea placeholder="আপনার সম্পূর্ণ ঠিকানা" rows="3" required
                                    class="w-full pl-11 pr-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#FF6A00] focus:ring-4 focus:ring-orange-50 transition-all text-sm"></textarea>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 ml-1">পাসওয়ার্ড</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-lock text-sm"></i>
                                </span>
                                <input type="password" placeholder="••••••••" required
                                    class="w-full pl-11 pr-12 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#FF6A00] focus:ring-4 focus:ring-orange-50 transition-all text-sm">
                                <button type="button"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#FF6A00]">
                                    <i class="far fa-eye text-[14px]"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 ml-1">পাসওয়ার্ড নিশ্চিত করুন</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-lock text-sm"></i>
                                </span>
                                <input type="password" placeholder="পাসওয়ার্ড আবার লিখুন" required
                                    class="w-full pl-11 pr-12 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#FF6A00] focus:ring-4 focus:ring-orange-50 transition-all text-sm">
                                <button type="button"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#FF6A00]">
                                    <i class="far fa-eye text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="flex items-start gap-3 py-2">
                            <input type="checkbox" id="terms" required
                                class="mt-1 w-4 h-4 accent-[#FF6A00] cursor-pointer">
                            <label for="terms" class="text-sm font-medium text-gray-600 cursor-pointer">
                                আমি <a href="#" class="text-[#FF6A00] hover:underline">নিয়ম ও শর্তাবলী</a> এবং <a
                                    href="#" class="text-[#FF6A00] hover:underline">গোপনীয়তা নীতি</a> গ্রহণ করি
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full bg-[#FF6A00] hover:bg-orange-600 text-white font-black py-2 text-md font-medium rounded-lg shadow-xs shadow-orange-100 transition-all active:scale-[0.98]">
                            নিবন্ধন করুন
                        </button>

                        <!-- Login Link -->
                        <div class="text-center pt-4 text-sm">
                            <p class="text-gray-500 font-medium">
                                ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="{{url('/login')}}"
                                    class="text-[#FF6A00] font-medium hover:underline ml-1">লগইন করুন</a>
                            </p>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
