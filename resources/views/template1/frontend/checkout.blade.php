@extends('template1.layouts.front')

@section('content')
    <section class="container mx-auto py-6 px-4">
        <h1 class="text-2xl font-black text-gray-900 mb-8 tracking-tight">চেকআউট</h1>

        <form action="{{ route('order.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- LEFT COLUMN: Customer & Payment Info -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- 1. Customer Information Card -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-2xl font-semibold text-gray-800 leading-tight">
                                অর্ডারটি কনফার্ম করতে আপনার নাম, ঠিকানা, মোবাইল নম্বর, নিয়ে অর্ডার কনফার্ম বাটনে ক্লিক করুন
                            </h2>
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">আপনার নাম <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" placeholder="আপনার পূর্ণ নাম লিখুন" required
                                        value="{{ old('name', auth('customer')->user()->name ?? '') }}"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#FF6A00] focus:ring-4 focus:ring-orange-50 transition-all">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">ফোন নম্বর <span class="text-red-500">*</span></label>
                                    <input type="tel" name="phone" placeholder="আপনার মোবাইল নম্বর" required
                                        value="{{ old('phone', auth('customer')->user()->phone ?? '') }}"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#FF6A00] focus:ring-4 focus:ring-orange-50 transition-all">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">আপনার ঠিকানা <span class="text-red-500">*</span></label>
                                <textarea name="address" placeholder="আপনার ঠিকানা" rows="3" required
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 outline-none focus:border-[#FF6A00] focus:ring-4 focus:ring-orange-50 transition-all">{{ old('address', auth('customer')->user()->address ?? '') }}</textarea>
                            </div>

                            <!-- Checkboxes -->
                            <div class="space-y-3 pt-2">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" checked class="w-4 h-4 accent-black rounded-full">
                                    <span class="text-sm font-medium text-gray-600 group-hover:text-gray-900">Billing
                                        address</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="create_account" class="w-4 h-4 accent-[#FF6A00]">
                                    <span class="text-sm font-medium text-gray-600 group-hover:text-gray-900">Create an
                                        account?</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Payment Method Card -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-xl font-bold text-gray-800">পেমেন্ট মেথড নির্বাচন করুন</h2>
                        </div>
                        <div class="space-y-3 p-6">
                            <!-- Cash on Delivery -->
                            <label
                                class="flex items-center space-x-4 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-all group">
                                <input type="radio" name="payment_method" value="cod" checked
                                    class="w-4 h-4 text-black border-gray-300 focus:ring-0 accent-black">
                                <div
                                    class="w-7 h-7 bg-gray-100 rounded flex items-center justify-center border border-gray-50">
                                    <i class="fas fa-truck text-xs text-gray-400"></i>
                                </div>
                                <span class="text-[15px] font-medium text-gray-900">ক্যাশ অন ডেলিভারি</span>
                            </label>

                            <!-- bKash -->
                            <label
                                class="flex items-center space-x-4 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-all group">
                                <input type="radio" name="payment_method" value="bkash"
                                    class="w-4 h-4 text-black border-gray-300 focus:ring-0 accent-black">
                                <div
                                    class="w-7 h-7 bg-gray-100 rounded flex items-center justify-center border border-gray-50">
                                    <i class="fas fa-mobile-alt text-xs text-gray-400"></i>
                                </div>
                                <span class="text-[15px] font-medium text-gray-900">bKash</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Delivery & Summary -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6 sticky top-24">

                        <!-- 1. Delivery Selection (Synced with Logic) -->
                        <div class="mb-8">
                            <h2 class="text-2xl font-semibold leading-none tracking-tight mb-6">ডেলিভারি মেথড নির্বাচন করুন
                            </h2>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="delivery_area" value="inside"
                                        onchange="updateCheckoutShipping(this.value)"
                                        {{ $shipping_area == 'inside' ? 'checked' : '' }} class="w-4 h-4 accent-black">
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-black">ঢাকার ভেতরে
                                        (৳৬০)</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="delivery_area" value="outside"
                                        onchange="updateCheckoutShipping(this.value)"
                                        {{ $shipping_area == 'outside' ? 'checked' : '' }} class="w-4 h-4 accent-black">
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-black">ঢাকার বাইরে
                                        (৳১২০)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Product Row in Summary -->
                        <div class="space-y-4 mb-8">
                            @foreach ($cartContent as $item)
                                <div class="bg-[#F9FAFB] rounded-xl p-4 flex items-center gap-4">
                                    <!-- Actual Product Image -->
                                    <div
                                        class="w-16 h-16 bg-white rounded-lg overflow-hidden border border-gray-100 shrink-0">
                                        <img src="{{ $item->options->thumbnail }}" class="w-full h-full object-cover">
                                    </div>

                                    <div class="flex-1">
                                        <h4 class="text-[13px] font-bold text-gray-800 leading-tight mb-1">
                                            {{ $item->name }}</h4>
                                        <p class="text-[#FF6A00] font-semibold text-sm">৳{{ number_format($item->price) }}
                                        </p>
                                    </div>

                                    <!-- Quantity Display -->
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-gray-500">Qty: {{ $item->qty }}</span>
                                        <a href="{{ route('cart.remove', $item->rowId) }}"
                                            class="text-red-400 hover:text-red-600">
                                            <i class="far fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- 3. Cost Breakdown -->
                        <div class="space-y-4 border-t border-gray-100 pt-6">
                            <div class="flex justify-between items-center text-gray-700">
                                <span class="text-md font-medium">সাবটোটাল:</span>
                                <span class="text-md font-bold text-gray-900">৳{{ number_format($subtotal) }}</span>
                            </div>

                            @if ($discount > 0)
                                <div class="flex justify-between items-center text-green-600">
                                    <span class="text-md font-medium">ডিসকাউন্ট:</span>
                                    <span class="text-md font-bold">- ৳{{ number_format($discount) }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between items-center text-gray-700">
                                <span class="text-md font-medium">ডেলিভারি চার্জ:</span>
                                <span class="text-md font-bold text-gray-900">৳{{ number_format($shipping) }}</span>
                            </div>
                            <div class="flex justify-between items-center border-t border-gray-100 pt-4">
                                <span class="text-lg font-black text-gray-900">পরিশোধ করতে হবে:</span>
                                <span class="text-xl font-bold text-[#FF6A00]">৳{{ number_format($total) }}</span>
                            </div>
                        </div>

                        <!-- 4. Confirm Button -->
                        <button type="submit"
                            class="w-full bg-[#EF4444] hover:bg-red-600 text-white font-bold py-4 text-sm rounded-xl mt-8 shadow-lg shadow-red-100 transition-all active:scale-[0.98]">
                            অর্ডার কনফার্ম করুন
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- hidden form for shipping update --}}
        <form id="shipping-update-form" action="{{ route('cart.shipping') }}" method="POST" style="display:none;">
            @csrf
            <input type="hidden" name="area" id="shipping-area-input">
        </form>
    </section>
@endsection

@push('scripts')
@push('scripts')
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const phoneInput = document.querySelector('input[name="phone"]');
        const nameInput = document.querySelector('input[name="name"]');
        const addressInput = document.querySelector('textarea[name="address"]');

        function saveDraft() {
            let phone = phoneInput.value;
            let name = nameInput.value;
            let address = addressInput.value;

            if (phone.length >= 11) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch("{{ route('order.partial') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        phone: phone,
                        name: name,
                        address: address
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log("Success: Draft order created.");
                    } else {
                        console.log("Error: " + (data.error || "Draft failed"));
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        phoneInput.addEventListener('blur', saveDraft);

        @auth('customer')
            saveDraft();
        @endauth
    });
</script>
@endpush
@endpush
    <script>
        function updateCheckoutShipping(value) {
            document.getElementById('shipping-area-input').value = value;
            document.getElementById('shipping-update-form').submit();
        }
    </script>
@endpush
