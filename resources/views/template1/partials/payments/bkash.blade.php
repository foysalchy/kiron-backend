{{-- resources/views/template1/partials/payment/bkash.blade.php --}}
<div class="payment-form hidden" id="form-bkash">
    <div class="mt-4 p-4 border border-gray-200 rounded-lg space-y-3">
        <p class="text-sm text-gray-600">
            নিচের bKash নম্বরে টাকা পাঠিয়ে নিচের ফর্মটি পূরণ করুন।<br>
            <span class="font-bold text-gray-800">bKash Number: {{ config('payment.bkash_merchant', '01XXXXXXXXX') }}</span>
        </p>

        <form action="{{ route('order.payment.submit') }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="order_id" id="bkash-order-id">
            <input type="hidden" name="payment_method" value="bkash">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Transaction ID</label>
                <input type="text" name="transaction_id" placeholder="bKash Transaction ID"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-[#e2136e]">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sender Number</label>
                <input type="tel" name="sender_number" placeholder="01XXXXXXXXX"
                    required maxlength="11"
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-[#e2136e]">
            </div>

            <button type="submit"
                class="w-full py-2.5 rounded text-white font-semibold text-sm"
                style="background:#e2136e;">
                Confirm Payment
            </button>
        </form>
    </div>
</div>
