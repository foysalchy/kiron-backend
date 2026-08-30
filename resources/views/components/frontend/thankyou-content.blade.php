<div class="px-3 md:px-0 container mx-auto pt-8 md:pt-10 pb-10">
    <!-- Thank You Section -->
    <div class="text-center bg-white p-5 md:p-8 rounded-xl shadow-sm border border-gray-100 mb-8 md:mb-12">
        <div class="w-12 h-12 md:w-16 md:h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 md:w-8 md:h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Thank you for your order!</h1>
        <p class="text-sm md:text-base text-gray-600 mb-6">Your order #{{ $order->order_no }} has been placed successfully. We'll send you a confirmation email shortly.</p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 md:gap-4">
            <a href="{{ route('shop.index') }}" class="w-full sm:w-auto px-6 py-3 bg-[var(--primary-color)] text-white font-medium rounded-lg hover:bg-opacity-90 transition-all text-sm md:text-base">
                Continue to Shopping
            </a>
            <a href="{{ route('invoice.download', $order->id) }}" class="w-full sm:w-auto px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-all flex items-center justify-center gap-2 text-sm md:text-base">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download Invoice
            </a>
        </div>
    </div>

    <!-- Related Products Section -->
    @if($relatedProducts->count() > 0)
    <div class="mt-8 md:mt-12 px-2 md:px-0">
        <div class="flex items-center justify-between mb-4 md:mb-6">
            <h2 class="text-lg md:text-2xl font-bold text-gray-900">You Might Also Like</h2>
            <a href="{{ route('shop.index') }}" class="text-[var(--primary-color)] hover:underline text-xs md:text-sm font-medium">View All</a>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 md:gap-6">
            @foreach($relatedProducts as $product)
                <!-- Dynamic component rendering based on the active template -->
               
                    <x-template1.product-card :product="$product" />
            @endforeach
        </div>
    </div>
    @endif
</div>
