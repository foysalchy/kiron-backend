{{-- resources/views/components/custom-pagination.blade.php --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center gap-4 md:gap-6 py-8">

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="text-gray-600 cursor-not-allowed text-sm font-medium flex items-center gap-1 select-none">
                ← Prev
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="text-gray-600 hover:text-[#FF6A00] transition-all text-sm font-medium flex items-center gap-1">
                ← Prev
            </a>
        @endif

        {{-- Pagination Elements (Numbers) --}}
        <div class="flex items-center gap-1 md:gap-2">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="px-2 text-gray-600">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            {{-- Active Page (আপনার ছবির মতো হাইলাইটেড বক্স) --}}
                            <span aria-current="page" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-[#FF6A00] text-white rounded-lg text-sm font-black shadow-lg shadow-orange-100">
                                {{ $page }}
                            </span>
                        @else
                            {{-- Other Pages (শুধু টেক্সট) --}}
                            <a href="{{ $url }}" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center text-gray-500 hover:text-[#FF6A00] hover:bg-orange-50 rounded-lg transition-all text-sm font-bold">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="text-gray-600 hover:text-[#FF6A00] transition-all text-sm font-medium flex items-center gap-1">
                Next →
            </a>
        @else
            <span class="text-gray-600 cursor-not-allowed text-sm font-medium flex items-center gap-1 select-none">
                Next →
            </span>
        @endif
    </nav>
@endif
