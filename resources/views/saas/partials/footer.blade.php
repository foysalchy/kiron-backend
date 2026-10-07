 <!-- FOOTER -->
 <footer class="bg-[#0a061e] text-white pt-20 pb-6 px-4 md:px-10">
     <div class="container mx-auto">
         <!-- TOP SECTION: NEWSLETTER -->
         <div class="bg-[#16112f] border border-white/10 rounded-[30px] p-8 md:p-12 mb-20">
             <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                 <div class="text-center lg:text-left">
                     <h2 class="text-2xl md:text-3xl font-bold mb-3">
                         Is your business ready to go online?
                     </h2>
                     <p class="text-gray-300 text-lg md:text-base">
                         Subscribe to our newsletter and get e-commerce tips directly to your inbox.
                     </p>
                 </div>

                 <div class="w-full lg:w-auto flex flex-col sm:flex-row items-center gap-3">
                     <input type="email" placeholder="Email"
                         class="w-full sm:w-80 bg-transparent border border-gray-600 rounded-xl px-5 py-3.5 focus:outline-none focus:border-[#00555c] transition text-lg" />
                     <button
                         class="w-full sm:w-auto bg-[#00555c] hover:bg-[#078e9a] text-white px-8 py-3.5 rounded-xl font-bold transition whitespace-nowrap">
                         Subscribe
                     </button>
                 </div>
             </div>
         </div>

         <!-- MIDDLE SECTION: 4 COLUMNS -->
         <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
             <!-- Col 1: Logo & Socials -->
             <div class="space-y-6">
                 <a href="{{ route('saas.index') }}" class="flex items-center gap-2"
                     title="{{ $setup->shop_name ?? 'Home' }}">

                     <img src="{{ $setup->logo_url ?? asset('images/saas/Shopify_Logo.png') }}"
                         alt="{{ $setup->shop_name ?? 'Dorja' }} Logo" class="h-8 w-auto object-contain" fetchpriority="high"
                         loading="lazy" width="180" height="45"
                         onerror="this.onerror=null; this.src='{{ asset('images/saas/Shopify_Logo.png') }}';">

                 </a>
                 <p class="text-gray-400 leading-relaxed text-base">
                     Sales, inventory, accounting, CRM, and e-commerce for your business—all now on one powerful platform.
                 </p>
                 <div class="flex items-center gap-3">
                     @foreach ($socialLinks as $social)
                         <a href="{{ $social->link ?? '#' }}" target="_blank" aria-label="Follow us on {{ $social->name ?? 'Social Media' }}"
                             class="w-10 h-10 rounded-full border border-gray-700 flex items-center justify-center hover:bg-[#00555c] hover:border-[#00555c] transition group">

                             <i class="{{ $social->icon_name }} text-lg text-gray-400 group-hover:text-white"></i>
                         </a>
                     @endforeach
                 </div>
             </div>

             <!-- Col 2: Important Links -->
             <div>
                 <h3 class="text-lg font-bold mb-6 relative inline-block">
                     Important Links
                     <span class="absolute bottom-[-8px] left-0 w-16 h-[4px] bg-[#00555c] rounded-full"></span>
                 </h3>
                 <ul class="space-y-4 text-gray-400 text-base">
                     <li>
                         <a href="{{route('saas.package.list')}}" class="hover:text-white transition">Pricing</a>
                     </li>
                     <li>
                         <a href="{{route('saas.feature.list')}}" class="hover:text-white transition">Features</a>
                     </li>


                     <li>
                         <a href="{{route('saas.blog.list')}}" class="hover:text-white transition">Blog</a>
                     </li>
                     <li>
                         <a href="https://app.dorja.io/register" class="hover:text-white transition">Register</a>
                     </li>
                     <li>
                         <a href="https://app.dorja.io/" class="hover:text-white transition">Login</a>
                     </li>
                     <li>
                         <a href="{{ route('partner.login') }}" class="hover:text-white transition">Partner Login</a>
                     </li>
                     <li>
                         <a href="{{ route('partner.register') }}" class="hover:text-white transition">Become a Partner</a>
                     </li>
                 </ul>
             </div>

             <!-- Col 3: Company -->
             <div>
                 <h3 class="text-lg font-bold mb-6 relative inline-block">
                     Company
                     <span class="absolute bottom-[-8px] left-0 w-16 h-[4px] bg-[#00555c] rounded-full"></span>
                 </h3>
                 <ul class="space-y-4 text-gray-400 text-base">
                    @foreach ($footerPages as $page)
                         <li>
                             <a href="{{ url($page->slug) }}" class="hover:text-white transition">
                                 {{ $page->title }}
                             </a>
                         </li>
                     @endforeach
                     <li>
                         <a href="{{route('saas.contact')}}" class="hover:text-white transition">Contact Us</a>
                     </li>
                      <li>
                         <a href="{{route('saas.faq.list')}}" class="hover:text-white transition">Faq</a>
                     </li>
                 </ul>
             </div>

             <!-- Col 4: Help & Support -->
             <div>
                 <h3 class="text-lg font-bold mb-6 relative inline-block">
                     Help & Support
                     <span class="absolute bottom-[-8px] left-0 w-16 h-[4px] bg-[#00555c] rounded-full"></span>
                 </h3>
                 <ul class="space-y-5 text-gray-400 text-base">
                     <li class="flex items-start gap-3">
                         <i class="fa-solid fa-phone mt-1 text-[#00555c]"></i>
                         <span>{{ $setup->phone ?? '0188-8888888' }}</span>
                     </li>
                     <li class="flex items-start gap-3">
                         <i class="fa-solid fa-envelope mt-1 text-[#00555c]"></i>
                         <span>{{ $setup->email ?? 'hello@sopify.com' }}</span>
                     </li>
                     <li class="flex items-start gap-3">
                         <div class="flex-shrink-0 w-5">
                             <i class="fa-solid fa-location-dot mt-1.5 text-primary text-lg" aria-hidden="true"></i>
                         </div>

                         <address class="not-italic text-gray-300 leading-relaxed text-sm md:text-base">
                             @if ($setup && $setup->corporate_address)
                                 {!! $setup->corporate_address !!}
                             @else
                                 P 208/3 South Baridhara <br /> Dhaka -1222
                             @endif
                         </address>
                     </li>
                 </ul>
             </div>
         </div>

         <!-- BOTTOM BAR -->
         <div
             class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-lg text-gray-400">
             <p>
                 © 2026 <span class="text-white font-semibold"></span> All rights reserved.
             </p>
             {{-- <div class="flex items-center gap-6">
                 <a href="#" class="hover:text-white transition">Terms</a>
                 <a href="#" class="hover:text-white transition">Privacy</a>
                 <a href="#" class="hover:text-white transition">Cookie Policy</a>
             </div> --}}
         </div>
     </div>
 </footer>
<!-- Scroll to Top Button -->
<button
    id="backToTop"
    class="fixed bottom-8 right-8 z-[100] w-12 h-12 bg-[#00555c] text-white rounded-full flex items-center justify-center shadow-2xl opacity-0 invisible transition-all duration-300 hover:bg-black hover:-translate-y-1 focus:outline-none"
    aria-label="Scroll to Top"
>
    <i class="fa-solid fa-chevron-up text-xl"></i>
</button>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const backToTop = document.getElementById('backToTop');

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
