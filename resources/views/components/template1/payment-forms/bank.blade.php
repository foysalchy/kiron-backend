{{-- resources/views/template1/partials/payments/bank.blade.php --}}
@props(['method'])
<div class="payment-form hidden" id="form-bank">
    <div class="mt-4 p-4 border border-gray-200 rounded-lg space-y-3 bg-gray-50">

        @if($method)
            <div class="p-4 rounded-xl text-sm bg-yellow-50 border border-yellow-200 shadow-sm space-y-2">
                <p class="text-[10px] font-black text-yellow-800 uppercase tracking-widest mb-1">Bank Transfer Details:</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-gray-700">
                    <div>
                        <span class="text-[11px] font-medium text-gray-500">Bank Name:</span>
                        <p class="font-bold text-gray-900 uppercase">{{ $method->name }}</p>
                    </div>
                    <div>
                        <span class="text-[11px] font-medium text-gray-500">Branch Name:</span>
                        <p class="font-bold text-gray-900">{{ $method->method_details['branch_name'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-[11px] font-medium text-gray-500">Account Holder:</span>
                        <p class="font-bold text-gray-900">{{ $method->account_holder }}</p>
                    </div>
                    <div>
                        <span class="text-[11px] font-medium text-gray-500">Account Number:</span>
                        <p class="font-bold text-gray-900 font-mono tracking-wider">{{ $method->account_number }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="p-3 text-xs text-red-500 bg-red-50 rounded">
                Bank payment details not found in database.
            </div>
        @endif

        <form action="{{ route('order.payment.submit') }}" method="POST"
            enctype="multipart/form-data" class="space-y-3" id="bank-payment-form">
            @csrf
            <input type="hidden" name="order_id"       id="bank-order-id">
            <input type="hidden" name="payment_method" value="bank">

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Amount <span class="text-red-500">*</span></label>
                <input type="number" name="amount" id="bank-amount"
                    placeholder="Paid amount" step="0.01" min="1"
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm bg-white focus:outline-none focus:border-gray-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Transaction ID <span class="text-red-500">*</span></label>
                <input type="text" name="transaction_id" placeholder="Transaction number"
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-mono bg-white focus:outline-none focus:border-gray-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Reference Number</label>
                <input type="text" name="reference_no" placeholder="e.g. REF-XXXXXXX"
                    oninput="this.value=this.value.toUpperCase()"
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-mono bg-white focus:outline-none focus:border-gray-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Your Account Number <span class="text-red-500">*</span></label>
                <input type="text" name="sender_number" placeholder="Sender account number"
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-mono bg-white focus:outline-none focus:border-gray-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Your Bank Name</label>
                <input type="text" name="bank_name" placeholder="e.g. Dutch-Bangla Bank"
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm bg-white focus:outline-none focus:border-gray-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Branch Name</label>
                <input type="text" name="branch_name" placeholder="e.g. Dhaka"
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm bg-white focus:outline-none focus:border-gray-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Account Holder Name</label>
                <input type="text" name="holder_name" placeholder="e.g. John Doe"
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm bg-white focus:outline-none focus:border-gray-500">
            </div>

            {{-- ── Multi Screenshot Upload ── --}}
            <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-2">
                    Payment Screenshots <span class="normal-case text-gray-400 font-normal">(max 3)</span>
                </label>

                {{-- Preview Grid --}}
                <div id="bank-preview-grid" class="flex flex-wrap gap-2 mb-2"></div>

                {{-- Drop Zone --}}
                <div id="bank-dropzone"
                    class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center bg-white cursor-pointer hover:border-gray-400 transition-colors"
                    onclick="document.getElementById('bank-file-input').click()">
                    <i class="fas fa-plus-circle text-gray-300 text-2xl mb-1"></i>
                    <p class="text-[11px] text-gray-400">Click to add screenshot <span id="bank-count-label">(0/3)</span></p>
                </div>

                {{-- Hidden real input (single, not multiple — we manage files manually) --}}
                <input type="file" id="bank-file-input" accept="image/*" class="hidden"
                    onchange="bankAddImage(this)">

                {{-- Hidden inputs container — one per image --}}
                <div id="bank-hidden-inputs"></div>
            </div>

            <button type="submit"
                class="w-full py-2.5 rounded bg-gray-700 text-white font-semibold text-sm hover:bg-gray-800 transition-all">
                <i class="fas fa-check-circle mr-1"></i> Confirm Bank Payment
            </button>
        </form>
    </div>
</div>

<script>
    // Store Files as array (not DataTransfer) — avoids browser bugs
    const bankFiles = [];
    const BANK_MAX = 3;

    function bankAddImage(input) {
        if (!input.files || !input.files[0]) return;

        const file = input.files[0];
        input.value = ''; // reset so same file can be re-added if needed

        if (bankFiles.length >= BANK_MAX) {
            alert('Maximum ' + BANK_MAX + ' screenshots allowed.');
            return;
        }

        bankFiles.push(file);
        bankRenderPreviews();
        bankSyncHiddenInputs();
    }

    function bankRemoveImage(index) {
        bankFiles.splice(index, 1);
        bankRenderPreviews();
        bankSyncHiddenInputs();
    }

    function bankRenderPreviews() {
        const grid = document.getElementById('bank-preview-grid');
        const countLabel = document.getElementById('bank-count-label');
        const dropzone = document.getElementById('bank-dropzone');

        grid.innerHTML = '';

        bankFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative w-20 h-20 border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm group';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <button type="button"
                        onclick="bankRemoveImage(${index})"
                        class="absolute top-0 right-0 bg-red-600 text-white w-5 h-5 flex items-center justify-center text-xs rounded-bl-lg opacity-0 group-hover:opacity-100 transition-all">
                        &times;
                    </button>
                `;
                grid.appendChild(div);
            };
            reader.readAsDataURL(file);
        });

        countLabel.innerText = `(${bankFiles.length}/${BANK_MAX})`;

        // Hide dropzone if max reached
        dropzone.style.display = bankFiles.length >= BANK_MAX ? 'none' : 'block';
    }

    function bankSyncHiddenInputs() {
        // We create a hidden file input per image using DataTransfer trick per-file
        const container = document.getElementById('bank-hidden-inputs');
        container.innerHTML = '';

        bankFiles.forEach((file, index) => {
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
