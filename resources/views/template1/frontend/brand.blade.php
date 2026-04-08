@extends('template1.layouts.front')
@section('content')
<!-- ALL BRANDS GRID SECTION -->
<section class="py-6 container mx-auto">
    <div class="">
        <h2 class="text-2xl font-black text-[#1D2128] mb-8">All Brands</h2>

        <!-- Brands Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($brands as $brand)
                <a href="{{ url('brand/' . $brand->slug) }}" class="group block h-full">
                    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-xs group-hover:shadow-xl group-hover:border-orange-100 transition-all duration-300">
                        <div class="flex items-start gap-4">
                            <!-- Logo Section -->
                            <div class="w-16 h-16 bg-gray-50 rounded-xl flex items-center justify-center group-hover:bg-orange-50 shrink-0 transition-colors overflow-hidden">
                                <img src="{{ $brand->logo_url ?? asset('./images/template1/frontend/default.webp')}}" alt="{{ $brand->name }}" class="w-full h-full object-contain p-2">

                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-[#FF6A00] truncate transition-colors">
                                        {{ $brand->name }}
                                    </h3>
                                    <!-- You can add a 'is_featured' check here if you add that column to your DB -->
                                    @if($loop->iteration <= 3)
                                        <span class="px-2.5 py-0.5 bg-[#FF6A00] text-white text-[10px] font-bold rounded-full uppercase tracking-wider">Featured</span>
                                    @endif
                                </div>

                                <p class="text-md text-gray-500 mb-4 line-clamp-2 leading-relaxed">
                                    {{-- Use a description field if you have one, otherwise a placeholder --}}
                                    Quality products from {{ $brand->name }}.
                                </p>

                                <div class="flex items-center justify-between">
                                    <span class="px-2.5 py-1 bg-gray-100 text-gray-600 text-[11px] font-bold rounded-lg group-hover:bg-orange-100 group-hover:text-[#FF6A00] transition-colors uppercase">
                                        Official Brand
                                    </span>
                                    <span class="text-sm text-gray-500 tracking-tight">
                                        {{ $brand->products_count }}+ Products
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-3 text-center py-10">
                    <p class="text-gray-500">No brands found.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
