@extends('template1.layouts.front')

@section('content')
    <section class="container py-6 mx-auto">
        <div class="max-w-3xl mx-auto text-center">

            <!-- Icon Circle -->
            <div class="inline-flex items-center justify-center w-20 h-20 bg-orange-100 text-[#FF6A00] rounded-full mb-8">
                <!-- Question Mark Icon (Lucide/FontAwesome style) -->
                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <path d="M12 17h.01"></path>
                </svg>
            </div>

            <!-- Main Heading -->
            <h1 class="text-xl md:text-4xl font-black text-black mb-6 tracking-tight">
                সাহায্য ও সহায়তা
            </h1>

            <!-- Description -->
            <p class="text-sm md:text-xl text-gray-700 leading-relaxed max-w-2xl mx-auto font-medium">
                আমরা আপনার সেবায় সর্বদা প্রস্তুত। যেকোনো সমস্যার সমাধান পেতে আমাদের সাথে যোগাযোগ করুন।
            </p>

        </div>
    </section>
    <section class="container py-6 mx-auto">
        <!-- SUPPORT CARDS SECTION -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <!-- Phone Support -->
            <div
                class="bg-white rounded-lg border border-gray-200 p-6 text-center shadow-xs hover:shadow-lg transition-all duration-300 group">
                <div
                    class="w-14 h-14 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-green-600 group-hover:text-white transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-phone">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">ফোন সাপোর্ট</h3>
                <p class="text-md text-gray-600 mb-2">{{ $setup->phone ?? 'নম্বর পাওয়া যায়নি' }}</p>
                <span
                    class="inline-flex items-center rounded-full px-4 py-1.5 text-xs font-bold bg-green-100 text-green-700 uppercase tracking-wider">
                    ২৪/৭ উপলব্ধ
                </span>
            </div>

            <!-- Live Chat (Oren Mart Theme) -->
            <div
                class="bg-white rounded-lg border border-gray-100 p-8 text-center shadow-sm hover:shadow-lg transition-all duration-300 group">
                <div
                    class="w-14 h-14 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-message-circle">
                        <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">লাইভ চ্যাট</h3>
                <p class="text-md text-gray-600 mb-2">তাৎক্ষণিক সাহায্য</p>
                <span
                    class="inline-flex items-center rounded-full px-4 py-1.5 text-xs font-bold bg-blue-100 text-blue-700 uppercase tracking-wider">
                    অনলাইন
                </span>
            </div>

            <!-- Email Support (Oren Mart Theme) -->
            <div
                class="bg-white rounded-lg border border-gray-100 p-8 text-center shadow-sm hover:shadow-lg transition-all duration-300 group">
                <div
                    class="w-14 h-14 bg-orange-100 text-[#FF6A00] rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-[#FF6A00] group-hover:text-white transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-mail">
                        <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">ইমেইল সাপোর্ট</h3>
                <p class="text-md text-gray-600 mb-2">{{ $setup->email ?? 'ইমেইল পাওয়া যায়নি' }}</p>
                <span
                    class="inline-flex items-center rounded-full px-4 py-1.5 text-xs font-bold bg-orange-100 text-[#FF6A00] uppercase tracking-wider">
                    ২৪ ঘণ্টায় উত্তর
                </span>
            </div>

        </div>
    </section>
    <!-- FAQ SEARCH & FILTER SECTION -->
    <section class="container py-6 mx-auto">
        <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
            <div class="flex flex-col lg:flex-row gap-4 items-center">

                <div class="relative w-full lg:flex-1 max-w-3xl">
                    <form action="{{ url()->current() }}" method="GET" class="relative w-full lg:flex-1 max-w-3xl">
                        @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif

                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fas fa-search text-sm"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="প্রশ্ন খুঁজুন..."
                            class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:border-gray-400 outline-none transition-all text-sm text-gray-800">

                    </form>
                </div>

                <div class="flex flex-wrap gap-2 items-center justify-center lg:justify-start">
                        <a href="{{ url()->current() }}"
                            class="flex items-center gap-2 px-5 py-2.5 rounded-lg {{ !request('category') ? 'bg-[#1A1A1A] text-white' : 'bg-white border border-gray-200 text-gray-800' }} font-bold text-sm transition-all">
                            <i class="far fa-question-circle"></i>
                            <span>সব</span>
                        </a>

                        @foreach($categories as $category)
                            <a href="{{ url()->current() . '?category=' . $category . (request('search') ? '&search=' . request('search') : '') }}"
                                class="flex items-center gap-2 px-5 py-2.5 rounded-lg {{ request('category') == $category ? 'bg-[#1A1A1A] text-white' : 'bg-white border border-gray-200 text-gray-800' }} font-bold text-sm hover:bg-gray-50 transition-all">

                                @if($category == 'অর্ডার') <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package h-4 w-4"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path><path d="m7.5 4.27 9 5.15"></path></svg>
                                @elseif($category == 'ডেলিভারি') <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck h-4 w-4"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path><path d="M15 18H9"></path><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path><circle cx="17" cy="18" r="2"></circle><circle cx="7" cy="18" r="2"></circle></svg>
                                @elseif($category == 'পেমেন্ট') <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card h-4 w-4"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>
                                @elseif($category == 'রিটার্ন') <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-rotate-ccw h-4 w-4"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
                                @elseif($category == 'নিরাপত্তা') <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield h-4 w-4"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path></svg>
                                @else <i class="fas fa-tag text-[13px]"></i> @endif

                                <span>{{ $category }}</span>
                            </a>
                        @endforeach
                    </div>

            </div>
        </div>
    </section>
    <!-- FULL FAQ ACCORDION SECTION -->
    <section class="container py-6 mx-auto">
        <h2 class="text-2xl font-black text-gray-900 mb-8 tracking-tight">প্রায়শই জিজ্ঞাসিত প্রশ্ন</h2>

        <div class="space-y-4">
            @forelse($faqs as $faq)
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                    <button onclick="toggleFAQ(this)"
                        class="w-full px-6 py-5 text-left flex items-center justify-between group hover:bg-gray-50 transition-colors">
                        <span class="text-lg font-bold text-gray-800">{{ $faq->title }}</span>
                        <i class="fas fa-chevron-down text-gray-400 text-sm transition-transform duration-300"></i>
                    </button>
                    <div class="max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                        <div class="px-6 pb-5 text-gray-500 text-md border-t border-gray-50 pt-3">
                            {!! nl2br(e($faq->content)) !!}
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <p class="text-gray-500">বর্তমানে কোনো প্রশ্ন ও উত্তর পাওয়া যায়নি।</p>
                </div>
            @endforelse
        </div>
    </section>
    <section class="container py-6 mx-auto">
    <!-- ASK A QUESTION FORM SECTION -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">

        <!-- Title -->
        <h3 class="text-2xl font-black text-gray-900 mb-8 flex items-center gap-3">
            আপনার প্রশ্ন জানান
        </h3>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('support.send') }}" method="POST" class="space-y-6">
            @csrf
            <!-- Name & Email Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">নাম *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="আপনার নাম লিখুন" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-md focus:ring-4 focus:ring-orange-50 focus:border-[#FF6A00] outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">ইমেইল *</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="আপনার ইমেইল লিখুন" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-md focus:ring-4 focus:ring-orange-50 focus:border-[#FF6A00] outline-none transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- বিষয় (Subject) -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">বিষয় *</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" placeholder="প্রশ্নের বিষয়" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-md focus:ring-4 focus:ring-orange-50 focus:border-[#FF6A00] outline-none transition-all">
                </div>

                <!-- ফোন নম্বর (Phone) -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">ফোন নম্বর *</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="আপনার ফোন নম্বর লিখুন" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-md focus:ring-4 focus:ring-orange-50 focus:border-[#FF6A00] outline-none transition-all">
                </div>
            </div>

            <!-- Message Detail -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">বিস্তারিত *</label>
                <textarea name="message" placeholder="আপনার প্রশ্ন বিস্তারিত লিখুন..." rows="4" required
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-md focus:ring-4 focus:ring-orange-50 focus:border-[#FF6A00] outline-none transition-all">{{ old('message') }}</textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full bg-black hover:bg-orange-600 text-white text-sm py-3 rounded-md flex items-center justify-center gap-3 transition-all active:scale-[0.98]">
                প্রশ্ন পাঠান
            </button>
        </form>
    </div>
</section>
    <!-- SUPPORT SCHEDULE SECTION -->
    <section class="container py-6 mx-auto ">
        <div class="bg-orange-200 rounded-lg border border-orange-500 p-6 md:p-8">

            <!-- Header -->
            <div class="flex items-center gap-2 mb-6 text-orange-700">
                <i class="far fa-clock text-lg"></i>
                <h3 class="text-xl font-bold">সাপোর্ট সময়সূচী</h3>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-16">

                <!-- Left Column: Phone Support -->
                <div class="space-y-1">
                    <h4 class="font-bold text-orange-700 mb-2">ফোন সাপোর্ট:</h4>
                    <div class="text-orange-700 text-md space-y-1">
                        <p>রবি - বৃহস্পতি: ৯:00 - ২১:00</p>
                        <p>শুক্রবার: ১৪:00 - ২১:00</p>
                        <p>শনিবার: ৯:00 - ২১:00</p>
                    </div>
                </div>

                <!-- Right Column: Live Chat & Email -->
                <div class="space-y-5">
                    <div class="space-y-1">
                        <h4 class="font-bold text-orange-700 mb-2">লাইভ চ্যাট:</h4>
                        <div class="text-orange-700 text-md space-y-1">
                            <p>সপ্তাহের সব দিন</p>
                            <p>২৪ ঘণ্টা উপলব্ধ</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-orange-700 text-md">
                            <span class="font-bold text-orange-700">ইমেইল:</span> যেকোনো সময়
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
@push('scripts')
    <script>
        function toggleFAQ(button) {
            const content = button.nextElementSibling;
            const icon = button.querySelector('i');

            if (content.style.maxHeight && content.style.maxHeight !== '0px') {
                content.style.maxHeight = '0px';
                icon.style.transform = 'rotate(0deg)';
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
                icon.style.transform = 'rotate(180deg)';
            }
        }
    </script>
@endpush
