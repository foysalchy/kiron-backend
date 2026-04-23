{{-- resources/views/components/template1/payment-forms/cod.blade.php --}}
<div class="payment-form hidden" id="form-cod">
    <div class="mt-4 p-6 border border-green-100 rounded-xl space-y-4 bg-green-50/30 text-center">

        {{-- Icon --}}
        <div
            class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm border border-green-100">
            <i class="fas fa-truck text-2xl text-green-500"></i>
        </div>

        {{-- Message --}}
        <div>
            <h4 class="text-sm font-bold text-green-900">Cash on Delivery</h4>
            <p class="text-xs text-green-700 px-4 leading-relaxed">
                You have chosen to pay with Cash on Delivery. Please confirm your order and prepare the payment when the
                delivery arrives. Thank you for shopping with us!
            </p>
        </div>

        <form action="{{ route('order.payment.submit') }}" method="POST">
            @csrf
            <<input type="hidden" name="order_id">
                <input type="hidden" name="payment_method" value="cod">
                <input type="hidden" name="amount">

                <<input type="hidden" name="transaction_id" value="COD-PLACEHOLDER">

                    <button type="submit"
                        class="w-full py-3 rounded-xl bg-green-600 text-white font-bold text-sm shadow-lg shadow-green-200 hover:bg-green-700 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-check-circle"></i> Confirm Order
                    </button>
        </form>
    </div>
</div>
