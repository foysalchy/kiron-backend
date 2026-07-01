 <!-- FOOTER -->
 <footer class="bg-[#0a061e] text-white pt-20 pb-6 px-4 md:px-10">
     <div class="container mx-auto">
         <!-- TOP SECTION: NEWSLETTER -->
         <div class="bg-[#16112f] border border-white/10 rounded-[30px] p-8 md:p-12 mb-20">
             <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                 <div class="text-center lg:text-left">
                     <h2 class="text-2xl md:text-3xl font-bold mb-3">
                         আপনার ব্যবসা কি অনলাইনে নিতে প্রস্তুত?
                     </h2>
                     <p class="text-gray-400 text-lg md:text-base">
                         আমাদের নিউজলেটার সাবস্ক্রাইব করুন এবং ই-কমার্স টিপস পান সরাসরি
                         আপনার ইনবক্সে।
                     </p>
                 </div>

                 <div class="w-full lg:w-auto flex flex-col sm:flex-row items-center gap-3">
                     <input type="email" placeholder="ই-মেইল"
                         class="w-full sm:w-80 bg-transparent border border-gray-600 rounded-xl px-5 py-3.5 focus:outline-none focus:border-[#34a487] transition text-lg" />
                     <button
                         class="w-full sm:w-auto bg-[#34a487] hover:bg-[#4a38b8] text-white px-8 py-3.5 rounded-xl font-bold transition whitespace-nowrap">
                         সাবস্ক্রাইব করুন
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
                         alt="{{ $setup->shop_name }} Logo" class="h-8 w-auto object-contain" fetchpriority="high"
                         loading="eager"
                         onerror="this.onerror=null; this.src='{{ asset('images/saas/Shopify_Logo.png') }}';">

                 </a>
                 <p class="text-gray-400 leading-relaxed text-[15px]">
                     আপনার ব্যবসার সেলস, ইনভেন্টরি, অ্যাকাউন্টিং, CRM এবং
                     ই-কমার্স—সবকিছু এখন একটি শক্তিশালী প্ল্যাটফর্মে।
                 </p>
                 <div class="flex items-center gap-3">
                     @foreach ($socialLinks as $social)
                         <a href="{{ $social->link ?? '#' }}" target="_blank"
                             class="w-10 h-10 rounded-full border border-gray-700 flex items-center justify-center hover:bg-[#34a487] hover:border-[#34a487] transition group">

                             <i class="{{ $social->icon_name }} text-lg text-gray-400 group-hover:text-white"></i>
                         </a>
                     @endforeach
                 </div>
             </div>

             <!-- Col 2: Important Links -->
             <div>
                 <h3 class="text-lg font-bold mb-6 relative inline-block">
                     গুরুত্বপূর্ণ লিংক
                     <span class="absolute bottom-[-8px] left-0 w-16 h-[4px] bg-[#34a487] rounded-full"></span>
                 </h3>
                 <ul class="space-y-4 text-gray-400 text-[15px]">
                     @foreach ($footerPages as $page)
                         <li>
                             <a href="{{ url('/page/' . $page->slug) }}" class="hover:text-white transition">
                                 {{ $page->title }}
                             </a>
                         </li>
                     @endforeach
                 </ul>
             </div>

             <!-- Col 3: Company -->
             <div>
                 <h3 class="text-lg font-bold mb-6 relative inline-block">
                     কোম্পানি
                     <span class="absolute bottom-[-8px] left-0 w-16 h-[4px] bg-[#34a487] rounded-full"></span>
                 </h3>
                 <ul class="space-y-4 text-gray-400 text-[15px]">
                     <li>
                         <a href="#" class="hover:text-white transition">আমাদের লক্ষ্য</a>
                     </li>
                     <li>
                         <a href="#" class="hover:text-white transition">ক্যারিয়ার</a>
                     </li>
                     <li>
                         <a href="#" class="hover:text-white transition">পার্টনারশিপ</a>
                     </li>
                     <li>
                         <a href="#" class="hover:text-white transition">প্রাইভেসি সেন্টার</a>
                     </li>
                 </ul>
             </div>

             <!-- Col 4: Help & Support -->
             <div>
                 <h3 class="text-lg font-bold mb-6 relative inline-block">
                     হেল্প & সাপোর্ট
                     <span class="absolute bottom-[-8px] left-0 w-16 h-[4px] bg-[#34a487] rounded-full"></span>
                 </h3>
                 <ul class="space-y-5 text-gray-400 text-[15px]">
                     <li class="flex items-start gap-3">
                         <i class="fa-solid fa-phone mt-1 text-[#34a487]"></i>
                         <span>{{ $setup->phone ?? '0188-8888888' }}</span>
                     </li>
                     <li class="flex items-start gap-3">
                         <i class="fa-solid fa-envelope mt-1 text-[#34a487]"></i>
                         <span>{{ $setup->email ?? 'hello@sopify.com' }}</span>
                     </li>
                     <li class="flex items-start gap-3">
                         <div class="flex-shrink-0 w-5">
                             <i class="fa-solid fa-location-dot mt-1.5 text-primary text-lg" aria-hidden="true"></i>
                         </div>

                         <address class="not-italic text-gray-400 leading-relaxed text-sm md:text-base">
                             @if ($setup && $setup->corporate_address)
                                 {!! $setup->corporate_address !!}
                             @else
                                 প ২০৮/৩ দক্ষিণ বাড্ডা <br /> ঢাকা -১২২২
                             @endif
                         </address>
                     </li>
                 </ul>
             </div>
         </div>

         <!-- BOTTOM BAR -->
         <div
             class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-lg text-gray-500">
             <p>
                 © ২০২৬ <span class="text-white font-semibold"></span> সকল
                 স্বত্ব সংরক্ষিত।
             </p>
             {{-- <div class="flex items-center gap-6">
                 <a href="#" class="hover:text-white transition">শর্তাবলী</a>
                 <a href="#" class="hover:text-white transition">গোপনীয়তা</a>
                 <a href="#" class="hover:text-white transition">কুকি পলিসি</a>
             </div> --}}
         </div>
     </div>
 </footer>
