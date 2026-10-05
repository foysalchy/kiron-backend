<div class="flex flex-col gap-6 text-left">
    <!-- product info -->
    <div class="flex gap-4 border-b border-gray-100 pb-4">
        <div class="w-20 h-20 bg-gray-50 rounded-xl overflow-hidden border">
            <img src="{{ $product- loading="lazy" width="800" height="800">thumbnail_url }}" class="w-full h-full object-contain">
        </div>
        <div class="flex-1">
            <h4 class="font-bold text-gray-800 text-md leading-tight">{{ $product->title }}</h4>
            @php $firstVar = $product->variations->first(); @endphp
            <!-- unit price -->
            <p class="text-gray-500 text-sm mt-1">
                Unit Price: <span id="modal-unit-price">
                    {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }}
                    {{ $firstVar ? number_format($firstVar->final_price) : number_format($product->sale_price) }}
                    {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}
                </span>
            </p>
            <p class="text-[var(--primary-color)] font-semibold text-xl mt-0.5" id="modal-total-price-display">
                {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }}
                {{ $firstVar ? number_format($firstVar->final_price) : number_format($product->sale_price) }}
                {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}
            </p>
        </div>
    </div>

    <!-- variation list -->
    <div class="space-y-4">
        <p class="text-sm font-bold text-gray-600 uppercase tracking-wide">Select a variation:</p>
        <div class="grid grid-cols-1 gap-2 max-h-[200px] overflow-y-auto pr-1 custom-scrollbar">
            <div class="grid grid-cols-1 gap-2 max-h-[200px] overflow-y-auto pr-1">
                @foreach($product->variations as $variation)
                    @php
                        $groupName = $variation->attributes->first()->attributeGroup->name ?? '';
                        $type = $groupCategories[$groupName] ?? 'single';
                    @endphp

                    <button type="button"
                        onclick="handleModalSelection(this, {{ $variation->id }}, {{ $variation->final_price }}, '{{ $type }}')"
                        data-price="{{ $variation->final_price }}" data-var-id="{{ $variation->id }}" {{-- এই লাইনটি যোগ
                        করুন --}}
                        class="modal-var-btn flex justify-between items-center border p-3 rounded-xl transition-all w-full mb-2 outline-none {{ $loop->first ? 'border-[var(--primary-color)] bg-[var(--primary-color)/10] ring-1 ring-[var(--primary-color)]' : 'border-gray-100 bg-white' }}">

                        <span class="text-sm font-bold text-gray-700">{{ $variation->display_name }}</span>
                        <span class="text-[var(--primary-color)] font-extrabold">
                            {{ number_format($variation->final_price) }} {{ $setup->currency }}
                        </span>
                    </button>
                @endforeach
            </div>
            <input type="hidden" id="modal-selected-ids" value="">
        </div>

        <!-- add to cart button-->
        <div class="flex items-center gap-4 border-t pt-5">
            <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                <button type="button" onclick="changeQty(-1)"
                    class="px-3 py-2 bg-gray-50 hover:bg-gray-100 font-bold">-</button>
                <input type="number" id="modal-qty" value="1" readonly
                    class="w-12 text-center text-sm font-bold outline-none border-none">
                <button type="button" onclick="changeQty(1)"
                    class="px-3 py-2 bg-gray-50 hover:bg-gray-100 font-bold">+</button>
            </div>

            <button type="button" onclick="processAddVariation()"
                class="flex-1 bg-[var(--primary-color)] text-white py-3 rounded-xl font-bold hover:bg-[var(--secondary-color)] transition-all shadow-lg flex items-center justify-center gap-2 cursor-pointer">
                <i id="modal-btn-icon" class="fas fa-shopping-cart text-sm"></i>
                <span id="modal-btn-text">Add to Cart</span>
            </button>
        </div>
    </div>
