@extends('template1.layouts.front')

@section('content')
    <!-- Header Text Section -->
    <section class="container mx-auto py-6">
        <a href="../index.html"
            class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-md text-sm text-gray-700 hover:bg-gray-50 transition-all mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-arrow-left h-4 w-4 mr-2">
                <path d="m12 19-7-7 7-7"></path>
                <path d="M19 12H5"></path>
            </svg>
            হোম পেজে ফিরুন
        </a>
        <h1 class="text-2xl md:text-3xl font-black text-[#1D2128] mb-2 tracking-tight">শর্তাবলী ও নিয়মাবলী</h1>
        <p class="text-gray-500 font-medium">সর্বশেষ আপডেট: ১ জানুয়ারি, ২০২৪</p>
    </section>
    <!-- Header Text Section -->
    <section class="container mx-auto py-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

            <!-- LEFT SIDEBAR -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm">
                    <div class="p-5 border-b border-gray-100 bg-gray-50">
                        <h2 class="font-bold text-gray-900">বিষয়সূচি</h3>
                    </div>
                    <nav class="flex flex-col">
                        <a href="#section1"
                            class="flex items-center gap-3 px-5 py-4 text-sm bg-orange-100 text-[#FF6A00] border-r-2 border-[#FF6A00]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-file-text h-4 w-4">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                <path d="M10 9H8"></path>
                                <path d="M16 13H8"></path>
                                <path d="M16 17H8"></path>
                            </svg>
                            শর্তাবলী গ্রহণ
                        </a>
                        <a href="#section2"
                            class="flex items-center gap-3 px-5 py-4 text-sm text-gray-600 hover:bg-gray-50 border-r-4 border-transparent transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-shield h-4 w-4">
                                <path
                                    d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                                </path>
                            </svg>
                            অ্যাকাউন্ট নিয়মাবলী
                        </a>
                        <a href="#section3"
                            class="flex items-center gap-3 px-5 py-4 text-sm text-gray-600 hover:bg-gray-50 border-r-4 border-transparent transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-credit-card h-4 w-4">
                                <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                <line x1="2" x2="22" y1="10" y2="10"></line>
                            </svg>
                            অর্ডার ও পেমেন্ট
                        </a>
                        <a href="#section4"
                            class="flex items-center gap-3 px-5 py-4 text-sm text-gray-600 hover:bg-gray-50 border-r-4 border-transparent transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-truck h-4 w-4">
                                <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                                <path d="M15 18H9"></path>
                                <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14">
                                </path>
                                <circle cx="17" cy="18" r="2"></circle>
                                <circle cx="7" cy="18" r="2"></circle>
                            </svg>
                            ডেলিভারি নীতি
                        </a>
                        <a href="#section5"
                            class="flex items-center gap-3 px-5 py-4 text-sm text-gray-600 hover:bg-gray-50 border-r-4 border-transparent transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-rotate-ccw h-4 w-4">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <path d="M3 3v5h5"></path>
                            </svg>
                            রিটার্ন ও রিফান্ড
                        </a>
                        <a href="#section6"
                            class="flex items-center gap-3 px-5 py-4 text-sm text-gray-600 hover:bg-gray-50 border-r-4 border-transparent transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-triangle-alert h-4 w-4">
                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path>
                                <path d="M12 9v4"></path>
                                <path d="M12 17h.01"></path>
                            </svg>
                            নিষিদ্ধ কার্যকলাপ
                        </a>
                        <a href="#section7"
                            class="flex items-center gap-3 px-5 py-4 text-sm text-gray-600 hover:bg-gray-50 border-r-4 border-transparent transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-scale h-4 w-4">
                                <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"></path>
                                <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"></path>
                                <path d="M7 21h10"></path>
                                <path d="M12 3v18"></path>
                                <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"></path>
                            </svg>
                            দায়বদ্ধতার সীমা
                        </a>
                        <a href="#section8"
                            class="flex items-center gap-3 px-5 py-4 text-sm text-gray-600 hover:bg-gray-50 border-r-4 border-transparent transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-square-pen h-4 w-4">
                                <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path
                                    d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z">
                                </path>
                            </svg>
                            শর্ত পরিবর্তন
                        </a>
                    </nav>
                </div>
            </div>

            <!-- RIGHT CONTEN-->
            <div class="lg:col-span-3 space-y-8">

                <!-- (Blue) -->
                <section id="section1" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-blue-50 flex items-center gap-3 text-blue-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-file-text h-5 w-5">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                            <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                            <path d="M10 9H8"></path>
                            <path d="M16 13H8"></path>
                            <path d="M16 17H8"></path>
                        </svg>
                        <span>১. শর্তাবলী গ্রহণ</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-4">
                        <p>OrenMart ওয়েবসাইট ব্যবহার করার মাধ্যমে আপনি এই শর্তাবলী মেনে নিতে সম্মত হচ্ছেন। যদি আপনি এই
                            শর্তাবলীর সাথে একমত না হন, তাহলে অনুগ্রহ করে আমাদের সেবা ব্যবহার করবেন না।</p>
                        <ul class="list-disc list-inside space-y-2 ml-4 marker:text-blue-500">
                            <li>এই শর্তাবলী সকল ব্যবহারকারীর জন্য প্রযোজ্য</li>
                            <li>আমাদের সেবা ব্যবহার করলে আপনি এই নিয়মাবলী মেনে চলতে বাধ্য</li>
                            <li>শর্তাবলী লঙ্ঘন করলে আমরা আপনার অ্যাকাউন্ট বন্ধ করার অধিকার রাখি</li>
                        </ul>
                    </div>
                </section>

                <!-- 2. অ্যাকাউন্ট নিয়মাবলী (Green) -->
                <section id="section2" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-green-50 flex items-center gap-3 text-green-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-shield h-5 w-5">
                            <path
                                d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                            </path>
                        </svg>
                        <span>২. অ্যাকাউন্ট নিয়মাবলী</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-6">
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">অ্যাকাউন্ট তৈরি:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>সঠিক ও সম্পূর্ণ তথ্য প্রদান করতে হবে</li>
                                <li>একটি বৈধ ইমেইল ঠিকানা ব্যবহার করতে হবে</li>
                                <li>পাসওয়ার্ড গোপনীয় রাখার দায়িত্ব আপনার</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">অ্যাকাউন্ট নিরাপত্তা:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>শক্তিশালী পাসওয়ার্ড ব্যবহার করুন</li>
                                <li>অন্য কারো সাথে লগইন তথ্য শেয়ার করবেন না</li>
                                <li>সন্দেহজনক কার্যকলাপ দেখলে আমাদের জানান</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- order payment -->
                <section id="section3" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-purple-50 flex items-center gap-3 text-purple-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-credit-card h-5 w-5">
                            <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                            <line x1="2" x2="22" y1="10" y2="10"></line>
                        </svg>
                        <span>৩. অর্ডার ও পেমেন্ট</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-6">
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">অর্ডার প্রক্রিয়া:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>অর্ডার কনফার্ম করার আগে সব তথ্য যাচাই করুন</li>
                                <li>অর্ডার প্লেস করার পর ২৪ ঘন্টার মধ্যে কনফার্মেশন পাবেন</li>
                                <li>স্টক না থাকলে আমরা আপনাকে জানাবো</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">পেমেন্ট নীতি:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>ক্যাশ অন ডেলিভারি (COD) সুবিধা আছে</li>
                                <li>অনলাইন পেমেন্ট নিরাপদ ও এনক্রিপ্টেড</li>
                                <li>পেমেন্ট ব্যর্থ হলে অর্ডার বাতিল হয়ে যাবে</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 4. ডেলিভারি নীতি (Orange) -->
                <section id="section4" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-orange-50 flex items-center gap-3 text-orange-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-truck h-5 w-5">
                            <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                            <path d="M15 18H9"></path>
                            <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14">
                            </path>
                            <circle cx="17" cy="18" r="2"></circle>
                            <circle cx="7" cy="18" r="2"></circle>
                        </svg>
                        <span>৪. ডেলিভারি নীতি</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-6">
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">ডেলিভারি সময়:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>ঢাকার ভিতরে: ১-২ কার্যদিবস</li>
                                <li>ঢাকার বাইরে: ৩-৫ কার্যদিবস</li>
                                <li>বিশেষ পণ্যের জন্য আলাদা সময় লাগতে পারে</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">ডেলিভারি চার্জ:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>ঢাকার ভিতরে: ৬০ টাকা</li>
                                <li>ঢাকার বাইরে: ১০০ টাকা</li>
                                <li>১০০০ টাকার উপরে অর্ডারে ফ্রি ডেলিভারি</li>
                            </ul>
                        </div>
                    </div>
                </section>
                <!-- 5. রিটার্ন ও রিফান্ড (Red) -->
                <section id="section5" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-red-50 flex items-center gap-3 text-red-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-rotate-ccw h-5 w-5">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                            <path d="M3 3v5h5"></path>
                        </svg>
                        <span>৫. রিটার্ন ও রিফান্ড</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-6">
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">রিটার্ন নীতি:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>ডেলিভারির ৭ দিনের মধ্যে রিটার্ন করা যাবে</li>
                                <li>পণ্য অব্যবহৃত ও মূল প্যাকেজিং এ থাকতে হবে</li>
                                <li>ক্ষতিগ্রস্ত বা ভুল পণ্যের জন্য ফ্রি রিটার্ন</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">রিফান্ড প্রক্রিয়া:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>রিটার্ন গ্রহণের ৫-৭ কার্যদিবসে রিফান্ড</li>
                                <li>অনলাইন পেমেন্টের ক্ষেত্রে একই মাধ্যমে রিফান্ড</li>
                                <li>COD অর্ডারের জন্য ব্যাংক ট্রান্সফার</li>
                            </ul>
                        </div>
                    </div>
                </section>
                <!-- ৬. নিষিদ্ধ কার্যকলাপ (Yellow) -->
                <section id="section6" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-yellow-50 flex items-center gap-3 text-yellow-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-triangle-alert h-5 w-5">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path>
                            <path d="M12 9v4"></path>
                            <path d="M12 17h.01"></path>
                        </svg>
                        <span>৬. নিষিদ্ধ কার্যকলাপ</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">নিম্নলিখিত কার্যকলাপ সম্পূর্ণ নিষিদ্ধ:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>ভুয়া তথ্য প্রদান করা</li>
                                <li>অন্যের অ্যাকাউন্ট হ্যাক করার চেষ্টা</li>
                                <li>স্প্যাম বা অবাঞ্ছিত বার্তা পাঠানো</li>
                                <li>কপিরাইট লঙ্ঘন করা</li>
                                <li>ওয়েবসাইটের নিরাপত্তা ভঙ্গ করার চেষ্টা</li>
                                <li>অশ্লীল বা আপত্তিকর কন্টেন্ট আপলোড করা</li>
                            </ul>
                        </div>

                    </div>
                </section>
                <!-- ৭. দায়বদ্ধতার সীমা (Indigo) -->
                <section id="section7" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-indigo-50 flex items-center gap-3 text-indigo-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-scale h-5 w-5">
                            <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"></path>
                            <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"></path>
                            <path d="M7 21h10"></path>
                            <path d="M12 3v18"></path>
                            <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"></path>
                        </svg>
                        <span>৭. দায়বদ্ধতার সীমা</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">OrenMart নিম্নলিখিত বিষয়ে দায়বদ্ধ নয়:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>তৃতীয় পক্ষের সেবা বা পণ্যের মান</li>
                                <li>প্রাকৃতিক দুর্যোগের কারণে ডেলিভারি বিলম্ব</li>
                                <li>ব্যবহারকারীর ভুল তথ্যের কারণে সমস্যা</li>
                                <li>ইন্টারনেট সংযোগ বা প্রযুক্তিগত সমস্যা</li>
                                <li>অননুমোদিত অ্যাকাউন্ট ব্যবহার</li>

                            </ul>
                        </div>

                    </div>
                </section>
                <!-- ৮. শর্ত পরিবর্তন (Teal) -->
                <section id="section8" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-teal-50 flex items-center gap-3 text-teal-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-square-pen h-5 w-5">
                            <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path
                                d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z">
                            </path>
                        </svg>
                        <span>৮. শর্ত পরিবর্তন</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">আমরা যেকোনো সময় এই শর্তাবলী পরিবর্তন করার অধিকার
                                রাখি। পরিবর্তনের ক্ষেত্রে:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>ওয়েবসাইটে নোটিশ প্রকাশ করা হবে</li>
                                <li>ইমেইলের মাধ্যমে জানানো হবে</li>
                                <li>পরিবর্তন কার্যকর হওয়ার তারিখ উল্লেখ থাকবে</li>
                                <li>পরিবর্তনের পর সেবা ব্যবহার করলে নতুন শর্ত মেনে নেওয়া হবে</li>
                            </ul>
                        </div>

                    </div>
                </section>

                <!-- CONTACT SECTION (Gray) -->
                <section class="border border-gray-200 rounded-xl overflow-hidden bg-gray-50">
                    <div class="p-8 space-y-4">
                        <h3 class="text-xl font-bold text-gray-900">যোগাযোগ</h3>
                        <p class="text-gray-600 font-medium">এই শর্তাবলী সম্পর্কে কোনো প্রশ্ন থাকলে আমাদের সাথে যোগাযোগ
                            করুন:</p>
                        <div class="space-y-2 text-gray-700">
                            <p><span class="font-bold">ইমেইল:</span> legal@orenmart.com</p>
                            <p><span class="font-bold">ফোন:</span> +৮৮০ ১৭১২-৩৪৫৬৭৮</p>
                            <p><span class="font-bold">ঠিকানা:</span> ১২৩ বিজনেস এভিনিউ, ঢাকা ১২০০, বাংলাদেশ</p>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </section>
@endsection
@push('scripts')
    <script>
        const navLinks = document.querySelectorAll('nav a');
        const sections = document.querySelectorAll('section[id]');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= (sectionTop - 150)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('bg-orange-100', 'text-[#FF6A00]', 'border-[#FF6A00]');
                link.classList.add('text-gray-600', 'border-transparent');

                if (link.getAttribute('href').includes(current)) {
                    link.classList.add('bg-orange-100', 'text-[#FF6A00]', 'border-[#FF6A00]');
                    link.classList.remove('text-gray-600', 'border-transparent');
                }
            });
        });
    </script>
@endpush
