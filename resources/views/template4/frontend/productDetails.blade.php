@extends('template4.layouts.front')

@section('content')
  <section class="bg-[#F9F9F9] py-2">
    <!-- Responsive Breadcrumbs -->
    <nav aria-label="Breadcrumb" class="container mx-auto px-4 flex flex-wrap items-center pt-2 md:pt-4 gap-1 md:gap-2 text-xs sm:text-sm md:text-base lg:text-lg mb-4 md:mb-6">
      <a href="#" class="text-[#632085] hover:text-[#52166d] transition font-medium">Home</a>
      <span class="text-gray-400">/</span>
      <a href="#" class="text-[#632085] hover:text-[#52166d] transition font-medium">Products</a>
      <span class="text-gray-400">/</span>
      <a href="#" class="text-[#632085] hover:text-[#52166d] transition font-medium">Feeding Item baby and mom</a>
      <span class="text-gray-400">/</span>
      <span class="text-gray-500 font-normal truncate">Luxury Essentials for Growing Families</span>
    </nav>
  </section>
    <section class="bg-white">
      <div class="container mx-auto px-4 py-4 md:py-8">
        <!-- ─── MAIN PRODUCT GRID ─── -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 items-start pb-8 md:pb-16">

          <!-- ════════════════════════════════════════
             LEFT COLUMN (Gallery with Sliding Animation)
            ════════════════════════════════════════ -->
          <div class="lg:col-span-7 flex flex-col md:flex-row gap-3 md:gap-4">

            <!-- Thumbnails on Left (Desktop) / Bottom (Mobile) -->
            <div
              class="flex md:flex-col gap-2 overflow-x-auto md:overflow-y-auto shrink-0 order-2 md:order-1 md:w-20 lg:w-24 pb-2 md:pb-0">
              <!-- Thumbnail 1 (Active) -->
              <button
                class="thumb-btn border-2 border-[#632085] p-0.5 rounded overflow-hidden w-16 h-16 sm:w-20 sm:h-20 md:w-full md:h-auto aspect-square hover:border-[#632085] transition shrink-0"
                data-index="0">
                <img src="https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?q=80&w=400" alt="Thumbnail 1"
                  class="w-full h-full object-cover" />
              </button>
              <!-- Thumbnail 2 -->
              <button
                class="thumb-btn border border-gray-200 p-0.5 rounded overflow-hidden w-16 h-16 sm:w-20 sm:h-20 md:w-full md:h-auto aspect-square hover:border-[#632085] transition shrink-0"
                data-index="1">
                <img src="https://images.unsplash.com/photo-1622290291165-d341f1938b86?q=80&w=400" alt="Thumbnail 2"
                  class="w-full h-full object-cover" />
              </button>
              <!-- Thumbnail 3 -->
              <button
                class="thumb-btn border border-gray-200 p-0.5 rounded overflow-hidden w-16 h-16 sm:w-20 sm:h-20 md:w-full md:h-auto aspect-square hover:border-[#632085] transition shrink-0"
                data-index="2">
                <img src="https://images.unsplash.com/photo-1503919545889-aef636e10ad4?q=80&w=400" alt="Thumbnail 3"
                  class="w-full h-full object-cover" />
              </button>
              <!-- Thumbnail 4 -->
              <button
                class="thumb-btn border border-gray-200 p-0.5 rounded overflow-hidden w-16 h-16 sm:w-20 sm:h-20 md:w-full md:h-auto aspect-square hover:border-[#632085] transition shrink-0"
                data-index="3">
                <img src="https://images.unsplash.com/photo-1515488042361-404e9250afef?q=80&w=400" alt="Thumbnail 4"
                  class="w-full h-full object-cover" />
              </button>
              <!-- Thumbnail 5 -->
              <button
                class="thumb-btn border border-gray-200 p-0.5 rounded overflow-hidden w-16 h-16 sm:w-20 sm:h-20 md:w-full md:h-auto aspect-square hover:border-[#632085] transition shrink-0"
                data-index="4">
                <img src="https://images.unsplash.com/photo-1471286174890-9c112ffca5b4?q=80&w=400" alt="Thumbnail 5"
                  class="w-full h-full object-cover" />
              </button>
            </div>

            <!-- Main Image Slider Box -->
            <div
              class="relative flex-1 bg-gray-50 border border-gray-100 rounded overflow-hidden order-1 md:order-2 aspect-square lg:aspect-[4/5]">

              <!-- Track containing images side-by-side -->
              <div id="image-track" class="flex h-full w-full transition-transform duration-500 ease-out"
                style="transform: translateX(0%);">
                <div class="w-full h-full shrink-0">
                  <img src="https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?q=80&w=800" alt="Slide 1"
                    class="w-full h-full object-cover" />
                </div>
                <div class="w-full h-full shrink-0">
                  <img src="https://images.unsplash.com/photo-1622290291165-d341f1938b86?q=80&w=800" alt="Slide 2"
                    class="w-full h-full object-cover" />
                </div>
                <div class="w-full h-full shrink-0">
                  <img src="https://images.unsplash.com/photo-1503919545889-aef636e10ad4?q=80&w=800" alt="Slide 3"
                    class="w-full h-full object-cover" />
                </div>
                <div class="w-full h-full shrink-0">
                  <img src="https://images.unsplash.com/photo-1515488042361-404e9250afef?q=80&w=800" alt="Slide 4"
                    class="w-full h-full object-cover" />
                </div>
                <div class="w-full h-full shrink-0">
                  <img src="https://images.unsplash.com/photo-1471286174890-9c112ffca5b4?q=80&w=800" alt="Slide 5"
                    class="w-full h-full object-cover" />
                </div>
              </div>

              <!-- Wishlist / Heart Icon -->
              <button
                class="absolute top-3 left-3 md:top-4 md:left-4 bg-white p-2 md:p-2.5 rounded-full shadow-md hover:bg-gray-100 transition text-[#632085] z-10">
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </button>
            </div>
          </div>

          <!-- ════════════════════════════════════════
             RIGHT COLUMN (Product Information)
            ════════════════════════════════════════ -->
          <div class="lg:col-span-5 flex flex-col justify-between h-full">

            <div>
              <!-- Title Responsive sizes -->
              <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 leading-tight">
                Luxury Essentials for Growing Families
              </h1>

              <!-- Brand Name -->
              <p class="text-gray-500 mt-1 md:mt-2 text-lg md:text-xl lg:text-2xl font-medium pt-2 md:pt-4">
                Brand Name: <span class="text-[#632085] font-semibold">Ponds</span>
              </p>

              <!-- Price -->
              <div class="flex items-baseline gap-2 md:gap-3 pt-3 md:pt-4">
                <span class="text-2xl md:text-3xl font-bold text-[#632085]">BDT 2500</span>
                <span class="text-lg md:text-xl text-gray-400 line-through">BDT 3000</span>
              </div>

              <!-- Separator Line -->
              <hr class="my-4 md:my-6 border-gray-200" />

              <!-- Features List (Responsive Texts) -->
              <ul class="space-y-3 md:space-y-4">
                <li class="flex items-start md:items-center gap-3 text-sm md:text-base text-gray-700 leading-relaxed">
                  <span class="w-2.5 h-2.5 md:w-3 md:h-3 rounded-full bg-[#632085] shrink-0 mt-1.5 md:mt-0"></span>
                  <span><strong>Large Capacity Storage:</strong> Discover Unparalleled Convenience And Comfort</span>
                </li>
                <li class="flex items-start md:items-center gap-3 text-sm md:text-base text-gray-700 leading-relaxed">
                  <span class="w-2.5 h-2.5 md:w-3 md:h-3 rounded-full bg-[#632085] shrink-0 mt-1.5 md:mt-0"></span>
                  <span><strong>Bottle And Accessory Pockets:</strong> Discover Unparalleled Convenience And Comfort
                    With Out</span>
                </li>
                <li class="flex items-start md:items-center gap-3 text-sm md:text-base text-gray-700 leading-relaxed">
                  <span class="w-2.5 h-2.5 md:w-3 md:h-3 rounded-full bg-[#632085] shrink-0 mt-1.5 md:mt-0"></span>
                  <span><strong>Comfortable And Ergonomic:</strong> Discover Unparalleled Convenience And Comfort</span>
                </li>
                <li class="flex items-start md:items-center gap-3 text-sm md:text-base text-gray-700 leading-relaxed">
                  <span class="w-2.5 h-2.5 md:w-3 md:h-3 rounded-full bg-[#632085] shrink-0 mt-1.5 md:mt-0"></span>
                  <span><strong>Extended Support Belt:</strong> Bangladesh-Based Retail And Online Store Specializing In
                    Baby</span>
                </li>
                <li class="flex items-start md:items-center gap-3 text-sm md:text-base text-gray-700 leading-relaxed">
                  <span class="w-2.5 h-2.5 md:w-3 md:h-3 rounded-full bg-[#632085] shrink-0 mt-1.5 md:mt-0"></span>
                  <span><strong>Large Capacity Storage:</strong> Ensures Both Parent And Baby Enjoy A Snug And</span>
                </li>
                <li class="flex items-start md:items-center gap-3 text-sm md:text-base text-gray-700 leading-relaxed">
                  <span class="w-2.5 h-2.5 md:w-3 md:h-3 rounded-full bg-[#632085] shrink-0 mt-1.5 md:mt-0"></span>
                  <span><strong>Comfortable And Ergonomic:</strong> Discover Unparalleled Convenience And Comfort</span>
                </li>
              </ul>

              <hr class="my-4 md:my-6 border-gray-200" />
            </div>

            <!-- Lower Part (Quantity & Action Buttons) -->
            <div>
              <!-- Action Buttons Container -->
              <div class="flex flex-col xl:flex-row items-start xl:items-center gap-4 md:gap-6">

                <!-- Quantity Selector Section -->
                <div class="flex items-center gap-3 md:gap-4">
                  <span class="text-[#0f172a] font-bold text-base md:text-lg select-none">Quantity:</span>
                  <div class="flex items-center gap-3 md:gap-4">
                    <!-- Minus Button -->
                    <button id="decrease-qty"
                      class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center border border-slate-400 rounded-lg hover:bg-gray-50 transition text-gray-700 text-xl md:text-2xl font-normal focus:outline-none">
                      -
                    </button>
                    <!-- Value Display -->
                    <span id="qty-value" class="w-4 md:w-6 text-center text-base md:text-lg font-bold text-[#0f172a] select-none">1</span>
                    <!-- Plus Button -->
                    <button id="increase-qty"
                      class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center border border-[#632085] rounded-lg hover:bg-purple-50 transition text-green-500 text-xl md:text-2xl font-bold focus:outline-none">
                      +
                    </button>
                  </div>
                </div>

                <!-- Actions Buttons Section -->
                <!-- Removed min-w-[320px] and added w-full for small screens to prevent overflow -->
                <div class="flex-1 flex flex-col sm:flex-row gap-3 md:gap-4 w-full">
                  <!-- Add to Cart -->
                  <button
                    class="flex-1 flex items-center justify-center gap-2 md:gap-3 border border-gray-200 text-[#0f172a] font-bold py-2.5 md:py-3 rounded-2xl hover:bg-gray-50 transition text-sm md:text-base shadow-sm focus:outline-none">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-[#334155]" fill="none" stroke="currentColor" stroke-width="2"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Add to Cart
                  </button>

                  <!-- Buy Now -->
                  <button
                    class="flex-1 flex items-center justify-center gap-2 md:gap-3 bg-[#632085] hover:bg-[#52166d] text-white font-bold py-2.5 md:py-3 rounded-2xl transition text-sm md:text-base shadow-sm focus:outline-none">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-white" fill="none" stroke="currentColor" stroke-width="2"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0zm3-10l2 2 4-4" />
                    </svg>
                    Buy Now
                  </button>
                </div>
              </div>

              <!-- Categories -->
              <p class="text-sm md:text-base text-gray-500 mt-5 md:mt-6 leading-relaxed">
                <span class="font-bold text-gray-700 text-xs md:text-sm">Categories:</span> Cereals, Muesli &amp; Oats, Baby Food,
                Organic Cereals, Single GrainCereals
              </p>

              <!-- Help / Contact Block -->
              <div class="mt-6 md:mt-8 border-t border-gray-100 pt-5 md:pt-6">
                <p class="font-bold text-gray-800 text-sm md:text-base">Have a question or need help placing your order?</p>
                <p class="text-gray-500 text-xs md:text-sm mt-1">We're just a message away — reach out anytime.</p>

                <div class="flex flex-wrap gap-4 md:gap-6 mt-4">
                  <a href="tel:#"
                    class="flex items-center gap-2 text-sm md:text-base font-bold text-gray-800 hover:text-[#632085] transition">
                    <svg width="20" height="20" class="md:w-6 md:h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M4.80527 0.765865L8.89415 4.85547C9.08975 5.05059 9.11207 5.36042 8.94599 5.58171C8.27975 6.47018 7.20119 7.90827 6.61391 8.69931C6.4265 8.9497 6.3136 9.24793 6.28821 9.55966C6.26282 9.87139 6.32597 10.184 6.47039 10.4614L6.48263 10.4837C6.94823 11.2882 7.92263 12.7733 9.57623 14.4269C11.2286 16.0795 12.7126 17.053 13.5146 17.5236C13.5223 17.5287 13.5305 17.5332 13.5394 17.5375C13.8181 17.6824 14.132 17.7456 14.4451 17.7197C14.7581 17.6939 15.0575 17.5801 15.3086 17.3914L18.4214 15.0571C18.5284 14.9768 18.6608 14.9378 18.7942 14.9473C18.9276 14.9567 19.0532 15.0141 19.1477 15.1087L23.2373 19.1979C23.3413 19.3022 23.3997 19.4434 23.3997 19.5907C23.3997 19.738 23.3413 19.8793 23.2373 19.9836C22.4088 20.8121 21.114 22.1064 20.304 22.9085C20.1267 23.0863 19.9113 23.2217 19.6742 23.3044C19.437 23.387 19.1842 23.4149 18.9348 23.3859C16.8118 23.0873 11.9225 21.87 7.02791 16.9755C2.13335 12.0809 0.915593 7.19114 0.611753 5.07291C0.581897 4.82241 0.609282 4.5684 0.691845 4.33002C0.774409 4.09164 0.909997 3.87511 1.08839 3.69675L4.01951 0.765625C4.12377 0.661521 4.26506 0.603004 4.41239 0.602905C4.55951 0.602905 4.70135 0.661945 4.80527 0.765865ZM12.1906 2.36835C17.4029 2.36835 21.6346 6.60002 21.6346 11.8123C21.6346 11.9597 21.6931 12.101 21.7972 12.2051C21.9014 12.3093 22.0427 12.3678 22.19 12.3678C22.3374 12.3678 22.4786 12.3093 22.5828 12.2051C22.687 12.101 22.7455 11.9597 22.7455 11.8123C22.7455 5.98682 18.0161 1.25739 12.1903 1.25739C12.043 1.25739 11.9017 1.31591 11.7975 1.42008C11.6934 1.52425 11.6348 1.66554 11.6348 1.81287C11.6348 1.96019 11.6934 2.10148 11.7975 2.20565C11.9017 2.30982 12.0432 2.36835 12.1906 2.36835Z"
                          fill="#111827" />
                        <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M12.1915 5.69957C15.5647 5.69957 18.3036 8.43821 18.3036 11.8117C18.3078 11.9562 18.3682 12.0934 18.4719 12.1941C18.5757 12.2948 18.7146 12.3512 18.8592 12.3512C19.0038 12.3512 19.1427 12.2948 19.2464 12.1941C19.3501 12.0934 19.4105 11.9562 19.4148 11.8117C19.4148 7.82477 16.1781 4.58813 12.1915 4.58813C12.047 4.59238 11.9098 4.65278 11.809 4.75651C11.7083 4.86025 11.652 4.99914 11.652 5.14373C11.652 5.28832 11.7083 5.42722 11.809 5.53096C11.9098 5.63469 12.047 5.69533 12.1915 5.69957Z"
                          fill="#111827" />
                        <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M12.1915 9.03344C13.7251 9.03344 14.9697 10.2781 14.9697 11.8117C14.974 11.9562 15.0344 12.0934 15.1381 12.1941C15.2418 12.2948 15.3807 12.3512 15.5253 12.3512C15.6699 12.3512 15.8088 12.2948 15.9125 12.1941C16.0163 12.0934 16.0767 11.9562 16.0809 11.8117C16.0809 9.66512 14.3378 7.92224 12.1915 7.92224C12.047 7.92649 11.9098 7.98689 11.809 8.09062C11.7083 8.19435 11.652 8.33325 11.652 8.47784C11.652 8.62243 11.7083 8.76133 11.809 8.86506C11.9098 8.96879 12.047 9.02919 12.1915 9.03344Z"
                          fill="#111827" />
                    </svg>
                    Call Us
                  </a>
                  <a href="https://wa.me/#" target="_blank"
                    class="flex items-center gap-2 text-sm md:text-base font-bold hover:text-green-700 transition">
                    <svg width="20" height="20" class="md:w-6 md:h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M14.865 13.403C14.4108 13.5886 14.1206 14.2997 13.8263 14.663C13.6753 14.8491 13.4953 14.8781 13.2633 14.7848C11.5584 14.1056 10.2516 12.968 9.31079 11.3991C9.15142 11.1558 9.18001 10.9636 9.3722 10.7377C9.65626 10.403 10.0134 10.0228 10.0903 9.57187C10.2609 8.57438 8.95688 5.48016 7.2347 6.88219C2.27907 10.9205 15.5016 21.6309 17.888 15.8381C18.563 14.1961 15.6178 13.0945 14.865 13.403ZM12 21.9037C10.2474 21.9037 8.52282 21.4378 7.01298 20.5556C6.77063 20.4136 6.47767 20.3761 6.20673 20.4497L2.92595 21.3502L4.06876 18.8325C4.1452 18.6644 4.17583 18.4791 4.15755 18.2954C4.13928 18.1116 4.07274 17.936 3.9647 17.7863C2.7422 16.0917 2.09579 14.0911 2.09579 12C2.09579 6.53859 6.5386 2.09578 12 2.09578C17.4614 2.09578 21.9038 6.53859 21.9038 12C21.9038 17.4609 17.4609 21.9037 12 21.9037ZM12 0C5.38313 0 9.41139e-06 5.38312 9.41139e-06 12C9.41139e-06 14.3278 0.660947 16.5633 1.91673 18.5034L0.0937594 22.5183C0.0113841 22.6996 -0.0176385 22.9007 0.0100879 23.0979C0.0378142 23.2952 0.121143 23.4804 0.250322 23.632C0.348844 23.7473 0.471174 23.8399 0.608897 23.9035C0.74662 23.967 0.896468 23.9999 1.04813 24C1.72407 24 5.40985 22.8417 6.34782 22.5844C8.08173 23.512 10.0266 24 12 24C18.6164 24 24 18.6164 24 12C24 5.38312 18.6164 0 12 0Z"
                        fill="#39AE41" />
                    </svg>
                    <span class="text-gray-800">Chat on Whatsapp</span>
                  </a>
                </div>
              </div>

              <!-- Social Share -->
              <div class="flex items-center gap-3 md:gap-4 mt-5 md:mt-6">
                <span class="text-sm md:text-base text-gray-500 font-bold">Share this Product:</span>
                <div class="flex gap-3 text-gray-400">
                  <!-- Facebook -->
                  <a href="#" class="hover:text-[#632085] transition">
                    <svg width="10" height="18" class="md:w-[9px] md:h-[16px]" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M5.39325 16V8.70218H7.84184L8.20921 5.85725H5.39325V4.04118C5.39325 3.21776 5.62097 2.65661 6.80309 2.65661L8.30832 2.65599V0.111384C8.04801 0.0775563 7.15446 0 6.11447 0C3.94279 0 2.45602 1.32557 2.45602 3.75942V5.85725H0V8.70218H2.45602V16H5.39325Z"
                        fill="#505D6C" />
                    </svg>
                  </a>
                  <!-- Instagram -->
                  <a href="#" class="hover:text-[#632085] transition">
                    <svg width="18" height="18" class="md:w-4 md:h-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M12 0H4C1.792 0 0 1.792 0 4V12C0 14.208 1.792 16 4 16H12C14.208 16 16 14.208 16 12V4C16 1.792 14.208 0 12 0ZM8 12C5.792 12 4 10.208 4 8C4 5.792 5.792 4 8 4C10.208 4 12 5.792 12 8C12 10.208 10.208 12 8 12ZM12.28 4.496C11.84 4.496 11.48 4.136 11.48 3.696C11.48 3.256 11.84 2.896 12.28 2.896C12.72 2.896 13.08 3.256 13.08 3.696C13.08 4.136 12.72 4.496 12.28 4.496Z"
                        fill="#505D6C" />
                      <path
                        d="M7.99998 10.4C9.32546 10.4 10.4 9.32546 10.4 7.99998C10.4 6.67449 9.32546 5.59998 7.99998 5.59998C6.67449 5.59998 5.59998 6.67449 5.59998 7.99998C5.59998 9.32546 6.67449 10.4 7.99998 10.4Z"
                        fill="#505D6C" />
                    </svg>
                  </a>
                  <!-- LinkedIn 1 -->
                  <a href="#" class="hover:text-[#632085] transition">
                    <svg width="18" height="18" class="md:w-4 md:h-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M16 16V10.14C16 7.26 15.38 5.06 12.02 5.06C10.4 5.06 9.32 5.94 8.88 6.78H8.84V5.32H5.66V16H8.98V10.7C8.98 9.3 9.24 7.96 10.96 7.96C12.66 7.96 12.68 9.54 12.68 10.78V15.98H16V16ZM0.26 5.32H3.58V16H0.26V5.32ZM1.92 0C0.86 0 0 0.86 0 1.92C0 2.98 0.86 3.86 1.92 3.86C2.98 3.86 3.84 2.98 3.84 1.92C3.84 0.86 2.98 0 1.92 0Z"
                        fill="#505D6C" />
                    </svg>
                  </a>
                  <!-- LinkedIn 2 -->
                  <a href="#" class="hover:text-[#632085] transition">
                    <svg width="18" height="18" class="md:w-4 md:h-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M16 3.039C15.405 3.3 14.771 3.473 14.11 3.557C14.79 3.151 15.309 2.513 15.553 1.744C14.919 2.122 14.219 2.389 13.473 2.538C12.871 1.897 12.013 1.5 11.077 1.5C9.261 1.5 7.799 2.974 7.799 4.781C7.799 5.041 7.821 5.291 7.875 5.529C5.148 5.396 2.735 4.089 1.114 2.098C0.831 2.589 0.665 3.151 0.665 3.756C0.665 4.892 1.25 5.899 2.122 6.482C1.595 6.472 1.078 6.319 0.64 6.078V6.114C0.64 7.708 1.777 9.032 3.268 9.337C3.001 9.41 2.71 9.445 2.408 9.445C2.198 9.445 1.986 9.433 1.787 9.389C2.212 10.688 3.418 11.643 4.852 11.674C3.736 12.547 2.319 13.073 0.785 13.073C0.516 13.073 0.258 13.061 0 13.028C1.453 13.965 3.175 14.5 5.032 14.5C11.068 14.5 14.368 9.5 14.368 5.166C14.368 5.021 14.363 4.881 14.356 4.742C15.007 4.28 15.554 3.703 16 3.039Z"
                        fill="#505D6C" />
                    </svg>
                  </a>
                </div>
              </div>

              <!-- Delivery Information -->
              <div class="mt-5 md:mt-6 text-sm md:text-base text-gray-600 space-y-1">
                <p><strong>Delivery Time:</strong> Inside Dhaka 24-48 Hours &amp; Outside Dhaka 48-96 Hours</p>
                <p><strong>Delivery Charge:</strong> Inside Dhaka-60TK &amp; Outside Dhaka-120TK</p>
              </div>

            </div>

          </div>
          <!-- end right column -->

        </div>
      </div>
    </section>

    <!-- ─── BOTTOM SECTION: ALL DETAILS SECTION (Scrollable) ─── -->
    <section id="product-listing-page" class="container mx-auto px-4 py-4" style="font-family: 'Manrope', sans-serif;">
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 md:gap-8 items-start">

        <!-- LEFT COLUMN: All Data Stacked Vertically with Sticky Navigation Bar -->
        <div class="lg:col-span-3 flex flex-col gap-6 md:gap-8">

          <!-- Sticky Navigation Bar with Horizontal scroll on small devices -->
          <div class="sticky top-0 z-30 py-2 md:py-4 bg-[#F9F9F9] md:bg-transparent">
            <div class="flex gap-2 sm:gap-3 overflow-x-auto no-scrollbar pb-2 md:pb-0" role="tablist">
              <button
                class="tab-btn flex-1 bg-white text-[#632085] font-bold text-sm sm:text-base md:text-lg py-2.5 md:py-3 px-3 md:px-4 text-center rounded-lg transition whitespace-nowrap shadow-sm active-tab"
                data-tab-target="section-description">
                Description
              </button>
              <button
                class="tab-btn flex-1 bg-white hover:bg-gray-200 text-[#632085] font-bold text-sm sm:text-base md:text-lg py-2.5 md:py-3 px-3 md:px-4 text-center rounded-lg transition whitespace-nowrap shadow-sm"
                data-tab-target="section-features">
                Features
              </button>
              <button
                class="tab-btn flex-1 bg-white hover:bg-gray-200 text-[#632085] font-bold text-sm sm:text-base md:text-lg py-2.5 md:py-3 px-3 md:px-4 text-center rounded-lg transition whitespace-nowrap shadow-sm"
                data-tab-target="section-specifications">
                Specifications
              </button>
            </div>
          </div>

          <!-- Content Area: All Sections Visible Simultaneously -->
          <div class="flex flex-col gap-6 md:gap-8">

            <!-- 1. Description Content -->
            <div id="section-description" class="bg-white p-4 sm:p-5 md:p-6 border border-gray-100 rounded shadow-sm scroll-mt-24">
              <h2 class="text-lg md:text-xl font-bold text-gray-900 border-l-4 border-[#632085] pl-3 mb-4">
                Product Description
              </h2>
              <div class="text-sm md:text-base text-gray-700 leading-relaxed space-y-3 md:space-y-4">
                <p>
                  This beautiful dress is designed for growing families looking for luxury, comfort, and unmatched
                  style. Carefully crafted with high-quality, lightweight fabric to ensure that your child is comfy all
                  day long.
                </p>
                <p>
                  Ideal for parties, outdoor activities, or any luxury family event. Includes comfortable inner lining
                  to prevent skin irritation.
                </p>
              </div>
            </div>

            <!-- 2. Features Content -->
            <div id="section-features" class="bg-white p-4 sm:p-5 md:p-6 border border-gray-100 rounded shadow-sm scroll-mt-24">
              <h2 class="text-lg md:text-xl font-bold text-gray-900 border-l-4 border-[#632085] pl-3 mb-4">
                Product Features
              </h2>
              <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4 text-sm md:text-base text-gray-700">
                <li class="flex items-center gap-2">
                  <svg class="w-4 h-4 md:w-5 md:h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  Skin-friendly Inner Lining
                </li>
                <li class="flex items-center gap-2">
                  <svg class="w-4 h-4 md:w-5 md:h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  Stylish High-Low One-Shoulder Cut
                </li>
                <li class="flex items-center gap-2">
                  <svg class="w-4 h-4 md:w-5 md:h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  Sparkly Lightweight Dress Material
                </li>
                <li class="flex items-center gap-2">
                  <svg class="w-4 h-4 md:w-5 md:h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  Comfortable for Multi-hour Wear
                </li>
                <!-- Repeating Features for Demo -->
                <li class="flex items-center gap-2">
                  <svg class="w-4 h-4 md:w-5 md:h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  Skin-friendly Inner Lining
                </li>
                <li class="flex items-center gap-2">
                  <svg class="w-4 h-4 md:w-5 md:h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  Stylish High-Low One-Shoulder Cut
                </li>
                <li class="flex items-center gap-2">
                  <svg class="w-4 h-4 md:w-5 md:h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  Sparkly Lightweight Dress Material
                </li>
                <li class="flex items-center gap-2">
                  <svg class="w-4 h-4 md:w-5 md:h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  Comfortable for Multi-hour Wear
                </li>
              </ul>
            </div>

            <!-- 3. Specifications Content -->
            <div id="section-specifications" class="bg-white p-4 sm:p-5 md:p-6 border border-gray-100 rounded shadow-sm scroll-mt-24">
              <h2 class="text-lg md:text-xl font-bold text-gray-900 border-l-4 border-[#632085] pl-3 mb-4">
                Product Specifications
              </h2>
              <div class="text-sm md:text-base text-gray-700 space-y-2 md:space-y-3">
                <p><strong>Material:</strong> 100% Organic Luxury Cotton &amp; Tulle Blend</p>
                <p><strong>Occasion:</strong> Festival, Party, Festive Wear</p>
                <p><strong>Color:</strong> Lavender Shimmer Pink</p>
                <p><strong>Care Instructions:</strong> Dry Clean Only</p>
                <p><strong>Material:</strong> 100% Organic Luxury Cotton &amp; Tulle Blend</p>
                <p><strong>Occasion:</strong> Festival, Party, Festive Wear</p>
                <p><strong>Color:</strong> Lavender Shimmer Pink</p>
                <p><strong>Care Instructions:</strong> Dry Clean Only</p>
              </div>
            </div>

          </div>

        </div>

        <!-- RIGHT COLUMN: Related & Recently Viewed Products -->
        <aside class="lg:col-span-2 flex flex-col gap-6 sticky top-24">

          <!-- Related Products -->
          <div class="bg-white border border-gray-100 rounded-lg overflow-hidden shadow-sm">
            <div class="px-4 py-3 md:px-6 md:py-4 bg-gray-50 border-b border-gray-200">
              <h3 class="font-bold text-gray-900 text-lg md:text-xl lg:text-2xl text-center">
                Related Products
              </h3>
            </div>

            <div class="divide-y divide-gray-200">
              <!-- Product Loop -->
              <div class="p-3 md:p-4 flex items-center gap-3 md:gap-4 hover:bg-gray-50 transition cursor-pointer">
                <img src="https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?q=80&w=200" alt="Related"
                  class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-lg flex-shrink-0" />
                <div class="min-w-0 flex-1">
                  <p class="text-sm md:text-base font-semibold text-gray-900 truncate">Luxury Essentials for Growing</p>
                  <p class="text-xs md:text-sm text-gray-600 mt-0.5 md:mt-1 truncate">Feeding Item baby and mom</p>
                  <p class="text-xs md:text-sm font-bold text-gray-900 mt-1.5 md:mt-2 bg-gray-100 inline-block px-2 md:px-3 py-0.5 md:py-1 rounded">BDT 3,600,000</p>
                </div>
                <button class="text-[#632085] hover:bg-purple-50 p-1.5 md:p-2 rounded-lg transition flex-shrink-0">
                  <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </button>
              </div>

              <!-- Product 2 -->
              <div class="p-3 md:p-4 flex items-center gap-3 md:gap-4 hover:bg-gray-50 transition cursor-pointer">
                <img src="https://images.unsplash.com/photo-1622290291165-d341f1938b86?q=80&w=200" alt="Related"
                  class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-lg flex-shrink-0" />
                <div class="min-w-0 flex-1">
                  <p class="text-sm md:text-base font-semibold text-gray-900 truncate">Luxury Essentials for Growing</p>
                  <p class="text-xs md:text-sm text-gray-600 mt-0.5 md:mt-1 truncate">Feeding Item baby and mom</p>
                  <p class="text-xs md:text-sm font-bold text-gray-900 mt-1.5 md:mt-2 bg-gray-100 inline-block px-2 md:px-3 py-0.5 md:py-1 rounded">BDT 3,600,000</p>
                </div>
                <button class="text-[#632085] hover:bg-purple-50 p-1.5 md:p-2 rounded-lg transition flex-shrink-0">
                  <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </button>
              </div>

              <!-- Product 3 -->
              <div class="p-3 md:p-4 flex items-center gap-3 md:gap-4 hover:bg-gray-50 transition cursor-pointer">
                <img src="https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?q=80&w=200" alt="Related"
                  class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-lg flex-shrink-0" />
                <div class="min-w-0 flex-1">
                  <p class="text-sm md:text-base font-semibold text-gray-900 truncate">Luxury Essentials for Growing</p>
                  <p class="text-xs md:text-sm text-gray-600 mt-0.5 md:mt-1 truncate">Feeding Item baby and mom</p>
                  <p class="text-xs md:text-sm font-bold text-gray-900 mt-1.5 md:mt-2 bg-gray-100 inline-block px-2 md:px-3 py-0.5 md:py-1 rounded">BDT 3,600,000</p>
                </div>
                <button class="text-[#632085] hover:bg-purple-50 p-1.5 md:p-2 rounded-lg transition flex-shrink-0">
                  <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Recently Viewed Products -->
          <div class="bg-white border border-gray-100 rounded-lg overflow-hidden shadow-sm">
            <div class="px-4 py-3 md:px-6 md:py-4 bg-gray-50 border-b border-gray-200">
              <h3 class="font-bold text-gray-900 text-lg md:text-xl lg:text-2xl text-center">
                Recently Viewed
              </h3>
            </div>

            <div class="divide-y divide-gray-200">
              <!-- Product 1 -->
              <div class="p-3 md:p-4 flex items-center gap-3 md:gap-4 hover:bg-gray-50 transition cursor-pointer">
                <img src="https://images.unsplash.com/photo-1503919545889-aef636e10ad4?q=80&w=200" alt="Recently Viewed"
                  class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-lg flex-shrink-0" />
                <div class="min-w-0 flex-1">
                  <p class="text-sm md:text-base font-semibold text-gray-900 truncate">Soft Cotton Baby Overall Set</p>
                  <p class="text-xs md:text-sm text-gray-600 mt-0.5 md:mt-1 truncate">Brand: Gentle Care</p>
                  <p class="text-xs md:text-sm font-bold text-gray-900 mt-1.5 md:mt-2 bg-gray-100 inline-block px-2 md:px-3 py-0.5 md:py-1 rounded">BDT 1500
                  </p>
                </div>
                <button class="text-[#632085] hover:bg-purple-50 p-1.5 md:p-2 rounded-lg transition flex-shrink-0">
                  <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </button>
              </div>

              <!-- Product 2 -->
              <div class="p-3 md:p-4 flex items-center gap-3 md:gap-4 hover:bg-gray-50 transition cursor-pointer">
                <img src="https://images.unsplash.com/photo-1515488042361-404e9250afef?q=80&w=200" alt="Recently Viewed"
                  class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-lg flex-shrink-0" />
                <div class="min-w-0 flex-1">
                  <p class="text-sm md:text-base font-semibold text-gray-900 truncate">Interactive Wooden Toddler Toy</p>
                  <p class="text-xs md:text-sm text-gray-600 mt-0.5 md:mt-1 truncate">Brand: PlayWood</p>
                  <p class="text-xs md:text-sm font-bold text-gray-900 mt-1.5 md:mt-2 bg-gray-100 inline-block px-2 md:px-3 py-0.5 md:py-1 rounded">BDT 950</p>
                </div>
                <button class="text-[#632085] hover:bg-purple-50 p-1.5 md:p-2 rounded-lg transition flex-shrink-0">
                  <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

        </aside>

      </div>
    </section>
@endsection


