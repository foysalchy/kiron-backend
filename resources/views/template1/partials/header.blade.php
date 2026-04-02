<header class="w-full bg-white sticky top-0 z-50 font-['Outfit']">

    <!-- 1. Top Bar (Orange Row) -->
    <div class="bg-[#FF6A00] text-white py-2 text-[13px] hidden lg:block">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center gap-6">
                <span class="flex items-center gap-2">
                    <i class="fas fa-phone-alt text-xs"></i> +1 (555) 123-4567
                </span>
                <span class="flex items-center gap-2">
                    <i class="fas fa-envelope text-xs"></i> support@orenmart.com
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
        <a href="{{ url('/home') }}" class="flex items-center gap-3 flex-shrink-0">
            <div class="bg-[#FF6A00] w-10 h-12 flex items-center justify-center rounded-lg shadow-sm">
                <span class="text-white text-2xl font-bold">O</span>
            </div>
            <span class="text-2xl font-extrabold text-[#1D2128] tracking-tight">OrenMart</span>
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
            <a href="{{ url('/carts') }}" class="flex items-center gap-2 hover:text-[#FF6A00] transition-colors">
                <i class="fa-solid fa-cart-shopping text-xl"></i>
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

            <a class="text-sm font-medium hover:text-[#FF6A00]" href="/category/car-interior">Car Interior</a>
            <a class="text-sm font-medium hover:text-[#FF6A00]" href="/category/car-exterior">Car Exterior</a>
            <a class="text-sm font-medium hover:text-[#FF6A00]" href="/category/electronics">Electronics</a>
            <a class="text-sm font-medium hover:text-[#FF6A00]" href="/category/oil-care">Oil & Care</a>
            <a class="text-sm font-medium hover:text-[#FF6A00]" href="/category/oil-care">Performance</a>
            <a class="text-sm font-medium hover:text-[#FF6A00]" href="/category/oil-care">Safety</a>
            <a class="text-sm font-medium hover:text-[#FF6A00]" href="{{ url('/brands') }}">Brands</a>
            <a class="text-sm font-medium text-red-500 hover:text-red-600" href="/flash-sale">Flash Sale</a>
        </div>
    </div>
</header>
<script>
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
</script>
