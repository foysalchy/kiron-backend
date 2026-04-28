@props(['method', 'mode' => 'modal'])
<div class="payment-form hidden" id="form-nagad">
    <div class="mt-4 p-4 border border-gray-200 rounded-lg space-y-3 bg-gray-50">
         @if($method)
            <div class="p-3 rounded-lg text-sm" style="background:#fffaf5; border:1px solid #f5821f30;">
            Send money to the following Nagad number:<br>
                <span class="font-bold" style="color:#f5821f;">
                    Nagad Number ({{ $method->method_details['type'] ?? 'Merchant' }}): {{ $method->account_number ?? $method->phone }}
                </span>
            </div>
        @endif

        <form action="{{ route('order.payment.submit') }}" method="POST"
            enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="hidden" name="order_id"       id="nagad-order-id">
            <input type="hidden" name="payment_method" value="nagad">

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Amount <span class="text-red-500">*</span></label>
                <input type="number" name="amount" id="nagad-amount"
                    placeholder="Paid amount" step="0.01" min="1"
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm bg-white focus:outline-none focus:border-[#f5821f]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Transaction ID <span class="text-red-500">*</span></label>
                <input type="text" name="transaction_id"
                    placeholder="e.g. NA6XXXXXXX"
                    oninput="this.value=this.value.toUpperCase()"
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-mono bg-white focus:outline-none focus:border-[#f5821f]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Reference Number <span class="text-red-500">*</span></label>
                <input type="text" name="reference_no"
                    placeholder="e.g. NA6XXXXXXX"
                    oninput="this.value=this.value.toUpperCase()"
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-mono bg-white focus:outline-none focus:border-[#f5821f]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Sender Nagad Number <span class="text-red-500">*</span></label>
                <input type="tel" name="sender_number"
                    placeholder="01XXXXXXXXX" maxlength="11"
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm bg-white focus:outline-none focus:border-[#f5821f]">
            </div>

             {{-- ── Multi Screenshot Upload ── --}}
            <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-2">
                    Payment Screenshots <span class="normal-case text-gray-400 font-normal">(max 3)</span>
                </label>

                {{-- Preview Grid --}}
                <div id="nagad-preview-grid" class="flex flex-wrap gap-2 mb-2"></div>

                {{-- Drop Zone --}}
                <div id="nagad-dropzone"
                    class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center bg-white cursor-pointer hover:border-gray-400 transition-colors"
                    onclick="document.getElementById('nagad-file-input').click()">
                    <i class="fas fa-plus-circle text-gray-300 text-2xl mb-1"></i>
                    <p class="text-[11px] text-gray-400">Click to add screenshot <span id="nagad-count-label">(0/3)</span></p>
                </div>

                {{-- Hidden real input (single, not multiple — we manage files manually) --}}
                <input type="file" id="nagad-file-input" accept="image/*" class="hidden"
                    onchange="nagadAddImage(this)">

                {{-- Hidden inputs container — one per image --}}
                <div id="nagad-hidden-inputs"></div>
            </div>

            <button type="submit"
                class="w-full py-2.5 rounded text-white font-semibold text-sm hover:opacity-90"
                style="background:#f5821f;">
                <i class="fas fa-check-circle mr-1"></i> Confirm Nagad Payment
            </button>
        </form>
    </div>
</div>
<script>
    // Store Files as array (not DataTransfer) — avoids browser bugs
    const nagadFiles = [];
    const nagad_MAX = 3;

    function nagadAddImage(input) {
        if (!input.files || !input.files[0]) return;

        const file = input.files[0];
        input.value = ''; // reset so same file can be re-added if needed

        if (nagadFiles.length >= nagad_MAX) {
            alert('Maximum ' + nagad_MAX + ' screenshots allowed.');
            return;
        }

        nagadFiles.push(file);
        nagadRenderPreviews();
        nagadSyncHiddenInputs();
    }

    function nagadRemoveImage(index) {
        nagadFiles.splice(index, 1);
        nagadRenderPreviews();
        nagadSyncHiddenInputs();
    }

    function nagadRenderPreviews() {
        const grid = document.getElementById('nagad-preview-grid');
        const countLabel = document.getElementById('nagad-count-label');
        const dropzone = document.getElementById('nagad-dropzone');

        grid.innerHTML = '';

        nagadFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative w-20 h-20 border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm group';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <button type="button"
                        onclick="nagadRemoveImage(${index})"
                        class="absolute top-0 right-0 bg-red-600 text-white w-5 h-5 flex items-center justify-center text-xs rounded-bl-lg opacity-0 group-hover:opacity-100 transition-all">
                        &times;
                    </button>
                `;
                grid.appendChild(div);
            };
            reader.readAsDataURL(file);
        });

        countLabel.innerText = `(${nagadFiles.length}/${nagad_MAX})`;

        // Hide dropzone if max reached
        dropzone.style.display = nagadFiles.length >= nagad_MAX ? 'none' : 'block';
    }

    function nagadSyncHiddenInputs() {
        // We create a hidden file input per image using DataTransfer trick per-file
        const container = document.getElementById('nagad-hidden-inputs');
        container.innerHTML = '';

        nagadFiles.forEach((file, index) => {
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
