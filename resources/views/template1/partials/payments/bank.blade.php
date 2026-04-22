<div class="payment-form hidden animate-in fade-in duration-300" id="form-bank">
    <div class="mt-4 space-y-4">

        {{-- ১. ইন্সট্রাকশন বক্স (Grey Box) --}}
        <div class="p-4 bg-[#E5E7EB] rounded-md text-sm text-gray-800 space-y-1">
            <p class="font-bold mb-2">নিচে দেয়া ব্যাংকে টাকা পাঠিয়ে নিচের ফিল্ডগুলো পূরণ করুন</p>
            <p>Bank Name: <span class="font-bold">{{ $methodInfo->method_details['bank_name'] ?? 'DBBL' }}</span></p>
            <p>Branch: <span class="font-bold">{{ $methodInfo->method_details['branch'] ?? 'Dhaka' }}</span></p>
            <p>Holder: <span class="font-bold">{{ $methodInfo->account_holder ?? 'SIX ZERO WORLD' }}</span></p>
            <p>Account: <span class="font-bold">{{ $methodInfo->account_number ?? '55555555' }}</span></p>
        </div>

        <form action="{{ route('order.payment.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="order_id" class="order-id-input">
            <input type="hidden" name="payment_method" value="bank">
            <input type="hidden" name="amount" class="payment-amount-input">

            {{-- Bank Name --}}
            <div>
                <label class="inline-block px-3 py-1 bg-white border border-b-0 border-gray-200 rounded-t-md text-xs font-medium text-gray-600">Bank Name</label>
                <input type="text" name="note_details[bank_name]" placeholder="Enter bank name" required
                    class="w-full p-2.5 border border-gray-200 rounded-b-md rounded-r-md text-sm focus:outline-none focus:ring-1 focus:ring-purple-500">
            </div>

            {{-- Account Number --}}
            <div>
                <label class="inline-block px-3 py-1 bg-white border border-b-0 border-gray-200 rounded-t-md text-xs font-medium text-gray-600">Account Number</label>
                <input type="text" name="reference_no" placeholder="Enter account number" required
                    class="w-full p-2.5 border border-gray-200 rounded-b-md rounded-r-md text-sm focus:outline-none focus:ring-1 focus:ring-purple-500">
            </div>

            {{-- Holder Name --}}
            <div>
                <label class="inline-block px-3 py-1 bg-white border border-b-0 border-gray-200 rounded-t-md text-xs font-medium text-gray-600">Holder Name</label>
                <input type="text" name="note_details[holder_name]" placeholder="Enter holder name" required
                    class="w-full p-2.5 border border-gray-200 rounded-b-md rounded-r-md text-sm focus:outline-none focus:ring-1 focus:ring-purple-500">
            </div>

            {{-- Branch Name --}}
            <div>
                <label class="inline-block px-3 py-1 bg-white border border-b-0 border-gray-200 rounded-t-md text-xs font-medium text-gray-600">Branch Name</label>
                <input type="text" name="note_details[branch_name]" placeholder="Enter branch name" required
                    class="w-full p-2.5 border border-gray-200 rounded-b-md rounded-r-md text-sm focus:outline-none focus:ring-1 focus:ring-purple-500">
            </div>

            {{-- Routing Number --}}
            <div>
                <label class="inline-block px-3 py-1 bg-white border border-b-0 border-gray-200 rounded-t-md text-xs font-medium text-gray-600">Routing Number</label>
                <input type="text" name="note_details[routing_number]" placeholder="Enter routing number"
                    class="w-full p-2.5 border border-gray-200 rounded-b-md rounded-r-md text-sm focus:outline-none focus:ring-1 focus:ring-purple-500">
            </div>

            {{-- Screenshot --}}
            <div>
                <label class="inline-block px-3 py-1 bg-white border border-b-0 border-gray-200 rounded-t-md text-xs font-medium text-gray-600">Upload Screenshot</label>
                <input type="file" name="screenshot" accept="image/*"
                    class="w-full p-2 border border-gray-200 rounded-b-md rounded-r-md text-xs bg-gray-50 file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:bg-purple-100 file:text-purple-700">
            </div>

            <button type="submit" class="w-full py-3 bg-[#A855F7] hover:bg-purple-700 text-white font-bold rounded-md shadow-md transition-all">
                Order
            </button>
        </form>
    </div>
</div>
