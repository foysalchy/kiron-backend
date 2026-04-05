<header class="w-full bg-white sticky top-0 z-50 font-['Outfit']">

    <!-- 1. Top Bar (Orange Row) -->
    <div class="bg-[#FF6A00] text-white py-2 text-[13px] hidden lg:block">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center gap-6">
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone h-4 w-4"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    {{ $setup->phone }}
                </span>
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail h-4 w-4"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>
                     {{ $setup->email }}
                </span>
            </div>
            <div class="flex items-center gap-6">
                <span>Free Shipping on Orders Over $50</span>
                <a href="{{ url('/support') }}" class="hover:underline">Help</a>
            </div>
        </div>
    </div>

    <!-- 2. Main Header (Logo, Search, Nav) -->
    <div class="container mx-auto px-4 py-4 flex items-center justify-between gap-4 lg:gap-8">

        <!-- Logo -->
        <a href="{{ url('/') }}" class="flex items-center gap-3 flex-shrink-0">
           @if($setup && $setup->logo)
                <img src="{{ $setup->logo_url }}" alt="{{ $setup->shop_name }}" class="h-12 w-auto object-contain">
            @else
                <div class="bg-[#FF6A00] w-10 h-12 flex items-center justify-center rounded-lg shadow-sm">
                    <span class="text-white text-2xl font-bold">
                        {{ substr($setup->shop_name ?? 'O', 0, 1) }}
                    </span>
                </div>
            @endif
            <span class="text-2xl font-extrabold text-[#1D2128] tracking-tight">{{ $setup->shop_name ?? 'OrenMart' }}</span>
        </a>

        <!-- Search Bar -->
        <div class="hidden md:flex flex-1 max-w-2xl relative">
            <div class="flex w-full items-center bg-white border border-gray-200 rounded-md p-1 shadow-xs">

                <input type="text" placeholder="Search for products..."
                    class="flex-1 bg-transparent px-4 py-2.5 text-sm text-gray-600 outline-none placeholder:text-gray-400">

                <button
                    class="bg-[#FF6A00] text-white h-10 w-12 flex items-center justify-center rounded-md hover:bg-orange-600 transition-all shrink-0">
                    <i class="fas fa-search text-lg px-4"></i>
                </button>
            </div>
        </div>

        <!-- Right Side Icons & Links -->
        <div class="flex items-center gap-4 lg:gap-7 text-[#1D2128]">

            <!-- Bangla Links (As seen in image) -->
            <div class="hidden xl:flex items-center gap-6 text-sm font-semibold text-gray-500">
                <a href="{{ url('/product-track') }}" class="hover:text-[#FF6A00] transition-colors">অর্ডার ট্র্যাক
                    করুন</a>
                <a href="{{ url('/contact') }}" class="hover:text-[#FF6A00] transition-colors">যোগাযোগ</a>
                <a href="{{ url('/blogs') }}" class="hover:text-[#FF6A00] transition-colors">ব্লগ</a>
            </div>

            <!-- Wishlist -->
            <a href="{{ url('/wishlist') }}" class="flex items-center gap-2 hover:text-[#FF6A00] transition-colors">
                <i class="fa-regular fa-heart text-xl"></i>
                <span class="hidden lg:block font-semibold text-sm">Wishlist</span>
            </a>

            <!-- Account -->
            <div class="relative cursor-pointer" id="account-menu">
                <div onclick="toggleAccount()"
                    class="flex items-center gap-2 hover:text-[#FF6A00] transition-colors select-none">
                    <i class="fa-regular fa-user text-xl"></i>
                    <span class="hidden lg:block font-semibold text-[15px]">Account</span>
                    <i class="fas fa-chevron-down text-xs mt-1 text-gray-400"></i>
                </div>

                <!-- Dropdown -->
                <div id="account-dropdown"
                    class="hidden absolute right-0 top-[calc(100%+10px)] w-44 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                    <a href="{{ url('/login') }}"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-[#FF6A00] hover:bg-orange-50 transition-colors">
                        <i class="fa-solid fa-right-to-bracket text-gray-400 text-sm"></i>
                        Login
                    </a>
                    <a href="{{ url('/register') }}"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-[#FF6A00] hover:bg-orange-50 transition-colors">
                        <i class="fa-solid fa-user-plus text-gray-400 text-sm"></i>
                        Register
                    </a>
                </div>
            </div>

            <!-- Cart -->
            <a href="{{ url('/carts') }}" class="flex items-center gap-2 hover:text-[#FF6A00] transition-colors relative group">
                <div class="relative">
                    <i class="fa-solid fa-cart-shopping text-xl"></i>
                    <!-- Count Badge -->
                    <span id="cart-count" class="absolute -top-2 -right-2 bg-[#FF6A00] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white">
                        0
                    </span>
                </div>
                <span class="hidden lg:block font-semibold text-sm">Cart</span>
            </a>

            <!-- Mobile Menu Toggle -->
            <button class="lg:hidden text-2xl">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- 3. Bottom Category Nav (Optional) -->
    <div class="border-t border-gray-100 hidden md:block">
        <div class="container mx-auto px-4 flex items-center space-x-8 py-3">

            @foreach($headerCategories as $cat)
                <a class="text-sm font-medium hover:text-[#FF6A00]"
                href="{{ url('/category/' . $cat->slug) }}">
                {{ $cat->name }}
                </a>
            @endforeach
            <a class="text-sm font-medium hover:text-[#FF6A00]" href="{{ url('/brands') }}">Brands</a>
        <a class="text-sm font-medium text-red-500 hover:text-red-600" href="{{ url('/flash-sale') }}">Flash Sale</a>
        </div>
    </div>
</header>

@push('scripts')
<script>
    // account dropdown
    function toggleAccount() {
        const dropdown = document.getElementById('account-dropdown');
        dropdown.classList.toggle('hidden');
    }
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('account-menu');
        const dropdown = document.getElementById('account-dropdown');
        if (!menu.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
    // --- Cart Logic ---

    // 1. Initialize cart from LocalStorage (persists across page refreshes)
    let cart = JSON.parse(localStorage.getItem('orenmart_cart')) || [];

    // 2. Function to update the badge in the UI
    function updateCartDisplay() {
        const countElements = document.querySelectorAll('#cart-count');
        countElements.forEach(el => {
            el.innerText = cart.length;
            // Hide badge if cart is empty (optional)
            el.style.display = cart.length > 0 ? 'flex' : 'none';
        });
    }

    // 3. The Add to Cart function
    function addToCart(productId) {
        // Add item to our array
        cart.push(productId);

        // Save to localStorage
        localStorage.setItem('orenmart_cart', JSON.stringify(cart));

        // Update the UI
        updateCartDisplay();

        // Optional: Show a mini-notification instead of a big alert
        console.log("Product " + productId + " added to cart.");

        // If you want to redirect to cart page after adding (optional):
        // window.location.href = "{{ url('/carts') }}";
    }

    // --- Filter Toggle Logic ---
    function toggleAllFilters() {
        const panel = document.getElementById('all-filters-panel');
        const arrow = document.getElementById('all-filters-arrow');
        const isHidden = panel.classList.contains('hidden');

        if (isHidden) {
            panel.classList.remove('hidden');
            arrow.style.transform = 'rotate(180deg)';
        } else {
            panel.classList.add('hidden');
            arrow.style.transform = 'rotate(0deg)';
        }
    }

    // --- Account Dropdown Logic ---
    function toggleAccount() {
        const dropdown = document.getElementById('account-dropdown');
        dropdown.classList.toggle('hidden');
    }

    // Run on Page Load
    document.addEventListener('DOMContentLoaded', () => {
        updateCartDisplay();

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('account-menu');
            const dropdown = document.getElementById('account-dropdown');
            if (menu && !menu.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    });
</script>
@endpush
