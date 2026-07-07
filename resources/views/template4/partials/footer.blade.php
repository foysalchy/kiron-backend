    <!-- FOOTER SECTION -->
    <footer class="primary-bg text-white pt-20 pb-10" role="contentinfo">
        <div class="container mx-auto p-4">
            <!-- Top Part: Logo & Menus -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12 mb-16">
                <!-- Column 1: Logo & Newsletter (Takes 2 parts space) -->
                <div class="lg:col-span-2 space-y-8">
                    <div>
                        <a href="{{ route('home') }}" aria-label="Little Joy Home">
                            <img src="{{ $setup->logo_url ?? asset('images/logo.png') }}"
                            loading="lazy" alt="Little Joy Baby Shop Logo" height="80" width="150" class="h-20">
                        </a>
                    </div>
                    <p class="text-base font-light leading-relaxed max-w-[280px]">
                        No need to worry, we'll help you make sense of it all...
                    </p>

                    <!-- Newsletter Form (Design Same, Logic Dynamic) -->
                    <form id="newsletter-form" class="relative max-w-[320px]">
                        @csrf
                        <input type="email" name="email" id="subscriber-email" placeholder="Email Address"
                            aria-label="Email address for newsletter" required
                            class="w-full bg-white text-gray-800 py-3 px-5 rounded-lg focus:outline-none placeholder:text-gray-600 font-medium border border-transparent focus:border-purple-200 transition-all" />
                        <button type="submit" id="subscribe-btn"
                            class="absolute right-1 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center text-[#3b143c] hover:scale-110 transition-transform cursor-pointer"
                            aria-label="Subscribe">
                            <i class="fa-solid fa-paper-plane text-xl"></i>
                        </button>
                    </form>
                </div>

                <!-- Column 2: SOLUTIONS -->
                <div class="lg:col-start-3">
                    <h3 class="text-lg font-bold uppercase tracking-widest mb-8">
                        Pages
                    </h3>
                    <ul class="space-y-4 text-white/90 text-base">
                        @foreach ($footerPages as $page)
                        <li><a href="{{ url('page', ['slug' => $page->slug]) }}" class="hover:underline">{{ $page->title }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Column 3: ABOUT US -->
                <div>
                    <h3 class="text-lg font-bold uppercase tracking-widest mb-8">
                        Quick Link
                    </h3>
                    <ul class="space-y-4 text-white/90 text-base">
                        <li><a href="{{ route('blog.index') }}" class="hover:underline">Blog</a></li>
                        <li><a href="{{ route('shop.index') }}" class="hover:underline">Shop</a></li>
                        <li><a href="{{ route('contact.index') }}" class="hover:underline">Contact Us</a></li>
                    </ul>
                </div>


                <!-- Column 5: SOCIAL -->
                <div>
                    <h3 class="text-lg font-bold uppercase tracking-widest mb-8">
                        Social
                    </h3>
                    <ul class="space-y-4 text-white/90 text-base">
                        @foreach ($socialLinks as $social)
                            <li>
                                <a href="{{ $social->link }}" target="_blank" rel="noopener noreferrer"
                                    class="flex items-center gap-3 hover:underline group transition-all"
                                    aria-label="{{ $social->icon_name ?? 'Social Link' }}">
                                    <span>{{ $social->icon_name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Bottom Part: Divider & Copyright -->
            <div class="border-t border-white/40 pt-8 mt-10">
                <p class="text-center text-white/90 text-base tracking-wide">
                    @ {{ $setup->shop_name }} 2025. All Rights Reserved
                </p>
            </div>
        </div>
    </footer>
    <script>
        document.getElementById('newsletter-form')?.addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('subscriber-email').value;
            const btn = document.getElementById('subscribe-btn');
            const originalText = btn.innerHTML;

            // লোডিং স্টেট
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch("{{ route('newsletter.subscribe') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        email: email
                    })
                })
                .then(async response => {
                    const data = await response.json();
                    if (response.ok) {
                        toastr.success(data.message);
                        document.getElementById('subscriber-email').value = ''; // ইনপুট ক্লিয়ার
                    } else {
                        toastr.error(data.message || 'Validation error');
                    }
                })
                .catch(error => {
                    toastr.error('Something went wrong. Please try again.');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        });
    </script>
