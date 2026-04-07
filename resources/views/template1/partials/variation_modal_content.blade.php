<div class="flex flex-col gap-6 text-left">
    <!-- প্রোডাক্ট ইনফো -->
    <div class="flex gap-4 border-b border-gray-100 pb-4">
        <div class="w-20 h-20 bg-gray-50 rounded-xl overflow-hidden border">
            <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-contain">
        </div>
        <div class="flex-1">
            <h4 class="font-bold text-gray-800 text-md leading-tight">{{ $product->title }}</h4>
            <p class="text-[#FF6A00] font-black text-xl mt-1" id="modal-price">৳{{ number_format($product->sale_price) }}</p>
        </div>
    </div>

    <!-- ভেরিয়েশন লিস্ট -->
    <div class="space-y-4">
        <p class="text-sm font-bold text-gray-600 uppercase tracking-wide">একটি ভেরিয়েশন সিলেক্ট করুন:</p>
        <div class="grid grid-cols-1 gap-2 max-h-[200px] overflow-y-auto pr-1 custom-scrollbar">
            @foreach($product->variations as $variation)
                <label class="relative block">
                    <input type="radio" name="selected_variant" value="{{ $variation->id }}"
                           onchange="document.getElementById('modal-price').innerText = '৳' + '{{ number_format($variation->final_price) }}'"
                           class="peer hidden" {{ $loop->first ? 'checked' : '' }}>
                    <div class="flex justify-between items-center border border-gray-200 p-3 rounded-xl cursor-pointer hover:bg-gray-50 peer-checked:border-[#FF6A00] peer-checked:bg-orange-50 transition-all">
                        <span class="text-sm font-bold text-gray-700">{{ $variation->display_name }}</span>
                        <span class="text-[#FF6A00] font-bold">৳{{ number_format($variation->final_price) }}</span>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    <!-- পরিমাণ এবং অ্যাড বাটন -->
    <div class="flex items-center gap-4 border-t pt-5">
        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
            <button type="button" onclick="changeQty(-1)" class="px-3 py-2 bg-gray-50 hover:bg-gray-100 font-bold">-</button>
            <input type="number" id="modal-qty" value="1" readonly class="w-12 text-center text-sm font-bold outline-none border-none">
            <button type="button" onclick="changeQty(1)" class="px-3 py-2 bg-gray-50 hover:bg-gray-100 font-bold">+</button>
        </div>

        <button type="button" onclick="processAddVariation()"
            class="flex-1 bg-[#1D2128] text-white py-3 rounded-xl font-bold hover:bg-[#FF6A00] transition-all shadow-lg flex items-center justify-center gap-2">
            <i class="fas fa-shopping-cart text-sm"></i>
            Add to Cart
        </button>
    </div>
</div>
