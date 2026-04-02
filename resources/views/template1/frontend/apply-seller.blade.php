@extends('template1.layouts.front')

@section('content')
    <!-- Header Text Section -->
    <section class="container mx-auto py-6">
        <div class="text-center mb-12">
            <!-- Title -->
            <h1 class="text-3xl md:text-4xl font-bold text-[#0F172A] mb-4 tracking-tight">
                Become a Seller on OrenMart
            </h1>

            <!-- Paragraph with Break -->
            <p class="text-gray-500 text-xl md:text-2xl max-w-2xl mx-auto leading-relaxed">
                Join thousands of successful sellers and start your online business
                journey with us
            </p>
        </div>
    </section>
    <!-- Progress Steps Indicator -->
    <section class="container mx-auto py-6">

        <div class="flex flex-wrap items-center justify-center gap-y-6 gap-x-4 mb-10">
            <!-- Step 1: Active -->
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-[#2563EB] text-white flex items-center justify-center text-sm font-bold">
                    1</div>
                <div class="text-left">
                    <p class="text-md font-medium text-[#2563EB] leading-none">Personal Information</p>
                    <p class="text-sm text-[#2563EB]/70 mt-1">Tell us about yourself</p>
                </div>
                <i class="fas fa-chevron-right text-gray-500 text-xs ml-2 hidden lg:block"></i>
            </div>

            <!-- Step 2 -->
            <div class="flex items-center gap-3 ">
                <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-800 flex items-center justify-center text-sm">2</div>
                <div class="text-left">
                    <p class="text-md font-medium text-gray-400 leading-none">Business Details</p>
                    <p class="text-sm text-gray-400 mt-1">Your business information</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 text-xs ml-2 hidden lg:block"></i>
            </div>

            <!-- Step 3 -->
            <div class="flex items-center gap-3">
                <div
                    class="w-8 h-8 rounded-full bg-gray-200 text-gray-800 flex items-center justify-center text-sm font-bold">
                    3</div>
                <div class="text-left">
                    <p class="text-md font-medium text-gray-400 leading-none">Financial Information</p>
                    <p class="text-sm text-gray-400 mt-1">Sales expectations and experience</p>
                </div>
                <i class="fas fa-chevron-right text-gray-4400 text-xs ml-2 hidden lg:block"></i>
            </div>

            <!-- Step 4 -->
            <div class="flex items-center gap-3">
                <div
                    class="w-8 h-8 rounded-full bg-gray-200 text-gray-800 flex items-center justify-center text-sm font-bold">
                    4</div>
                <div class="text-left">
                    <p class="text-md font-medium text-gray-400 leading-none">Documents & Verification</p>
                    <p class="text-sm text-gray-400 mt-1">Upload required documents</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400 text-xs ml-2 hidden lg:block"></i>
            </div>

            <!-- Step 5 -->
            <div class="flex items-center gap-3">
                <div
                    class="w-8 h-8 rounded-full bg-gray-200 text-gray-800 flex items-center justify-center text-sm font-bold">
                    5</div>
                <div class="text-left">
                    <p class="text-md font-medium text-gray-400 leading-none">Review & Submit</p>
                    <p class="text-sm text-gray-400 mt-1">Review your application</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Form Card -->
    <section class="container mx-auto py-6">
        <div class="max-w-4xl mx-auto mb-10">
            <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-8 md:p-10">
                <div class="flex items-center gap-3 mb-8">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-store w-6 h-6 mr-2">
                        <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"></path>
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                        <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"></path>
                        <path d="M2 7h20"></path>
                        <path
                            d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7">
                        </path>
                    </svg>
                    <h2 class="text-2xl font-semibold leading-none tracking-tight flex items-center">Personal Information
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none text-gray-800">Full Name *</label>
                        <input type="text" placeholder="Enter your full name"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 outline-none focus:border-blue-500 transition-all text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none text-gray-800">Email Address *</label>
                        <input type="email" placeholder="your@email.com"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 outline-none focus:border-blue-500 transition-all text-sm">
                    </div>
                    <div class="space-y-2 md:col-span-1">
                        <label class="text-sm font-medium leading-none text-gray-800">Phone Number *</label>
                        <input type="tel" placeholder="+880 1XXX-XXXXXX"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 outline-none focus:border-blue-500 transition-all text-sm">
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between items-center mt-8">
                <button
                    class="px-6 py-2 rounded-md border border-gray-200 bg-white text-gray-400 font-medium text-md cursor-not-allowed">Previous</button>
                <button
                    class="px-8 py-2 rounded-md bg-[#0F172A] text-white font-medium text-md hover:bg-black transition-all">Next</button>
            </div>
        </div>
    </section>
    <!-- Features Section -->
    <section class="container mx-auto py-6">
        <div class="bg-white rounded-lg p-6 border-gray-50 shadow-sm">
            <h2 class="text-2xl font-bold text-center text-[#0F172A] mb-12">Why Sell on OrenMart?</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-6">
                <!-- Feature 1 -->
                <div class="text-center">
                    <div
                        class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-store w-8 h-8 text-blue-600">
                            <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"></path>
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                            <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"></path>
                            <path d="M2 7h20"></path>
                            <path
                                d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-3">Easy Setup</h3>
                    <p class="text-gray-500 text-lg leading-relaxed max-w-[450px] mx-auto">Get your store up and running in
                        minutes with our intuitive setup process</p>
                </div>

                <!-- Feature 2 -->
                <div class="text-center">
                    <div
                        class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-circle-check-big w-8 h-8 text-green-600">
                            <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                            <path d="m9 11 3 3L22 4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-3">Low Fees</h3>
                    <p class="text-gray-500 text-lg leading-relaxed max-w-[450px] mx-auto">Competitive commission rates and
                        transparent pricing with no hidden fees</p>
                </div>

                <!-- Feature 3 -->
                <div class="text-center">
                    <div
                        class="w-16 h-16 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-arrow-right w-8 h-8 text-purple-600">
                            <path d="M5 12h14"></path>
                            <path d="m12 5 7 7-7 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-3">Marketing Support</h3>
                    <p class="text-gray-500 text-lg leading-relaxed max-w-[450px] mx-auto">Benefit from our marketing
                        campaigns and promotional opportunities</p>
                </div>
            </div>
        </div>

    </section>
@endsection
