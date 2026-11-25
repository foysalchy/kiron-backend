<!-- FOOTER (Exact Match to dorja.io — Modern Homepage Redesign-2nd.html) -->
<footer id="contact" class="relative z-20 px-4 md:px-10" style="background: #0A1F22; color: #C6DDDA;">
    <div class="container mx-auto max-w-[1240px] flex flex-col pt-16 pb-12">

        <!-- TOP SECTION: NEWSLETTER -->
        <div class="pb-10 md:pb-14 mb-8 md:mb-14"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 420px), 1fr)); gap: 28px; align-items: center; border-bottom: 1px solid #1e4547;">
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <h2 style="font-size: 30px; letter-spacing: -0.03em; font-weight: 600; color: #fff;">
                    Is your business ready to go online?
                </h2>
                <p style="font-size: 16px; line-height: 1.6; color: #C6DDDA;">
                    Subscribe to our newsletter and get e-commerce tips directly to your inbox.
                </p>
            </div>

            <form style="display: flex; gap: 8px; background: #12302F; border: 1px solid #234B4D; border-radius: 999px; padding: 6px; max-width: 520px; width: 100%; justify-self: end;"
                onsubmit="event.preventDefault();">
                <input type="email" placeholder="Enter your email" aria-label="Email address"
                    style="flex: 1; min-width: 0; background: transparent; border: 0; color: #fff; font: inherit; font-size: 16px; padding: 0 16px; min-height: 44px; outline: none;" required />
                <button type="submit"
                    style="background: #7CE0C3; color: #052528; border: 0; font: inherit; font-weight: 600; font-size: 15px; padding: 0 24px; border-radius: 999px; min-height: 44px; cursor: pointer; transition: opacity 0.2s;"
                    onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    Subscribe
                </button>
            </form>
        </div>

        <!-- MIDDLE SECTION: 4 COLUMNS -->
        <div class="pb-8 md:pb-12" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 200px), 1fr)); gap: 36px;">

            <!-- Col 1: Logo & Socials -->
            <div style="display: flex; flex-direction: column; gap: 16px; grid-column: span 1;">
                <a href="{{ route('saas.index') }}" style="display: flex; align-items: center; gap: 10px; color: #fff;"
                    title="{{ $setup->shop_name ?? 'Dorja.io' }}">
                    @php $saasFooterLogo = $setup->footer_logo_url ?? null; @endphp
                    @if ($setup && $saasFooterLogo)
                        <img src="{{ $saasFooterLogo }}" alt="{{ $setup->shop_name ?? 'Dorja' }} Logo"
                            class="h-8 w-auto object-contain"
                            onerror="this.onerror=null; this.src='{{ asset('images/saas/Shopify_Logo.png') }}';">
                    @else
                        <span aria-hidden="true" style="width: 36px; height: 36px; border-radius: 10px; background: #7CE0C3; display: grid; place-items: center; color: #052528; font-weight: 700; font-size: 20px;">d</span>
                        <span style="font-size: 22px; font-weight: 700; letter-spacing: -0.04em; color: #fff;">dorja<span style="color: #7CE0C3;">.io</span></span>
                    @endif
                </a>
                <p style="font-size: 16px; line-height: 1.6; max-width: 300px; color: #C6DDDA;">
                    Sales, inventory, accounting, CRM, and e-commerce for your business—all now on one powerful platform.
                </p>
                <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px;">
                    @foreach ($socialLinks as $social)
                        <a href="{{ $social->link ?? '#' }}" target="_blank" aria-label="Follow us on {{ $social->name ?? 'Social Media' }}"
                            style="width: 36px; height: 36px; border-radius: 50%; background: #12302F; border: 1px solid #234B4D; display: grid; place-items: center; color: #C6DDDA; transition: all 0.2s;"
                            onmouseover="this.style.background='#7CE0C3'; this.style.color='#052528';"
                            onmouseout="this.style.background='#12302F'; this.style.color='#C6DDDA';">
                            <i class="{{ $social->icon_name }} text-sm"></i>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Col 2: Important Links -->
            <nav aria-label="Important Links" style="display: flex; flex-direction: column; gap: 10px;">
                <h3 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 4px;">Important Links</h3>
                <a href="{{ route('saas.package.list') }}" style="color: #C6DDDA; font-size: 16px; padding: 2px 0; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#C6DDDA'">Pricing</a>
                <a href="{{ route('saas.feature.list') }}" style="color: #C6DDDA; font-size: 16px; padding: 2px 0; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#C6DDDA'">Features</a>
                <a href="{{ route('saas.blog.list') }}" style="color: #C6DDDA; font-size: 16px; padding: 2px 0; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#C6DDDA'">Blog</a>
                <a href="https://app.dorja.io/register" style="color: #C6DDDA; font-size: 16px; padding: 2px 0; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#C6DDDA'">Register</a>
                <a href="https://app.dorja.io/" style="color: #C6DDDA; font-size: 16px; padding: 2px 0; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#C6DDDA'">Login</a>
                <a href="{{ route('partner.login') }}" style="color: #C6DDDA; font-size: 16px; padding: 2px 0; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#C6DDDA'">Partner Login</a>
                <a href="{{ route('partner.register') }}" style="color: #C6DDDA; font-size: 16px; padding: 2px 0; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#C6DDDA'">Become a Partner</a>
            </nav>

            <!-- Col 3: Company -->
            <nav aria-label="Company" style="display: flex; flex-direction: column; gap: 10px;">
                <h3 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 4px;">Company</h3>
                @foreach ($footerPages as $page)
                    <a href="{{ url($page->slug) }}" style="color: #C6DDDA; font-size: 16px; padding: 2px 0; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#C6DDDA'">
                        {{ $page->title }}
                    </a>
                @endforeach
                <a href="{{ route('saas.contact') }}" style="color: #C6DDDA; font-size: 16px; padding: 2px 0; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#C6DDDA'">Contact Us</a>
                <a href="{{ route('saas.faq.list') }}" style="color: #C6DDDA; font-size: 16px; padding: 2px 0; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#C6DDDA'">Faq</a>
            </nav>

            <!-- Col 4: Help & Support -->
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <h3 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 2px;">Help &amp; Support</h3>
                <a href="tel:{{ $setup->phone ?? '01864411645' }}" style="color: #C6DDDA; font-size: 16px; display: flex; gap: 10px; align-items: center; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#C6DDDA'">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#7CE0C3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"></path></svg>
                    <span>{{ $setup->phone ?? '01864411645' }}</span>
                </a>
                <a href="mailto:{{ $setup->email ?? 'info@dorja.io' }}" style="color: #C6DDDA; font-size: 16px; display: flex; gap: 10px; align-items: center; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#C6DDDA'">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#7CE0C3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 7l9 6 9-6"></path></svg>
                    <span>{{ $setup->email ?? 'info@dorja.io' }}</span>
                </a>
                <div style="font-size: 16px; display: flex; gap: 10px; align-items: flex-start; line-height: 1.5; color: #C6DDDA;">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#7CE0C3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 3px"><path d="M12 22s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12z"></path><circle cx="12" cy="10" r="2.5"></circle></svg>
                    <address class="not-italic">
                        @if ($setup && $setup->corporate_address)
                            {!! $setup->corporate_address !!}
                        @else
                            House#85, Road#3, Chairman Bari, Banani
                        @endif
                    </address>
                </div>
            </div>

        </div>

        <!-- BOTTOM BAR -->
        <div class="mt-8 md:mt-14 pt-6 md:pt-8"
            style="border-top: 1px solid #1e4547; font-size: 15px; color: #8FB0AC; display: flex; flex-direction: row; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>© 2026 <span style="color: #fff; font-weight: 600;">{{ $setup->shop_name ?? 'dorja.io' }}</span>. All rights reserved.</div>
        </div>

    </div>
</footer>

<!-- Scroll to Top Button (Brand Styled) -->
<button
    id="backToTop"
    class="fixed bottom-8 right-8 z-[100] w-12 h-12 bg-[#00555C] text-white rounded-full flex items-center justify-center shadow-2xl opacity-0 invisible transition-all duration-300 hover:bg-[#7CE0C3] hover:text-[#052528] hover:-translate-y-1 focus:outline-none"
    aria-label="Scroll to Top"
>
    <i class="fa-solid fa-chevron-up text-xl"></i>
</button>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const backToTop = document.getElementById('backToTop');
        if (!backToTop) return;

        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTop.classList.remove('opacity-0', 'invisible');
                backToTop.classList.add('opacity-100', 'visible');
            } else {
                backToTop.classList.add('opacity-0', 'invisible');
                backToTop.classList.remove('opacity-100', 'visible');
            }
        });

        backToTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
</script>
