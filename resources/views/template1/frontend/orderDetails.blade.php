@extends('template1.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.order-details-meta', ['setup' => $setup])
@endsection
@section('content')
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
        <!-- Header Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-2 gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.dashboard') }}?section=orders"
                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all">
                    <i class="fas fa-arrow-left text-[10px]"></i> Dashboard
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Order Details</h1>
            </div>
            <div class="flex flex-col items-start md:items-end">
                <span class="px-4 py-1 {{ $order->status_color }} text-white text-md font-bold rounded-lg mb-1">
                    {{ \App\Enums\Status::tryFrom($order->status)?->label() ?? 'Draft' }}
                </span>
                <p class="text-sm text-gray-500 font-medium">Order Date: {{ $order->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
        <p class="text-md text-gray-500 mb-8">Order Number: #{{ $order->order_no }}</p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left Column (Products & Tracking) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Ordered Products Card -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">Ordered Items</h3>

                    <div class="space-y-4">
                        @foreach ($order->orderDetails as $item)
                            <div
                                class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-3 md:p-4 border border-gray-200 rounded-lg bg-white hover:shadow-sm transition-all">

                                {{-- 1. Safe Image Check --}}
                                <div
                                    class="w-16 h-16 md:w-20 md:h-20 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-50 shrink-0 overflow-hidden">
                                    <img src="{{ $item->product->thumbnail_url ?? asset('./images/template1/frontend/default.webp') }}"
                                        class="w-full h-full object-cover">
                                </div>

                                <div class="flex-1 text-center sm:text-left">
                                    {{-- 2. Safe Title Check --}}
                                    <h4 class="text-md font-bold text-gray-800 mb-1 leading-tight">
                                        {{ $item->product->title ?? 'Product Not Available' }}
                                    </h4>

                                    @if ($item->variation)
                                        <div class="flex flex-wrap justify-center sm:justify-start gap-2 mb-2">
                                            @foreach ($item->variation->attributes as $attr)
                                                <span
                                                    class="px-2 py-0.5 text-[10px] font-bold bg-gray-100 rounded text-gray-600 uppercase">
                                                    {{ $attr->attributeValue->name ?? '' }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <p class="text-gray-900 font-medium">
                                        <span class="text-sm">
                                            {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }} {{ number_format($item->unit_price) }} {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}
                                            × {{ $item->quantity }}
                                        </span>
                                        <span class="text-lg font-bold text-[var(--primary-color)] ml-3">
                                            {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }} {{ number_format($item->total) }} {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}
                                        </span>
                                    </p>
                                </div>

                                {{-- 3. Safe Review Button Logic --}}
                                @php
                                    $canReview = $item->product && $order->status === \App\Enums\Status::Delivered;
                                @endphp

                                @if ($canReview)
                                    <button type="button"
                                        onclick="openReviewModal('{{ $item->product->id }}', '{{ $item->product->title }}', '{{ $item->product->thumbnail_url ?? asset('./images/template1/frontend/default.webp') }}', '{{ $item->variation->display_name ?? '' }}', '{{ $item->variation_id }}')"
                                        class="w-full sm:w-auto px-4 py-2 border border-[var(--primary-color)] text-[var(--primary-color)] rounded-lg text-xs font-bold hover:bg-orange-50 transition-all flex items-center gap-2 cursor-pointer">
                                        <i class="far fa-star"></i> Write Review
                                    </button>
                                @else
                                    <button disabled
                                        class="px-4 py-2 bg-gray-50 text-gray-300 rounded-lg text-sm flex items-center gap-2 cursor-not-allowed">
                                        <i class="fas fa-lock"></i>
                                        {{ !$item->product ? 'Item Not Found' : 'Review Locked' }}
                                    </button>
                                @endif

                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order Tracking Section -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden p-6">
                    <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-8">Order Tracking</h3>

                    <!-- Vertical Timeline -->
                    {{-- The 'before' class creates the vertical line connecting the dots --}}
                    <div
                        class="relative pl-8 space-y-8 before:content-[''] before:absolute before:left-[7px] before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-100">

                        @php
                            $currentStatus = $order->status; // এটি একটি Enum Object
                            $flow = \App\Enums\Status::ORDER_FLOW; // এটি Enum Objects এর এ্যারে

                            $currentIndex = -1;
                            foreach ($flow as $index => $status) {
                                if ($status === $currentStatus) {
                                    $currentIndex = $index;
                                    break;
                                }
                            }
                        @endphp

                        {{-- // Special case: If order is cancelled, we only show steps up to the cancellation point or just the flow
                            $isCancelled = $currentStatusValue === \App\Enums\Status::Cancelled->value;
                        @endphp --}}

                        @foreach ($flow as $index => $status)
                            {{-- Skip Return/Cancel steps if they haven't happened yet to keep the UI clean --}}
                            {{-- @if (($status == \App\Enums\Status::Cancelled || $status == \App\Enums\Status::ReturntoCourier || $status == \App\Enums\Status::ReturnReceived) && $index > $currentIndex)
                                @continue
                            @endif --}}

                            @php
                                $isCompleted = $currentIndex !== -1 && $index <= $currentIndex;
                                $isCurrent = $index === $currentIndex;
                            @endphp

                            <div class="relative">
                                <!-- Dot -->
                                <div
                                    class="absolute -left-8 top-2 w-4 h-4 rounded-full border-2 border-white z-10
                    {{ $isCurrent ? 'bg-orange-500 shadow-[0_0_0_3px_rgba(249,115,22,0.2)]' : ($isCompleted ? 'bg-green-500' : 'bg-gray-200') }}">
                                    @if ($isCompleted && !$isCurrent)
                                        <i
                                            class="fas fa-check text-[8px] text-primary flex items-center justify-center h-full"></i>
                                    @endif
                                </div>

                                <!-- Label -->
                                <p
                                    class="font-bold text-md leading-none mb-1
                    {{ $isCurrent ? 'text-orange-600' : ($isCompleted ? 'text-green-700' : 'text-gray-400') }}">
                                    {{ $status->label() }}
                                </p>

                                <!-- Date/Time -->
                                <p class="text-sm text-gray-500 font-medium">
                                    @if ($index === 0)
                                        {{-- Always show creation date for the first step --}}
                                        {{ $order->created_at->format('M d, Y h:i A') }}
                                    @elseif($isCurrent)
                                        {{-- Show update date for the current active step --}}
                                        {{ $order->updated_at->format('M d, Y h:i A') }}
                                    @else
                                        <span class="opacity-0">--</span> {{-- Keep spacing even if no date --}}
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <!-- Courier Box (Dynamic) -->
                    @if ($order->courier_info && isset($order->courier_info['consignment_id']))
                        @php
                            $courierName = strtolower($order->courier_info['courier_name'] ?? '');
                            $consignmentId = $order->courier_info['consignment_id'];
                            $customerPhone = $order->customer->phone ?? '';

                            $trackingUrl = '#';

                            // pathao
                            if (str_contains($courierName, 'pathao')) {
                                $trackingUrl = "https://merchant.pathao.com/tracking?consignment_id={$consignmentId}&phone={$customerPhone}";
                            }
                            // carrybee
                            elseif (str_contains($courierName, 'carrybee')) {
                                $trackingUrl = "https://merchant.carrybee.com/order-track/{$consignmentId}";
                            }
                            // steadfast
                            elseif (str_contains($courierName, 'steadfast')) {
                                $trackingUrl = "https://steadfast.com.bd/t/{$consignmentId}";
                            }
                            // redx
                            elseif (str_contains($courierName, 'redx')) {
                                $trackingUrl = "https://redx.com.bd/track/{$consignmentId}";
                            } elseif (isset($order->courier_info['url'])) {
                                $trackingUrl = $order->courier_info['url'];
                            }
                        @endphp

                        <div
                            class="mt-6 md:mt-10 p-4 md:p-5 bg-blue-50 border border-blue-100 rounded-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-3 md:gap-4">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-blue-600 shadow-sm border border-blue-50">
                                    <i class="fas fa-truck-moving text-xl"></i>
                                </div>
                                <div>
                                    <h5 class="text-blue-900 font-bold text-sm mb-0.5">Courier Tracking</h5>
                                    <p class="text-sm text-blue-700 font-semibold uppercase">
                                        {{ $order->courier_info['courier_name'] ?? 'Courier' }}
                                    </p>
                                    <p class="text-xs text-blue-500 font-medium">
                                        Tracking ID: <span class="font-mono">{{ $consignmentId }}</span>
                                    </p>
                                </div>
                            </div>

                            @if ($trackingUrl !== '#')
                                <a href="{{ $trackingUrl }}" target="_blank"
                                    class="w-full md:w-auto px-6 py-3 bg-blue-600 text-primary rounded-xl text-sm font-bold hover:bg-blue-700 transition-all flex items-center justify-center gap-2 shadow-sm">
                                    <i class="fas fa-external-link-alt text-xs"></i>
                                    Track Now
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column (Info Cards) -->
            <div class="space-y-6">

                <!-- Customer Information -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                    <h3 class="text-lg md:text-2xl font-bold text-gray-900 mb-6">Customer Information</h3>
                    <div class="space-y-4">
                        <div class="flex items-start ">
                            <div class="w-10 h-10 flex items-center justify-center text-gray-400 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-map-pin h-4 w-4 text-gray-500">
                                    <path
                                        d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0">
                                    </path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-900 text-md">{{ $order->customer->name ?? '' }}</p>
                                <p class="text-sm text-gray-500 leading-relaxed font-medium">
                                    {{ $order->customer->address ?? '' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center ">
                            <div class="w-10 h-10 flex items-center justify-center text-gray-400 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-phone h-4 w-4 text-gray-500">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                    </path>
                                </svg>
                            </div>
                            <p class="text-gray-900 text-md">{{ $order->customer->phone ?? '' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                    <h3 class="text-lg md:text-2xl font-bold text-gray-800 mb-6 border-b border-gray-50 pb-3">Payment
                        Summary</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between text-md text-gray-600 font-medium">
                            <span>Subtotal:</span>
                            <span class="text-gray-900 font-bold">{{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }} {{ number_format($order->subtotal) }} {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}</span>
                        </div>
                        <div class="flex justify-between text-md text-gray-600 font-medium">
                            <span>Shipping Charge:</span>
                            <span class="text-gray-900 font-bold">{{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }} {{ number_format($order->other_charges) }} {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}</span>
                        </div>
                        @if ($order->coupon_discount > 0)
                            <div class="flex justify-between text-md text-green-600 font-medium">
                                <span>Discount:</span>
                                <span class="font-bold"> - {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }} {{ number_format($order->coupon_discount) }} {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}</span>
                            </div>
                        @endif
                        <div class="pt-4 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-gray-800 font-black">Total:</span>
                            <span class="text-xl font-black text-[var(--primary-color)]">{{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }} {{ number_format($order->grand_total) }} {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}</span>
                        </div>
                        <p class="text-[11px] text-gray-400 font-bold uppercase mt-2">Method:
                            {{ str_replace('_', ' ', $order->payment_method ?? 'COD') }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                    <h3 class="text-lg md:text-2xl font-bold text-gray-900 mb-6">Action</h3>
                    <div class="space-y-3">
                        <a href="{{ route('invoice.download', $order->id) }}"
                            class="w-full py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-800 hover:border-[var(--primary-color)] hover:text-[var(--primary-color)] transition-all flex items-center justify-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-download h-4 w-4 mr-2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" x2="12" y1="15" y2="3"></line>
                            </svg>
                            Invoice Download
                        </a>
                        <a href="{{ route('faq.index') }}"
                            class="w-full py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-800 hover:border-[var(--primary-color)] hover:text-[var(--primary-color)] transition-all flex items-center justify-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-message-square h-4 w-4 mr-2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            Support
                        </a>
                        @if ($order->status == \App\Enums\Status::ReturnRequest->value)
                            <button disabled
                                class="w-full py-2.5 bg-gray-100 text-gray-400 rounded-md text-sm font-bold cursor-not-allowed">
                                Return Requested
                            </button>
                        @else
                            <button onclick="openReturnModal()"
                                class="w-full py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-800 hover:border-red-500 hover:text-red-500 transition-all flex items-center justify-center gap-3 cursor-pointer">
                                <i class="fas fa-undo h-4 w-4"></i> Return Request
                            </button>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </section>
    <div id="return-modal"
        class="fixed inset-0 z-[120] hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 relative shadow-2xl">
            <button onclick="closeReturnModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-xl cursor-pointer">&times;</button>
            <h2 class="text-xl font-bold text-gray-800 mb-6">Request a Return</h2>

            <form action="{{ route('order.return', $order->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="text-sm font-bold text-gray-700 mb-2 block">Reason for Return:</label>
                    <textarea name="reason" rows="4" required
                        class="w-full border rounded-xl p-3 text-sm outline-none focus:border-red-500 bg-gray-50"
                        placeholder="Describe the issue with the product..."></textarea>
                </div>

                <div class="mb-6">
                    <label class="text-sm font-bold text-gray-700 mb-2 block">Upload Proof (Images):</label>
                    <div class="flex flex-wrap gap-2" id="return-image-preview">
                        <label
                            class="w-16 h-16 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center cursor-pointer hover:border-red-500">
                            <input type="file" name="images[]" multiple accept="image/*" class="hidden"
                                onchange="handleReturnPreview(this)">
                            <i class="fas fa-camera text-gray-400"></i>
                        </label>
                    </div>
                </div>

                <button type="submit"
                    class="w-full primary-bg text-primary py-3 rounded-xl font-bold hover:bg-red-600 transition-all">Submit
                    Request</button>
            </form>
        </div>
    </div>
    <!-- Review Modal -->
    <div id="review-modal"
        class="fixed inset-0 z-[110] hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 relative shadow-2xl">
            <button onclick="closeReviewModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-xl cursor-pointer border-none bg-transparent">&times;</button>

            <h2 class="text-xl font-bold text-gray-800 mb-6">Give Product Review</h2>

            <!-- Added enctype for file upload -->
            <form action="{{ route('user.review.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="product_id" id="modal-product-id">
                <input type="hidden" name="variation_id" id="modal-variation-id">
                <input type="hidden" name="rating" id="modal-rating-value" value="5">

                <!-- Product Info -->
                <div class="flex gap-4 mb-6">
                    <img id="modal-product-img" src="" class="w-16 h-16 rounded-lg border object-cover">
                    <div>
                        <h4 id="modal-product-name" class="font-bold text-gray-800 text-sm leading-tight"></h4>
                        <p id="modal-product-variant" class="text-xs text-gray-400 mt-1"></p>
                    </div>
                </div>

                <!-- Stars Section -->
                <div class="mb-4">
                    <p class="text-sm font-bold text-gray-700 mb-2">Rating:</p>
                    <div class="flex gap-2 text-2xl text-yellow-400" id="star-container">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star cursor-pointer star-btn" data-index="{{ $i }}"></i>
                        @endfor
                    </div>
                </div>

                <!-- Comment -->
                <div class="mb-4">
                    <p class="text-sm font-bold text-gray-700 mb-2">Your Review:</p>
                    <textarea name="comment" rows="3" required
                        class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:border-[#016738] bg-gray-50"
                        placeholder="Write your feedback..."></textarea>
                </div>

                <!-- Image Upload Section -->
                <div class="mb-6">
                    <p class="text-sm font-bold text-gray-700 mb-2">Upload Photos:</p>
                    <div class="flex flex-wrap gap-2" id="review-image-preview">
                        <label
                            class="w-16 h-16 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center cursor-pointer hover:border-orange-500">
                            <input type="file" name="images[]" multiple accept="image/*" class="hidden"
                                onchange="handleReviewImagePreview(this)">
                            <i class="fas fa-camera text-gray-400"></i>
                        </label>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <button type="submit"
                        class="flex-1 bg-[#1D2128] text-primary py-3 rounded-xl font-bold hover:bg-black transition-all cursor-pointer">Submit
                        Review</button>
                    <button type="button" onclick="closeReviewModal()"
                        class="px-6 py-3 border border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 cursor-pointer">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        let returnFiles = new DataTransfer();

        function openReturnModal() {
            document.getElementById('return-modal').classList.remove('hidden');
            document.getElementById('return-modal').classList.add('flex');
        }

        function closeReturnModal() {
            document.getElementById('return-modal').classList.add('hidden');
            document.getElementById('return-modal').classList.remove('flex');
        }

        function handleReturnPreview(input) {
            const container = document.getElementById('return-image-preview');
            const label = container.querySelector('label');

            if (input.files) {
                Array.from(input.files).forEach(file => {
                    returnFiles.items.add(file);
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const div = document.createElement('div');
                        div.className = 'w-16 h-16 rounded-xl border overflow-hidden shrink-0 relative';
                        div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                        container.insertBefore(div, label);
                    };
                    reader.readAsDataURL(file);
                });
                input.files = returnFiles.files;
            }
        }
        // multiple file upload
        let reviewFilesContainer = new DataTransfer();

        function handleReviewImagePreview(input) {
            const container = document.getElementById('review-image-preview');
            const addButton = container.querySelector('label');

            if (input.files) {
                const newFiles = Array.from(input.files);
                const currentCount = reviewFilesContainer.files.length;

                // Check if total files will exceed 5
                if (currentCount + newFiles.length > 5) {
                    toastr.error("You can only upload a maximum of 5 images.");
                    input.value = ""; // Clear the selection
                    return;
                }

                newFiles.forEach(file => {
                    reviewFilesContainer.items.add(file);

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className =
                            'preview-item w-16 h-16 rounded-xl border border-gray-200 overflow-hidden shrink-0 relative group';
                        div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <button type="button" onclick="removeReviewImage(this, '${file.name}')"
                        class="absolute top-0 right-0 bg-red-500 text-primary p-1 cursor-pointer">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                `;
                        container.insertBefore(div, addButton);
                    }
                    reader.readAsDataURL(file);
                });

                // Sync the input with our custom container
                input.files = reviewFilesContainer.files;
            }
        }

        // image remove function for review
        function removeReviewImage(element, fileName) {
            const input = document.querySelector('input[name="images[]"]');

            // reviewFilesContainer from DOM remove
            const newDataTransfer = new DataTransfer();
            Array.from(reviewFilesContainer.files).forEach(file => {
                if (file.name !== fileName) {
                    newDataTransfer.items.add(file);
                }
            });
            reviewFilesContainer = newDataTransfer;
            input.files = reviewFilesContainer.files;

            // preview from DOM remove
            element.parentElement.remove();
        }

        // modal close for review
        function closeReviewModal() {
            const modal = document.getElementById('review-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');

                modal.querySelector('form').reset();

                reviewFilesContainer = new DataTransfer();

                const previews = modal.querySelectorAll('.preview-item');
                previews.forEach(el => el.remove());
            }
        }

        // openReviewModal, Star Rating
        function openReviewModal(id, name, img, variant, variationId) {
            document.getElementById('modal-product-id').value = id;
            document.getElementById('modal-product-name').innerText = name;
            document.getElementById('modal-product-img').src = img;
            document.getElementById('modal-product-variant').innerText = variant ? '(' + variant + ')' : '';
            document.getElementById('modal-variation-id').value = variationId || '';

            const modal = document.getElementById('review-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        const stars = document.querySelectorAll('.star-btn');
        const ratingInput = document.getElementById('modal-rating-value');
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const index = this.getAttribute('data-index');
                ratingInput.value = index;
                stars.forEach(s => {
                    if (s.getAttribute('data-index') <= index) {
                        s.classList.remove('text-gray-200');
                        s.classList.add('text-yellow-400');
                    } else {
                        s.classList.remove('text-yellow-400');
                        s.classList.add('text-gray-200');
                    }
                });
            });
        });
    </script>
@endpush
@push('scripts')
    <x-meta-info.ecommerce-meta.order-details-meta />
@endpush
