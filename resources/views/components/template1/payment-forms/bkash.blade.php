{{-- resources/views/template1/partials/payments/bkash.blade.php --}}
@props(['method', 'mode' => 'modal'])

<div class="payment-form hidden" id="form-bkash">
    <div class="space-y-3">
        @if ($method)
            <div class="p-4 rounded-xl text-sm" style="background:#fdf2f7; border:1px solid #e2136e30;">
                <p class="text-[#e2136e] font-medium mb-1">Send money to this bKash number:</p>
                <span class="text-lg font-black text-[#e2136e]">
                    {{ $method->account_number ?? $method->phone }}
                </span>
                <p class="text-[10px] text-pink-400 mt-1 uppercase font-bold">Type:
                    {{ $method->method_details['type'] ?? 'Personal/Merchant' }}</p>
            </div>
        @endif

        @if ($mode === 'modal')
            <form onsubmit="handlePaymentSubmit(event, 'bkash')" method="POST"
                action="{{ route('order.payment.submit') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="hidden" name="order_id" id="bkash-order-id">
                <input type="hidden" name="payment_method" value="bkash">
        @endif

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Amount <span
                    class="text-red-500">*</span></label>
            <input type="number" name="amount" id="bkash-amount" placeholder="Paid amount" step="0.01"
                min="1"
                class="w-full px-3 py-2 border border-gray-300 rounded text-sm bg-white focus:outline-none focus:border-[#e2136e]">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Transaction ID <span
                    class="text-red-500">*</span></label>
            <input type="text" name="transaction_id" placeholder="e.g. 8D6XXXXXXX"
                oninput="this.value=this.value.toUpperCase()"
                class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-mono bg-white focus:outline-none focus:border-[#e2136e]">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Reference no <span
                    class="text-red-500">*</span></label>
            <input type="text" name="reference_no" placeholder="e.g. 8D6XXXXXXX"
                oninput="this.value=this.value.toUpperCase()"
                class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-mono bg-white focus:outline-none focus:border-[#e2136e]">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Sender bKash Number <span
                    class="text-red-500">*</span></label>
            <input type="tel" name="sender_number" placeholder="01XXXXXXXXX" maxlength="11"
                class="w-full px-3 py-2 border border-gray-300 rounded text-sm bg-white focus:outline-none focus:border-[#e2136e]">
        </div>

        <div>
            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-2">
                Payment Screenshots <span class="normal-case text-gray-400 font-normal">(max 3)</span>
            </label>
            <div id="bkash-preview-grid" class="flex flex-wrap gap-2 mb-2"></div>
            <div id="bkash-dropzone"
                class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center bg-white cursor-pointer hover:border-gray-400 transition-colors"
                onclick="document.getElementById('bkash-file-input').click()">
                <i class="fas fa-plus-circle text-gray-300 text-2xl mb-1"></i>
                <p class="text-[11px] text-gray-400">Click to add screenshot <span id="bkash-count-label">(0/3)</span>
                </p>
            </div>
           <input type="file" id="bkash-file-input" accept="image/*" class="hidden" onchange="bkashAddImage(this)">
            <div id="bkash-hidden-inputs"></div>
        </div>

        @if ($mode === 'modal')
            <button type="submit" class="w-full py-2.5 rounded text-white font-semibold text-sm hover:opacity-90"
                style="background:#e2136e;">
                <i class="fas fa-check-circle mr-1"></i> Confirm bKash Payment
            </button>
            </form>
        @endif
    </div>
</div>

<script>
    const bkashFiles = [];
    const bkash_MAX = 3;

    function bkashAddImage(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        input.value = '';
        if (bkashFiles.length >= bkash_MAX) {
            alert('Max ' + bkash_MAX + ' allowed.');
            return;
        }
        bkashFiles.push(file);
        bkashRenderPreviews();
        bkashSyncHiddenInputs();
    }

    function bkashRemoveImage(index) {
        bkashFiles.splice(index, 1);
        bkashRenderPreviews();
        bkashSyncHiddenInputs();
    }

    function bkashRenderPreviews() {
        const grid = document.getElementById('bkash-preview-grid');
        const countLabel = document.getElementById('bkash-count-label');
        const dropzone = document.getElementById('bkash-dropzone');
        grid.innerHTML = '';
        bkashFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className =
                    'relative w-20 h-20 border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm group';
                div.innerHTML =
                    `<img src="${e.target.result}" class="w-full h-full object-cover">
                    <button type="button" onclick="bkashRemoveImage(${index})" class="absolute top-0 right-0 bg-red-500 text-white w-5 h-5 flex items-center justify-center text-xs rounded-bl-lg opacity-0 group-hover:opacity-100 transition-all">&times;</button>`;
                grid.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
        countLabel.innerText = `(${bkashFiles.length}/${bkash_MAX})`;
        dropzone.style.display = bkashFiles.length >= bkash_MAX ? 'none' : 'block';
    }

    function bkashSyncHiddenInputs() {
        const container = document.getElementById('bkash-hidden-inputs');
        container.innerHTML = '';
        bkashFiles.forEach((file) => {
            const dt = new DataTransfer();
            dt.items.add(file);
            const inp = document.createElement('input');
            inp.type = 'file';
            inp.name = 'screenshots[]';
            inp.style.display = 'none';
            inp.files = dt.files;
             inp.disabled = false;
            container.appendChild(inp);
        });
    }
</script>
