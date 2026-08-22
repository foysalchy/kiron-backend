<div class="flex flex-col gap-6 text-left">
    <!-- product info -->
    <div class="flex gap-4 border-b border-gray-100 pb-4">
        <div class="w-20 h-20 bg-gray-50 rounded-xl overflow-hidden border">
            <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-contain">
        </div>
        <div class="flex-1">
            <h4 class="font-bold text-gray-800 text-md leading-tight">{{ $product->title }}</h4>
            @php $firstVar = $product->variations->first(); @endphp
            <!-- unit price -->
            <p class="text-gray-500 text-sm mt-1">
                Unit Price: <span id="modal-unit-price">৳{{ $firstVar ? number_format($firstVar->final_price) : number_format($product->sale_price) }}</span>
            </p>
            <!-- total price -->
            <p class="text-[var(--primary-color)] font-semibold text-xl mt-0.5" id="modal-total-price-display">
                ৳{{ $firstVar ? number_format($firstVar->final_price) : number_format($product->sale_price) }}
            </p>
        </div>
    </div>

    <!-- variation list -->
    <div class="space-y-4">
        <p class="text-sm font-bold text-gray-600 uppercase tracking-wide">Select a variation:</p>
        <div class="grid grid-cols-1 gap-2 max-h-[200px] overflow-y-auto pr-1 custom-scrollbar">
            @foreach($product->variations as $variation)
                <label class="relative block">
                    <!-- data-price  onchange updateModalTotal()  -->
                    <input type="radio" name="selected_variant" value="{{ $variation->id }}"
                           data-price="{{ $variation->final_price }}"
                           onchange="updateModalTotal()"
                           class="peer hidden" {{ $loop->first ? 'checked' : '' }}>

                    <div class="flex justify-between items-center border border-gray-200 p-3 rounded-xl cursor-pointer hover:bg-gray-50 peer-checked:border-[var(--primary-color)] peer-checked:bg-orange-50 transition-all">
                        <span class="text-sm font-bold text-gray-700">{{ $variation->display_name }}</span>
                        <span class="text-[var(--primary-color)] ">৳{{ number_format($variation->final_price) }}</span>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    <!-- add to cart button-->
    <div class="flex items-center gap-4 border-t pt-5">
        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
            <button type="button" onclick="changeQty(-1)" class="px-3 py-2 bg-gray-50 hover:bg-gray-100 font-bold">-</button>
            <input type="number" id="modal-qty" value="1" readonly class="w-12 text-center text-sm font-bold outline-none border-none">
            <button type="button" onclick="changeQty(1)" class="px-3 py-2 bg-gray-50 hover:bg-gray-100 font-bold">+</button>
        </div>

        <button type="button" onclick="processAddVariation()"
            class="flex-1 bg-[#1D2128] text-primary py-3 rounded-xl font-bold hover:bg-[var(--primary-color)] transition-all shadow-lg flex items-center justify-center gap-2 cursor-pointer">
            <i id="modal-btn-icon" class="fas fa-shopping-cart text-sm"></i>
            <span id="modal-btn-text">Add to Cart</span>
        </button>
    </div>
</div>
