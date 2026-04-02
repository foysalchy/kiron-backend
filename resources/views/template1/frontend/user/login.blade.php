@extends('template1.layouts.front')

@section('content')
    <section class="container py-6 mx-auto ">
        <!-- Login Card -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-xs max-w-lg mx-auto overflow-hidden">

            <!-- Card Header -->
            <div class="p-6 text-center border-b border-gray-50">
                <h1 class="text-2xl font-black text-gray-900 mb-2">লগইন করুন</h1>
                <p class="text-gray-700 font-medium">আপনার অ্যাকাউন্টে প্রবেশ করুন</p>
            </div>

            <!-- Login Form -->
            <div class="p-6">
                <form class="space-y-4">

                    <!-- Email Field -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700 ml-1">ইমেইল ঠিকানা</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="far fa-envelope text-sm"></i>
                            </span>
                            <input type="email" placeholder="user@example.com" required
                                class="w-full pl-11 pr-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#FF6A00] focus:ring-4 focus:ring-orange-50 transition-all text-sm">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700 ml-1">পাসওয়ার্ড</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-lock text-sm"></i>
                            </span>
                            <input type="password" placeholder="••••••••" required
                                class="w-full pl-11 pr-12 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#FF6A00] focus:ring-4 focus:ring-orange-50 transition-all text-sm">
                            <!-- Toggle Visibility Button -->
                            <button type="button"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#FF6A00]">
                                <i class="far fa-eye text-[14px]"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="text-left">
                        <a href="#" class="text-[#FF6A00] text-sm font-medium hover:underline">পাসওয়ার্ড ভুলে
                            গেছেন?</a>
                    </div>

                    <!-- Login Button -->
                    <button type="submit"
                        class="w-full bg-[#FF6A00] hover:bg-orange-600 text-white font-medium py-2 rounded-lg shadow-xs text-md shadow-orange-100 transition-all active:scale-[0.98]">
                        লগইন করুন
                    </button>

                    <!-- Registration Link -->
                    <div class="text-center pt-2">
                        <p class="text-gray-500 font-medium">
                            অ্যাকাউন্ট নেই? <a href="{{url('/register')}}"
                                class="text-[#FF6A00] font-medium hover:underline ml-1">নিবন্ধন করুন</a>
                        </p>
                    </div>
                </form>

            </div>
        </div>
    </section>
@endsection
