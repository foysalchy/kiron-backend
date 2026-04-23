{{-- resources/views/components/template1/payment-forms/card.blade.php --}}
<div class="payment-form hidden" id="form-card">
    <div class="mt-4 p-5 border border-blue-100 rounded-xl space-y-4 bg-blue-50/30">

        {{-- Instructions Box --}}
        <div class="flex items-start gap-3 p-3 rounded-lg bg-white border border-blue-100 shadow-sm">
            <i class="fas fa-credit-card text-blue-500 mt-0.5"></i>
            <div>
                <p class="text-xs font-bold text-blue-900 uppercase">Card Payment Instructions</p>
                <p class="text-[11px] text-blue-700 leading-tight">Please pay through your card and provide the Approval Code or Transaction ID from the receipt below.</p>
            </div>
        </div>

        <form action="{{ route('order.payment.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            {{-- Hidden Inputs: JS will fill these --}}
            <input type="hidden" name="order_id">
            <input type="hidden" name="payment_method" value="card">
            <input type="hidden" name="amount">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Card Type Selection --}}
                <div class="md:col-span-1">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Card Type <span class="text-red-500">*</span></label>
                    <select name="card_type" required
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20">
                        <option value="">-- Select Card --</option>
                        <option value="Visa">Visa Card</option>
                        <option value="Mastercard">Mastercard</option>
                        <option value="Amex">American Express</option>
                        <option value="Nexus">DBBL Nexus</option>
                    </select>
                </div>

                {{-- Transaction ID --}}
                <div class="md:col-span-1">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Transaction ID / Approval Code <span class="text-red-500">*</span></label>
                    <input type="text" name="transaction_id" placeholder="e.g. 524103" required
                        oninput="this.value=this.value.toUpperCase()"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm font-mono bg-white focus:outline-none focus:border-blue-500">
                </div>
            </div>

            {{-- Last 4 Digits (Stored in sender_number field) --}}
            <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Last 4 Digits of your Card</label>
                <input type="text" name="sender_number" maxlength="4" placeholder="e.g. 1234" pattern="\d{4}"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm font-mono bg-white focus:outline-none focus:border-blue-500">
                <p class="text-[10px] text-gray-400 mt-1">Enter the last 4 digits of your card number.</p>
            </div>

            {{-- Screenshot Upload --}}
            <div>
                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Payment Receipt / Screenshot</label>
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center bg-white hover:border-blue-400 transition-colors cursor-pointer group"
                    onclick="document.getElementById('card-ss').click()">
                    <img id="card-preview" class="hidden mx-auto mb-2 max-h-32 rounded-lg object-contain shadow-sm">
                    <div id="card-placeholder">
                        <i class="fas fa-camera text-gray-300 text-2xl mb-2 group-hover:text-blue-400"></i>
                        <p class="text-[11px] text-gray-400">Click to upload transaction receipt</p>
                    </div>
                    <input type="file" id="card-ss" name="screenshot" accept="image/*" class="hidden"
                        onchange="previewScreenshot(this,'card-preview','card-placeholder')">
                </div>
            </div>

            {{-- Submit Button --}}
            <button type="submit"
                class="w-full py-3 rounded-xl bg-blue-600 text-white font-bold text-sm shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all flex items-center justify-center gap-2">
                <i class="fas fa-check-circle"></i> Confirm Card Payment
            </button>
        </form>
    </div>
</div>
