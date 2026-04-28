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
                FAQ
            </h1>

            <!-- Description -->
            <p class="text-sm md:text-xl text-gray-700 leading-relaxed max-w-2xl mx-auto font-medium">
                We are always ready at your service. Please contact us to solve any of your issues.
            </p>

        </div>
    </section>

    <!-- FAQ SEARCH & FILTER SECTION -->

    <!-- FULL FAQ ACCORDION SECTION -->
    <section class="container py-6 mx-auto">
        <h2 class="text-2xl font-black text-gray-900 mb-8 tracking-tight">Frequently Asked Questions</h2>

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
                    <p class="text-gray-500">No questions or answers were found at this time.</p>
                </div>
            @endforelse
        </div>
    </section>
    <section class="container py-6 mx-auto">
        <!-- ASK A QUESTION FORM SECTION -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">

            <!-- Title -->
            <h3 class="text-2xl font-black text-gray-900 mb-8 flex items-center gap-3">
                Ask your question.
            </h3>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('support.send') }}" method="POST" class="space-y-6">
                @csrf
                <!-- Name & Email Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter Your Name"
                            required
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-md focus:ring-4 focus:ring-orange-50 focus:border-[#FF6A00] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter Your Email"
                            required
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-md focus:ring-4 focus:ring-orange-50 focus:border-[#FF6A00] outline-none transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- বিষয় (Subject) -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Subject *</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Subject.." required
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-md focus:ring-4 focus:ring-orange-50 focus:border-[#FF6A00] outline-none transition-all">
                    </div>

                    <!-- ফোন নম্বর (Phone) -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Phone *</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="Enter Your Phone"
                            required
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-md focus:ring-4 focus:ring-orange-50 focus:border-[#FF6A00] outline-none transition-all">
                    </div>
                </div>

                <!-- Message Detail -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Details *</label>
                    <textarea name="message" placeholder="Write your question in detail...." rows="4" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-md focus:ring-4 focus:ring-orange-50 focus:border-[#FF6A00] outline-none transition-all">{{ old('message') }}</textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-black hover:bg-orange-600 text-white text-sm py-3 rounded-md flex items-center justify-center gap-3 transition-all active:scale-[0.98]">
                    Send
                </button>
            </form>
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
