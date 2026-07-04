@extends('saas.layouts.layout')

@php
    $pageData = \App\Services\Saas\SystemPageService::get(
        \App\Enums\SystemPageType::CONTACT_US,
        $setup->company_id ?? null
    );
@endphp

@include('components.meta-info.saas-meta', [
    'setup' => $setup,
    'type' => 'ContactPage',

    'title' => $pageData?->meta_title ?? ('Contact Us | ' . $setup->shop_name),

    'description' => $pageData?->meta_description ?? ('Contact ' . $setup->shop_name . ' for sales, support, product demos, or any business inquiries. We are here to help you grow your business.'),

    'keywords' => $pageData?->meta_keywords
        ? implode(',', $pageData->meta_keywords)
        : 'contact, support, sales, customer service, business software',

    'image' => $setup->meta_image
        ? asset('storage/' . $setup->meta_image)
        : asset('storage/' . $setup->logo),

    'canonical' => route('saas.contact'),

    'breadcrumb' => [
        [
            'name' => 'Home',
            'url' => url('/'),
        ],
        [
            'name' => 'Contact',
            'url' => route('saas.contact'),
        ],
    ],
])
@section('content')
<!-- CONTACT US SECTION WITH DOT GRID BACKGROUND -->
<section class="relative py-24 px-6 md:px-10 font-manrope overflow-hidden"
  style="background-color: #ffffff; background-image: radial-gradient(#e5e7eb 1.5px, transparent 1px); background-size: 30px 30px;">

  <div class="container mx-auto max-w-6xl relative z-10">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center mb-24">

      <!-- Left Side: Minimalist Form -->
      <div class="bg-white/80 backdrop-blur-sm p-2 rounded-xl">
        <div class="mb-12">
          <h2 class="text-4xl font-extrabold text-gray-800 mb-3 tracking-tight">Contact us</h2>
          <div class="w-14 h-1.5 bg-[#34a487] rounded-full mb-6"></div>
          <p class="text-gray-500 text-lg font-medium">Reach out to us for any inquiry</p>
        </div>

        {{-- সাকসেস মেসেজ --}}
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 shadow-sm font-bold">
          {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('saas.contact.send') }}" method="POST" class="space-y-8">
          @csrf

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Name --}}
            <div class="relative">
              <input type="text" name="name" value="{{ old('name') }}" placeholder="Full name *" required
                class="w-full border-b border-gray-200 py-3 bg-transparent focus:border-[#34a487] focus:outline-none transition-all duration-300 placeholder:text-gray-400 text-lg @error('name') border-red-500 @enderror">
              @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Email --}}
            <div class="relative">
              <input type="email" name="email" value="{{ old('email') }}" placeholder="Your email *" required
                class="w-full border-b border-gray-200 py-3 bg-transparent focus:border-[#34a487] focus:outline-none transition-all duration-300 placeholder:text-gray-400 text-lg @error('email') border-red-500 @enderror">
              @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Phone (আপনার কন্ট্রোলারের রিকোয়ারমেন্ট অনুযায়ী যোগ করা হয়েছে) --}}
            <div class="relative">
              <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Phone Number *" required
                class="w-full border-b border-gray-200 py-3 bg-transparent focus:border-[#34a487] focus:outline-none transition-all duration-300 placeholder:text-gray-400 text-lg @error('phone') border-red-500 @enderror">
              @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Subject (আপনার কন্ট্রোলারের রিকোয়ারমেন্ট অনুযায়ী যোগ করা হয়েছে) --}}
            <div class="relative">
              <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Subject"
                class="w-full border-b border-gray-200 py-3 bg-transparent focus:border-[#34a487] focus:outline-none transition-all duration-300 placeholder:text-gray-400 text-lg">
            </div>
          </div>

          {{-- Message --}}
          <div class="relative">
            <textarea name="message" rows="3" placeholder="Message *" required
              class="w-full border-b border-gray-200 py-3 bg-transparent focus:border-[#34a487] focus:outline-none transition-all duration-300 placeholder:text-gray-400 text-lg resize-none @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
            @error('message') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
          </div>

          <button type="submit"
            class="w-full bg-[#34a487] text-white font-black py-5 rounded-md shadow-xl shadow-[#34a487]/20 hover:bg-[#2c8a71] hover:-translate-y-1 transition-all uppercase tracking-[0.2em] text-sm cursor-pointer">
            Submit
          </button>
        </form>
      </div>

      <!-- Right Side: Map with Dynamic URL -->
      <div class="relative flex justify-center">
        <div class="absolute -top-8 -right-8 w-48 h-72 bg-[#34a487] rounded-xl z-0 hidden md:block opacity-90 shadow-lg transition-transform hover:scale-105"></div>

        <!-- Map Container -->
        <div class="relative z-10 w-full bg-white rounded-xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.1)] aspect-square border-[10px] border-white">
          <iframe
            src="{{ $setup->url ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.9024424301385!2d90.3910801!3d23.7508666!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjPCsDQ1JzAzLjEiTiA5MMKwMjMnMjcuOSJF!5e0!3m2!1sen!2sbd!4v1625123456789!5m2!1sen!2sbd' }}"
            width="100%" height="100%" style="border:0; filter: grayscale(100%) contrast(1.2) opacity(0.8);" allowfullscreen="" loading="lazy">
          </iframe>
        </div>
      </div>

    </div>

    <!-- Bottom: Info Grid (Dynamic from $setup) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-12 pt-16 border-t border-gray-100">

      <!-- Location Item -->
      <div class="flex items-center gap-6 group">
        <div class="flex-shrink-0">
          <svg class="w-12 h-12 text-[#34a487] transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
        </div>
        <div class="space-y-1">
          <h4 class="font-bold text-gray-900 text-xl leading-none">Location:</h4>
          <p class="text-gray-500 text-[15px] leading-tight font-medium">
            {{ $setup->store_address ?? 'Address not set' }}
          </p>
        </div>
      </div>

      <!-- Email Item -->
      <div class="flex items-center gap-6 group">
        <div class="flex-shrink-0">
          <svg class="w-12 h-12 text-[#34a487] transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
        </div>
        <div class="space-y-1">
          <h4 class="font-bold text-gray-900 text-xl leading-none">Email:</h4>
          <p class="text-gray-500 text-[15px] leading-tight font-medium truncate">
            {{ $setup->email ?? 'info@yourdomain.com' }}
          </p>
        </div>
      </div>

      <!-- Phone Item -->
      <div class="flex items-center gap-6 group">
        <div class="flex-shrink-0">
          <svg class="w-12 h-12 text-[#34a487] transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 011.94.445l-.992 3.472a1 1 0 01-1.1.714l-2.008-.338a16.03 16.03 0 006.51 6.51l.338-2.008a1 1 0 01.714-1.1l3.472.992a1 1 0 01.445 1.94V19a2 2 0 01-2 2h-12a2 2 0 01-2-2V5z"></path>
          </svg>
        </div>
        <div class="space-y-1">
          <h4 class="font-bold text-gray-900 text-xl leading-none">Phone:</h4>
          <p class="text-gray-500 text-[15px] leading-tight font-medium">
            {{ $setup->phone ?? 'Phone not set' }}
          </p>
        </div>
      </div>

    </div>

  </div>
</section>
@endsection