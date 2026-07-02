@extends('saas.layouts.layout')

@section('content')
    <!-- HEADER SECTION -->
    <section class="bg-[#34a487] pt-32 pb-20 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#34a487]/10 blur-[120px] rounded-full"></div>
        <div class="container mx-auto px-6 text-center relative z-10">
            <h1 class="text-white text-4xl md:text-6xl font-black mb-6">আমাদের সব ফিচারসমূহ</h1>
            <p class="text-gray-800 text-lg md:text-xl max-w-2xl mx-auto">
                আপনার ব্যবসাকে ডিজিটাল করার জন্য প্রয়োজনীয় সব টুলস এবং ফিচার রয়েছে আমাদের এই একটি প্ল্যাটফর্মে।
            </p>
        </div>
    </section>

    <!-- FEATURES GRID SECTION -->
    <section class="bg-white py-10">
        <div class="container mx-auto px-6 md:px-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($allFeatures as $feature)
                        <div
                            class="bg-[#f9faff] p-8 md:p-12 rounded-[40px] border border-indigo-100 transition-all duration-300 group hover:shadow-xl hover:shadow-indigo-500/5">
                            <!-- Icon Area -->
                            <div
                                class="w-20 h-20 rounded-full border border-indigo-300 bg-white flex items-center justify-center mb-8">
                                <i class="{{ $feature->icon ?? 'fa-solid fa-file-lines' }} text-3xl text-[#34a487]"></i>
                            </div>

                            <!-- Content Area -->
                            <h3 class="text-2xl md:text-3xl font-bold text-[#34a487] mb-5">
                                {{ $feature->title ?? '' }}
                            </h3>
                            <p class="text-gray-700 text-lg leading-relaxed mb-12">
                                {{ $feature->subtitle ?? '' }}
                            </p>

                            <!-- Link Area -->
                            <a href="{{ route('saas.feature.details', $feature->slug) }}"
                                class="inline-flex items-center gap-3 font-bold text-gray-900 group-hover:text-[#34a487] transition-colors text-lg">
                                বিস্তারিত জানুন
                                <i class="fa-solid fa-arrow-right text-sm"></i>
                            </a>
                        </div>
                    @endforeach
            </div>

            <div class="mt-16">
                {{ $allFeatures->links() }}
            </div>
        </div>
    </section>
@endsection
