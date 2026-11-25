<header id="saas-header" class="sticky top-0 z-50 w-full pt-3 px-4 md:px-6 pointer-events-none transition-transform duration-300 ease-in-out">
    <div class="glass container mx-auto max-w-[1240px] rounded-full px-4 md:pl-4 md:pr-2.5 py-2 md:py-2.5 flex items-center justify-between pointer-events-auto">
        <!-- Logo Section -->
        <div class="flex-shrink-0 md:px-6">
            <a href="{{ route('saas.index') }}" class="flex items-center gap-2"
                aria-label="{{ $setup->shop_name ?? 'Home' }}">
                @if ($setup && $setup->logo_url)
                    <img src="{{ $setup->logo_url }}" alt="{{ $setup->shop_name }}" class="h-8 md:h-9 w-auto"
                        fetchpriority="high" loading="eager" width="180" height="45" onerror="this.onerror=null; this.src='{{ asset('images/saas/Shopify_Logo.png') }}';">
                @else
                    @if (file_exists(public_path('images/saas/dorja-logo.webp')))
                        <img src="{{ asset('images/saas/dorja-logo.webp') }}" alt="Default Logo" fetchpriority="high" loading="eager" width="180" height="45"
                            class="h-8 md:h-9 w-auto">
                    @else
                        <span class="text-xl font-bold text-[#0A1F22]">{{ $setup->shop_name ?? 'Bhaiya Digital' }}</span>
                    @endif
                @endif
            </a>
        </div>

        <!-- Navigation Links (Desktop) with Smooth Sliding Glider -->
        <nav id="desktop-nav" class="relative hidden lg:flex items-center space-x-1 p-1">
            <!-- Sliding Pill Glider -->
            <div id="nav-glider" class="absolute bg-[#E6EFED] rounded-full transition-all duration-300 ease-out pointer-events-none opacity-0 z-0"></div>

            <a href="{{ route('saas.index') }}"
                class="nav-item relative z-10 text-lg px-4 py-1.5 rounded-full transition-colors duration-200 {{ request()->routeIs('saas.index') ? 'is-active text-[#00555C] font-bold' : 'text-[#2C4346] hover:text-[#00555C] font-semibold' }}">
                Home
            </a>
            <a href="{{ route('saas.feature.list') }}"
                class="nav-item relative z-10 text-lg px-4 py-1.5 rounded-full transition-colors duration-200 {{ request()->routeIs('saas.feature.*') ? 'is-active text-[#00555C] font-bold' : 'text-[#2C4346] hover:text-[#00555C] font-semibold' }}">
                Features
            </a>
            <a href="{{ route('saas.index') }}#intergation"
                class="nav-item relative z-10 text-lg px-4 py-1.5 rounded-full transition-colors duration-200 text-[#2C4346] hover:text-[#00555C] font-semibold">
                Integration
            </a>
            <a href="{{ route('saas.package.list') }}"
                class="nav-item relative z-10 text-lg px-4 py-1.5 rounded-full transition-colors duration-200 {{ request()->routeIs('saas.package.*') ? 'is-active text-[#00555C] font-bold' : 'text-[#2C4346] hover:text-[#00555C] font-semibold' }}">
                Pricing
            </a>
            <a href="{{ route('saas.partner') }}"
                class="nav-item relative z-10 text-lg px-4 py-1.5 rounded-full transition-colors duration-200 {{ request()->routeIs('saas.partner') ? 'is-active text-[#00555C] font-bold' : 'text-[#2C4346] hover:text-[#00555C] font-semibold' }}">
                Partner
            </a>
            <a href="{{ route('saas.faq.list') }}"
                class="nav-item relative z-10 text-lg px-4 py-1.5 rounded-full transition-colors duration-200 {{ request()->routeIs('saas.faq.*') ? 'is-active text-[#00555C] font-bold' : 'text-[#2C4346] hover:text-[#00555C] font-semibold' }}">
                FAQ
            </a>
            <a href="{{ route('saas.contact') }}"
                class="nav-item relative z-10 text-lg px-4 py-1.5 rounded-full transition-colors duration-200 {{ request()->routeIs('saas.contact') ? 'is-active text-[#00555C] font-bold' : 'text-[#2C4346] hover:text-[#00555C] font-semibold' }}">
                Contact
            </a>
        </nav>

        <!-- Right Side Buttons -->
        <div class="flex items-center space-x-3 md:space-x-6">
            <a href="https://app.dorja.io" class="hidden md:block text-[#2C4346] font-bold text-lg hover:text-[#00555C]">
                Login
            </a>
            <!-- CTA Button -->
            <a href="https://app.dorja.io/register"
                class="bg-[#00555C] hover:bg-[#003A3F] text-white px-4 py-2.5 md:px-6 md:py-3 rounded-full font-bold text-xs md:text-lg transition shadow-lg shadow-teal-900/20 whitespace-nowrap">
                Start Free Trial
            </a>

            <!-- Mobile Menu Toggle Button -->
            <button id="menu-toggle" class="lg:hidden text-gray-700 text-xl p-1" aria-label="mobile menu">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
        </div>
    </div>
</header>

    <!-- MOBILE MENU SIDEBAR (Hidden by default) -->
    <div id="mobile-menu" class="fixed inset-0 z-[60] lg:hidden translate-x-full pointer-events-auto">
        <!-- Overlay -->
        <div id="menu-overlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

        <!-- Sidebar Content -->
        <div class="absolute right-0 top-0 h-full w-[280px] shadow-2xl p-6" style="background-color: #ffffff;">
            <div class="flex items-center justify-between mb-8">
                <span class="font-bold text-xl">Menu</span>
                <button id="menu-close" class="text-gray-700 text-2xl" aria-label="sidebar menu">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <nav class="flex flex-col space-y-5">
                <a href="{{ route('saas.index') }}" class="text-[#00555c] font-bold text-lg">Home</a>
                <a href="{{ route('saas.feature.list') }}" class="text-gray-700 font-semibold text-lg border-b border-gray-50 pb-2">Features</a>
                <a href="{{ route('saas.index') }}#intergation"
                    class="text-gray-700 font-semibold text-lg border-b border-gray-50 pb-2">Integration</a>
                <a href="{{ route('saas.package.list') }}" class="text-gray-700 font-semibold text-lg border-b border-gray-50 pb-2">Pricing</a>
                <a href="{{ route('saas.partner') }}" class="text-gray-700 font-semibold text-lg border-b border-gray-50 pb-2">Partner</a>
                <a href="{{ route('saas.contact') }}" class="text-gray-700 font-semibold text-lg border-b border-gray-50 pb-2">Contact</a>
                <div class="pt-4">
                    <a href="https://app.dorja.io"
                        class="block text-center bg-[#00555C] hover:bg-[#003A3F] text-white py-3 rounded-full font-bold transition shadow-lg shadow-teal-900/20">Login</a>
                </div>
            </nav>
        </div>
    </div>

    <script>
        (function () {
            const header = document.getElementById("saas-header");
            if (!header) return;
            let lastScrollY = window.scrollY;
            window.addEventListener("scroll", function () {
                const currentScrollY = window.scrollY;
                if (currentScrollY <= 80 || currentScrollY < lastScrollY) {
                    header.style.transform = "translateY(0)";
                } else if (currentScrollY > lastScrollY) {
                    header.style.transform = "translateY(-100%)";
                }
                lastScrollY = currentScrollY;
            }, { passive: true });
        })();

        document.addEventListener("DOMContentLoaded", function () {
            const nav = document.getElementById("desktop-nav");
            const glider = document.getElementById("nav-glider");
            if (!nav || !glider) return;

            const items = nav.querySelectorAll(".nav-item");
            let activeItem = nav.querySelector(".nav-item.is-active");

            function moveGlider(target, immediate = false) {
                if (!target) {
                    glider.style.opacity = "0";
                    return;
                }
                const navRect = nav.getBoundingClientRect();
                const targetRect = target.getBoundingClientRect();

                if (immediate) {
                    glider.style.transition = "none";
                } else {
                    glider.style.transition = "all 0.65s cubic-bezier(0.25, 1, 0.5, 1)";
                }

                glider.style.left = (targetRect.left - navRect.left) + "px";
                glider.style.top = (targetRect.top - navRect.top) + "px";
                glider.style.width = targetRect.width + "px";
                glider.style.height = targetRect.height + "px";
                glider.style.opacity = "1";
            }

            // Set glider on initial load without jump
            if (activeItem) {
                moveGlider(activeItem, true);
                requestAnimationFrame(() => {
                    glider.style.transition = "all 0.65s cubic-bezier(0.25, 1, 0.5, 1)";
                });
            }

            // Smooth sliding when selecting/clicking a menu item
            items.forEach(item => {
                item.addEventListener("click", function (e) {
                    items.forEach(i => {
                        i.classList.remove("is-active", "font-bold", "text-[#0c4a45]");
                        i.classList.add("text-gray-700", "font-semibold");
                    });

                    this.classList.remove("text-gray-700");
                    this.classList.add("is-active", "font-bold", "text-[#0c4a45]");
                    activeItem = this;

                    moveGlider(this);

                    // If it's an on-page anchor link (e.g., #intergation), smoothly scroll to section
                    const href = this.getAttribute("href");
                    if (href && href.includes("#")) {
                        const hash = href.substring(href.indexOf("#"));
                        const targetEl = document.querySelector(hash);
                        if (targetEl) {
                            e.preventDefault();
                            targetEl.scrollIntoView({ behavior: "smooth" });
                            history.pushState(null, null, hash);
                        }
                    }
                });
            });

            window.addEventListener("resize", function () {
                if (activeItem) moveGlider(activeItem, true);
            });
        });
    </script>
