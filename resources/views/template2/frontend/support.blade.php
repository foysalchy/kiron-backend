@extends('template2.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.faq-meta', ['setup' => $setup])
@endsection
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
