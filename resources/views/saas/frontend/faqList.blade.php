@extends('saas.layouts.layout')
@push('styles')
<style>
    html {
        scroll-behavior: smooth;
    }

    .prose ul {
        list-style-type: disc !important;
        padding-left: 1.5rem !important;
        margin-top: 1rem;
        margin-bottom: 1rem;
    }
    .prose li {
        margin-bottom: 0.5rem;
    }
    .prose strong {
        font-weight: 700;
        color: #000;
    }
</style>
@endpush
@section('content')
<div class="bg-white min-h-screen font-sans pb-20">
    <div class="container mx-auto pt-20 pb-10">
        <h1 class="text-center text-2xl md:text-3xl font-bold text-black mb-10">Question/Answer</h1>

        <div class="flex flex-col lg:flex-row gap-10 px-4 md:px-10">

            <aside class="w-full lg:w-[30%]">
                <div class="sticky top-24">
                    <input type="text" id="faqSearch" placeholder="Search FAQs..."
                        class="w-full border border-gray-300 rounded-md px-4 py-2.5 mb-6 outline-none focus:border-blue-400 text-sm">

                    <ul class="space-y-4">
                        @foreach($faqs as $index => $faq)
                            <li class="faq-link-item">
                                <a href="#faq-{{ $faq->id }}"
                                   class="text-[#0b2d24] hover:underline text-base leading-snug block transition-colors">
                                    {{ $index + 1 }}. {{ $faq->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>

            <main class="w-full lg:w-[70%] space-y-5">
                @forelse($faqs as $index => $faq)
                    <div id="faq-{{ $faq->id }}" class="scroll-mt-24 faq-section-block">
                        <div class="bg-[#34a487] px-4 py-2.5 text-white font-medium text-base border border-gray-200">
                            {{ $index + 1 }}. {{ $faq->title }}
                        </div>

                        <div class="py-4 px-1 text-[#333] leading-relaxed text-base prose max-w-none">
                            {!! $faq->content !!}
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-10">No questions found.</p>
                @endforelse
            </main>

        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
    document.getElementById('faqSearch').addEventListener('input', function() {
        let filter = this.value.toLowerCase();
        let links = document.querySelectorAll('.faq-link-item');
        let blocks = document.querySelectorAll('.faq-section-block');

        links.forEach((link, i) => {
            let text = link.innerText.toLowerCase();
            if(text.includes(filter)) {
                link.style.display = "block";
                blocks[i].style.display = "block";
            } else {
                link.style.display = "none";
                blocks[i].style.display = "none";
            }
        });
    });
</script>
@endpush
