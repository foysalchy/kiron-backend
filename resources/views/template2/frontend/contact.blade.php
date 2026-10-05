@extends('template2.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.contact-meta', ['setup' => $setup])
@endsection
@section('content')
    <!-- CONTACT HEADER SECTION -->
    <section class="container py-6 mx-auto font-['Outfit'] px-4">
        <div class="text-center">
            <!-- Main Heading -->
            <h1 class="text-2xl md:text-4xl font-black text-gray-900 mb-4">
                Contact Us
            </h1>
            <!-- Description -->
            <p class="text-md md:text-lg text-gray-500 max-w-3xl mx-auto leading-relaxed font-medium">
                Get in touch with us. We are available to serve you 24/7.
            </p>

        </div>
    </section>
    <!-- CONTACT CONTENT SECTION -->
    <section class="container py-6 mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-0 items-start">

            <div class="lg:col-span-2 bg-white rounded-lg shadow-xs p-6 ">
                <h2 class="text-lg md:text-2xl font-black text-gray-900 mb-8">Send Us a Message</h2>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('contact.send') }}" method="POST" class="space-y-2">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter Your Name.."
                                required
                                class="w-full h-10 px-3 rounded-lg focus:border-[#016738] outline-none border border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                placeholder="Enter Your Email.." required
                                class="w-full h-10 px-3 rounded-lg focus:border-[#016738] outline-none border border-gray-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Phone No *</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}"
                                placeholder="Enter Your Phone .." required
                                class="w-full h-10 px-3 rounded-lg focus:border-[#016738] outline-none border border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Subject</label>
                            <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Enter subject.."
                                class="w-full h-10 px-3 rounded-lg focus:border-[#016738] outline-none border border-gray-300">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Message *</label>
                        <textarea name="message" placeholder="Enter Your Message..." rows="5" required
                            class="w-full px-3 py-2 rounded-lg focus:border-[#016738] outline-none border border-gray-300">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit"
                        class="w-full primary-bg hover:bg-orange-600 text-primary font-black py-3 rounded-lg shadow-lg transition-all">
                        <i class="fas fa-paper-plane mr-2"></i> Send Message
                    </button>
                </form>
            </div>

            <div class="space-y-2">

                <!-- Contact Information -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow-xs p-6 ">
                    <h3 class="text-2xl font-black text-gray-900 mb-4">Contact Information</h3>

                    <div class="space-y-2">
                        <!-- Phone -->
                        <div class="flex items-start gap-2">
                            <div style="color:var(--primary-color)" class="w-10 h-10  rounded-xl flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-phone h-5 w-5 mt-1">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-900 font-semibold mb-1">Phone</p>

                                {{-- মেইন ফোন নম্বর --}}
                                <a href="tel:{{ str_replace(' ', '', $setup->phone) }}" aria-label="Call us"
                                    class="text-gray-600 hover:text-orange-500 transition-colors d-block mb-1">
                                    {{ $setup->phone }}
                                </a>



                                {{-- অল্টারনেটিভ ফোন নম্বর --}}
                                @if ($setup->alt_phone)
                                    <a href="tel:{{ str_replace(' ', '', $setup->alt_phone) }}" aria-label="Call alternative number"
                                        class="text-gray-600 hover:text-orange-500 transition-colors block">
                                        {{ $setup->alt_phone }}
                                    </a>
                                @endif
                            </div>
                        </div>
                         <div class="h-[1px] bg-gray-200"></div>

                        <!-- Email -->
                        <div class="flex items-start gap-2">
                            <div style="color:var(--primary-color)" class="w-10 h-10  rounded-xl flex items-center justify-center shrink-0">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-phone h-5 w-5 mt-1">
                                    <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path>
                                </svg>
                            </div>
                             @php
                                $whatsappNumber = preg_replace('/[^0-9]/', '', $setup->whatsapp);
                            @endphp

                            <div>
                                <p class="text-gray-900 font-semibold">WhatsApp</p>
                                <a href="https://wa.me/{{ $whatsappNumber }}" aria-label="Message us on WhatsApp" class="hover:text-orange-500 transition-colors">
                                    {{ $whatsappNumber }}
                                </a>
                            </div>
                        </div>

                        <div class="h-[1px] bg-gray-200"></div>

                        <!-- Email -->
                        <div class="flex items-start gap-2">
                            <div style="color:var(--primary-color)" class="w-10 h-10  rounded-xl flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-mail h-5 w-5 mt-1">
                                    <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-900 font-semibold">Email</p>
                                <a href="mailto:{{ $setup->email }}" aria-label="Send us an email" class="hover:text-orange-500 transition-colors">
                                    {{ $setup->email }}
                                </a>
                            </div>
                        </div>

                        <div class="h-[1px] bg-gray-200"></div>

                        <!-- Address -->
                        <div class="flex items-start gap-2">
                            <div style="color:var(--primary-color)" class="w-10 h-10  rounded-xl flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-map-pin h-5 w-5 mt-1">
                                    <path
                                        d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0">
                                    </path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div>
                                <p class=" text-gray-900 font-semibold">Address</p>
                                <p class="text-gray-700">{!! nl2br(e($setup->store_address)) ?? 'Address not found' !!}</p>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="lg:col-span-2 bg-white rounded-lg shadow-xs p-6 ">
                    <h3 class="text-lg md:text-2xl font-black text-gray-900 mb-8">Social Media</h3>

                    <div class="flex flex-wrap gap-3">
                        @foreach ($socialLinks as $social)
                            <a href="{{ $social->link }}" target="_blank"
                                class="flex items-center gap-3 rounded-md px-3 py-2 transition-all group"
                                onmouseover="this.style.borderColor='{{ $social->hover_bg ?? '#FF6A00' }}'; this.style.color='{{ $social->hover_bg ?? '#FF6A00' }}'"
                                onmouseout="this.style.borderColor='#e5e7eb'; this.style.color='inherit'">

                                @if ($social->icon_image)
                                    <img src="{{ asset('storage/' . $social- loading="lazy" width="800" height="800">icon_image) }}"
                                        alt="{{ $social->icon_name }}"
                                        class="h-5 w-5 object-contain group-hover:primary-bg transition-transform">
                                @else
                                    <i
                                        class="{{ $social->icon_class ?? 'fab fa-share' }} text-gray-700 group-hover:text-inherit transition-colors"></i>
                                @endif

                                <span class="text-sm text-gray-800 font-medium group-hover:text-inherit transition-colors">
                                    {{ $social->icon_name }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- WhatsApp Card -->


                <!-- Social Media -->


            </div>
        </div>
    </section>
    <!-- FAQ SECTION -->
     @if($faqs->count() > 0)
    <section class="container py-6 mx-auto pb-0 font-['Outfit']">
        <div class="bg-white rounded-lg shadow-xs">

            <!-- Title -->
            <div class="py-10 text-center">
                <h2 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight">Frequently Asked Questions</h2>
            </div>

            <!-- FAQ Items Container -->
            <div class="px-6 pb-12 space-y-4 mx-auto">

                @forelse($faqs as $faq)
                    <!-- Dynamic Item -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden transition-all bg-white">
                        <button onclick="toggleFAQ(this)"
                            class="w-full px-6 py-4 text-left flex items-center justify-between group hover:bg-gray-50 transition-colors">
                            <span class="text-md font-medium text-gray-800">{{ $faq->title ?? ''}}</span>
                            <i class="fas fa-chevron-down text-gray-400 text-sm transition-transform duration-300"></i>
                        </button>
                        <div class="max-h-0 overflow-hidden transition-all duration-300 ease-in-out bg-white">
                            <div class="px-6 pb-5 text-gray-600 text-md border-t border-gray-50 pt-3">
                                {!! $faq->content !!}
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500">No information found.</p>
                @endforelse

            </div>
        </div>
    </section>
    @endif
    <!-- OUR LOCATION SECTION (With Real Google Map) -->
    <section class="container py-6 mx-auto">
        <div class="bg-white rounded-lg shadow-xs p-6">

            <!-- Title -->
            <h3 class="text-2xl font-black text-gray-900 mb-8 flex items-center gap-3">
                Our location
            </h3>

            <!-- Map Container -->
            <div class="relative w-full rounded-lg overflow-hidden shadow-inner group">
                <iframe title="Google Maps Location"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.1075677025856!2d90.41018317589578!3d23.779185187720234!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755c70b22a019d3%3A0xe54331201990c74e!2sGulshan%202%2C%20Dhaka%201212!5e0!3m2!1sen!2sbd!4v1709456789012!5m2!1sen!2sbd"
                    class="w-full h-[400px] md:h-[500px] grayscale-[0.2] contrast-[1.1] transition-all group-hover:grayscale-0"
                    style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                </iframe>

                <div class="absolute bottom-4 right-4 z-10">
                    <a href="https://maps.app.goo.gl/9uT5Qx5Y8hX6q8yX9" target="_blank"
                        class="bg-white text-gray-800 px-6 py-3 rounded-xl font-bold text-sm shadow-xl flex items-center gap-2 hover:bg-[var(--primary-color)] hover:text-white transition-all">
                        <i class="fas fa-external-link-alt"></i>
                        View Zoom on Google Maps
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts')
    <script>
        function toggleFAQ(button) {
            const content = button.nextElementSibling;
            const icon = button.querySelector('i');

            if (content.style.maxHeight && content.style.maxHeight !== '0px') {
                content.style.maxHeight = '0px';
                icon.style.transform = 'rotate(0deg)';
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
                icon.style.transform = 'rotate(180deg)';
            }
        }
    </script>
@endpush
