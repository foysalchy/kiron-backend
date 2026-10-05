<!-- FOOTER -->
<footer class="footer-custom-bg text-footer">

    <!-- 1. Top Features Row -->
     @if($footerFeatures->count() > 0)
    <div class="p-6">
        <div class="container mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($footerFeatures as $feature)
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 md:w-12 md:h-12 primary-bg rounded-full flex items-center justify-center shrink-0">
                            @if ($feature->icon_file)
                                <img src="{{ asset('storage/' . $feature->icon_file) }}" alt="{{ $feature->title }}" height="" width=""
                                    class="w-5 h-5 md:w-6 md:h-6 brightness-0 invert">
                            @else
                                <i class="{{ $feature->icon_url ?? 'fas fa-truck' }} text-footer text-lg"></i>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-medium text-sm md:text-[16px] text-footer leading-tight">
                                {{ $feature->title }}</h4>
                            <p class="opacity-90 text-xs md:text-sm mt-0.5">
                                {{ $feature->subtitle ?? $feature->text_content }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div>
        <!-- 2. Main Footer Content -->
        <div class="container mx-auto px-4 py-10 md:py-16">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 md:gap-12">

                <!-- Column 1: Brand Info -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <div class="flex items-center gap-3 mb-5">
                        @if ($setup && $setup->logo)
                        <img src="{{ $setup->logo_url ?? asset('images/logo.jpeg') }}" height="80" width="200"
                            alt="{{ $setup->shop_name ?? 'Little Joy Baby Shop' }} Logo"
                            class="h-12 md:h-16 w-auto object-contain" loading="lazy" />
                        @else
                            <div class="w-10 h-10 flex items-center justify-center rounded-lg">
                                <span
                                    class="text-footer text-xl font-semibold">{{ substr($setup->shop_name ?? 'O', 0, 1) }}</span>
                            </div>
                        @endif
                        
                    </div>
                    <p class="opacity-90 text-[16px] leading-relaxed mb-5">
                        {{ $setup->description ?? 'Your trusted partner for automotive accessories and car care products.' }}
                    </p>
                    <ul class="space-y-3 text-[16px]">
                        <li class="flex items-start gap-3 opacity-90">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-[var(--primary-color)] mt-0.5 shrink-0">
                                <path
                                    d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0">
                                </path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span>{{ $setup->corporate_address ?? 'Dhaka, Bangladesh' }}</span>
                        </li>
                        <li class="flex items-center gap-3 opacity-90">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-[var(--primary-color)] shrink-0">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                </path>
                            </svg>
                            <a href="tel:{{ $setup->phone ?? ''}}" class="hover:text-[var(--primary-color)] transition-colors">
                                {{ $setup->phone ?? ''}}
                            </a>
                        </li>
                        <li class="flex items-center gap-3 opacity-90">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-[var(--primary-color)] shrink-0">
                                <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                            </svg>
                            <a href="mailto:{{ $setup->email ?? ''}}" class="hover:text-[var(--primary-color)] transition-colors">
                                {{ $setup->email ?? ''}}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Column 2: Quick Links -->
                <div class=" ">
                    <h3 class="text-[18px] font-bold mb-4 md:mb-4">Quick Links</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('contact.index') }}"
                                class="text-[16px] opacity-90 hover:underline transition-colors">Contact Us</a></li>
                        <li><a href="{{ route('order.track') }}"
                                class="text-[16px] opacity-90 hover:underline transition-colors">Track Order</a></li>
                        <li><a href="{{ route('faq.index') }}"
                                class="text-[16px] opacity-90 hover:underline transition-colors">Help & Support</a></li>
                        <li><a href="{{ route('blog.index') }}"
                                class="text-[16px] opacity-90 hover:underline transition-colors">Blog</a></li>
                    </ul>
                </div>

                <!-- Column 3: Pages -->
                <div>
                    <h3 class="text-base md:text-lg font-bold mb-4 md:mb-4">Pages</h3>
                    <ul class="space-y-2.5 text-sm">
                        @foreach ($footerPages as $page)
                            <li>
                                <a href="{{ url($page->slug) }}"
                                    class="text-[16px] opacity-90 hover:underline transition-colors">
                                    {{ $page->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Column 4: Newsletter -->
                <div>
                    <h3 class="text-base text-footer md:text-lg font-bold mb-4 md:mb-4">Newsletter</h3>
                    <p class="opacity-90 text-[16px] mb-4">Subscribe to get updates on new products and exclusive offers.
                    </p>
                    <div class="flex mb-5">
                        <input type="email" placeholder="Enter your email"
                            class="  border border-gray-700 text-footer px-3 py-2.5 rounded-l-md w-full text-sm focus:outline-none focus:border-[#BD4F00]">
                        <button
                            class="primary-bg hover:bg-[#a34400] px-4 py-2.5 rounded-r-md font-semibold text-sm transition-colors whitespace-nowrap">
                            Subscribe
                        </button>
                    </div>
                    <div class="flex gap-4 flex-wrap">
                        @foreach ($socialLinks as $social)
                            <a href="{{ $social->link }}" target="_blank"
                                aria-label="Follow us on {{ $social->name }}"
                                class="opacity-90 text-lg transition-all duration-300"
                                onmouseover="this.style.color='{{ $social->hover_bg ?? '#BD4F00' }}'"
                                onmouseout="this.style.color='#9CA3AF'">
                                @if ($social->icon_image)
                                    <img src="{{ $social->icon_image ?? '' }}" alt="social icon" height="" width=""
                                        class="h-5 w-5 object-contain">
                                @else
                                    <i class="{{ $social->icon_class ?? 'fab fa-share' }}" aria-hidden="true"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        <!-- 3. Bottom Bar -->
        <div class="border-t border-white/40 py-5">
            <div class="container mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-4">
                <div
                    class="flex flex-wrap justify-center md:justify-start gap-4 text-xs md:text-sm opacity-90 text-center">
                    <p>Â© {{ date('Y') }} {{ $setup->shop_name ?? 'OrenMart' }}. All rights reserved.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs md:text-sm opacity-90">We Accept:</span>
                    <div class="flex gap-2">
                        @foreach ($footerBottomRight as $item)
                            <div class="bg-white px-2 py-1 rounded text-gray-700 text-xs h-16 w-42 flex items-center">
                                @if ($item->icon_file)
                                    <img src="{{ asset('storage/' . $item->icon_file) ?? './images/template1/frontend/default.webp' }}"
                                        height="16" width="120" loading="lazy" alt="{{ $item->title }}"
                                        class="h-4">
                                @else
                                    {{ $item->title }}
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

</footer>

