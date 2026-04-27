{{-- resources/views/template1/partials/payments/card.blade.php --}}
@props(['method', 'mode' => 'modal'])

<div class="payment-form hidden" id="form-card">
    <div class="space-y-3">
        {{-- Instructions Box (Blue Theme like bKash) --}}
        <div class="p-4 rounded-xl text-sm" style="background:#f0f7ff; border:1px solid #2563eb30;">
            <p class="text-[#2563eb] font-medium mb-1">Card Payment Instructions:</p>
            <span class="text-xs text-blue-700 leading-tight">
                Please pay via your card and provide the Transaction ID/Approval Code and the last 4 digits of your card below.
            </span>
        </div>

        {{-- ড্যাশবোর্ড মোডে থাকলে ফর্ম ট্যাগ থাকবে --}}
        @if($mode === 'modal')
        <form onsubmit="handlePaymentSubmit(event, 'card')" method="POST" action="{{ route('order.payment.submit') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="hidden" name="order_id" id="card-order-id">
            <input type="hidden" name="payment_method" value="card">
        @endif

            {{-- Amount --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Amount <span class="text-red-500">*</span></label>
                <input type="number" name="amount" id="card-amount" placeholder="Paid amount" step="0.01" min="1"
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm bg-white focus:outline-none focus:border-[#2563eb]">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                {{-- Card Type --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Card Type <span class="text-red-500">*</span></label>
                    <select name="card_type" class="w-full px-3 py-2 border border-gray-300 rounded text-sm bg-white focus:outline-none focus:border-[#2563eb]">
                        <option value="Visa">Visa Card</option>
                        <option value="Mastercard">Mastercard</option>
                        <option value="Amex">Amex</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                {{-- Transaction ID --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Transaction / Approval ID <span class="text-red-500">*</span></label>
                    <input type="text" name="transaction_id" placeholder="e.g. 524103"
                        oninput="this.value=this.value.toUpperCase()"
                        class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-mono bg-white focus:outline-none focus:border-[#2563eb]">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                {{-- Reference --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Reference No</label>
                    <input type="text" name="reference_no" placeholder="e.g. REF123"
                        oninput="this.value=this.value.toUpperCase()"
                        class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-mono bg-white focus:outline-none focus:border-[#2563eb]">
                </div>
                {{-- Last 4 Digits --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Last 4 Digits <span class="text-red-500">*</span></label>
                    <input type="text" name="sender_number" placeholder="e.g. 1234" maxlength="4" pattern="\d{4}"
                        class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-mono bg-white focus:outline-none focus:border-[#2563eb]">
                </div>
            </div>

            {{-- ── Multi Screenshot Upload (Same Logic as bKash) ── --}}
            <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-2">
                    Payment Receipts <span class="normal-case text-gray-400 font-normal">(max 3)</span>
                </label>
                <div id="card-preview-grid" class="flex flex-wrap gap-2 mb-2"></div>
                <div id="card-dropzone" class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center bg-white cursor-pointer hover:border-blue-400 transition-colors"
                    onclick="document.getElementById('card-file-input').click()">
                    <i class="fas fa-plus-circle text-gray-300 text-2xl mb-1"></i>
                    <p class="text-[11px] text-gray-400">Click to add receipts <span id="card-count-label">(0/3)</span></p>
                </div>
                <input type="file" id="card-file-input" accept="image/*" class="hidden" onchange="cardAddImage(this)">
                <div id="card-hidden-inputs"></div>
            </div>

            {{-- সাবমিট বাটন (শুধুমাত্র মোডাল মোডে দেখাবে) --}}
            @if($mode === 'modal')
            <button type="submit" class="w-full py-2.5 rounded text-white font-semibold text-sm hover:bg-blue-700 transition-all shadow-md"
                style="background:#2563eb;">
                <i class="fas fa-check-circle mr-1"></i> Confirm Card Payment
            </button>
        </form>
        @endif
    </div>
</div>

<script>
    const cardFiles = [];
    const card_MAX = 3;

    function cardAddImage(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        input.value = '';
        if (cardFiles.length >= card_MAX) { alert('Maximum ' + card_MAX + ' allowed.'); return; }
        cardFiles.push(file);
        cardRenderPreviews();
        cardSyncHiddenInputs();
    }

    function cardRemoveImage(index) {
        cardFiles.splice(index, 1);
        cardRenderPreviews();
        cardSyncHiddenInputs();
    }

    function cardRenderPreviews() {
        const grid = document.getElementById('card-preview-grid');
        const countLabel = document.getElementById('card-count-label');
        const dropzone = document.getElementById('card-dropzone');
        grid.innerHTML = '';
        cardFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative w-20 h-20 border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm group';
                div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">
                    <button type="button" onclick="cardRemoveImage(${index})" class="absolute top-0 right-0 bg-red-500 text-white w-5 h-5 flex items-center justify-center text-xs rounded-bl-lg opacity-0 group-hover:opacity-100 transition-all">&times;</button>`;
                grid.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
        countLabel.innerText = `(${cardFiles.length}/${card_MAX})`;
        dropzone.style.display = cardFiles.length >= card_MAX ? 'none' : 'block';
    }

    function cardSyncHiddenInputs() {
        const container = document.getElementById('card-hidden-inputs');
        container.innerHTML = '';
        cardFiles.forEach((file) => {
            const dt = new DataTransfer();
            dt.items.add(file);
            const inp = document.createElement('input');
            inp.type = 'file';
            inp.name = 'screenshots[]';
            inp.style.display = 'none';
            inp.files = dt.files;
            container.appendChild(inp);
        });
    }
</script>
