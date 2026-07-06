@extends('saas.layouts.layout')
@section('meta')

@php
$pageData = \App\Services\Saas\SystemPageService::get(
\App\Enums\SystemPageType::FAQ,
$setup->company_id ?? null
);
@endphp

@include('components.meta-info.saas-meta', [
'setup' => $setup,
'type' => 'FAQPage',

'title' => $pageData->meta_title ?? 'Frequently Asked Questions',
'description' => $pageData->meta_description ?? 'Frequently Asked Questions',

'faq' => $faqs->map(function ($item) {
return [
'question' => $item->title,
'answer' => strip_tags($item->content),
];
})->toArray(),
])
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

    .faq-section-block:target {
        background-color: #f0faf7;
        border-radius: 12px;
        padding: 10px;
        transition: all 0.5s ease-in-out;
    }

    .faq-section-block:target .question-header {
        background-color: #2c8a71;
        box-shadow: 0 4px 15px rgba(52, 164, 135, 0.3);
        transform: scale(1.01);
    }

    .active-link {
        color: #22705d !important;
        font-weight: 700 !important;
        text-decoration: underline !important;
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
                        @foreach ($faqs as $index => $faq)
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
                @foreach ($faqs as $index => $faq)
                <div id="faq-{{ $faq->id }}"
                    class="scroll-mt-32 faq-section-block transition-all duration-500">

                    <div
                        class="question-header bg-[#22705d] px-4 py-3 text-white font-bold text-base border border-gray-200 rounded-lg transition-all duration-300">
                        {{ $index + 1 }}. {{ $faq->title }}
                    </div>

                    <div class="py-4 px-2 text-[#444] leading-loose text-base prose max-w-none">
                        {!! $faq->content !!}
                    </div>
                </div>
                @endforeach
            </main>

        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    const faqLinks = document.querySelectorAll('.faq-link-item a');

    faqLinks.forEach(link => {
        link.addEventListener('click', function() {
            faqLinks.forEach(l => l.classList.remove('active-link'));
            this.classList.add('active-link');
        });
    });

    document.getElementById('faqSearch').addEventListener('input', function() {
        let filter = this.value.toLowerCase();
        let links = document.querySelectorAll('.faq-link-item');
        let blocks = document.querySelectorAll('.faq-section-block');

        links.forEach((link, i) => {
            let text = link.innerText.toLowerCase();
            if (text.includes(filter)) {
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
