@extends('template1.layouts.front')

@section('content')
    <!-- HERO SECTION -->
    <section class="py-6 container mx-auto ">
        <!-- Main 3-Column Layout -->
        <div class="flex flex-col lg:flex-row gap-4 items-stretch h-auto lg:h-[480px]">

            <!-- 1. LEFT SIDEBAR: Scrollable Categories (260px wide) -->
            <div class="hidden lg:block w-[260px] shrink-0">
                <div class="bg-white rounded-2xl shadow-sm h-full overflow-y-auto custom-scrollbar">
                    <div class="p-2 space-y-1">

                        <!-- Item: Summer Essential (No Dropdown) -->
                        <a href="#" class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-xl transition-all">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/aef3608c-8655-423f-8892-22d775c97be0.jpg"
                                class="w-8 h-8 rounded-full object-cover border border-gray-100" alt="icon">
                            <span class="text-[14px] font-bold text-gray-800">Summer Essential</span>
                        </a>

                        <!-- Item: Winter Essential (No Dropdown) -->
                        <a href="#" class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-xl transition-all">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/d2f0ea5c-af11-4d88-b853-637fec50072e.jpg"
                                class="w-8 h-8 rounded-full object-cover border border-gray-100" alt="icon">
                            <span class="text-[14px] font-bold text-gray-800">Winter Essential</span>
                        </a>

                        <!-- CATEGORY WITH DROPDOWN: Car Interior -->
                        <div class="category-item">
                            <button onclick="toggleDropdown('interior')"
                                class="w-full flex items-center justify-between p-3 hover:bg-gray-50 rounded-xl transition-all group">
                                <div class="flex items-center gap-3">
                                    <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/f0fea668-d4e9-43a8-b27c-7eecc85bd40e.jpg"
                                        class="w-8 h-8 rounded-full object-cover border border-gray-100" alt="icon">
                                    <span class="text-[14px] font-bold text-gray-800">Car Interior</span>
                                </div>
                                <i id="icon-interior"
                                    class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform duration-300"></i>
                            </button>
                            <div id="menu-interior" class="overflow-hidden transition-all duration-300 max-h-0">
                                <div class="flex flex-col pb-2">
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Net
                                        Holder</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Car
                                        Sun Shade</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Travel
                                        Essentials</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Back
                                        Support</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Car
                                        Brake Pedal Cover</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Car
                                        Steering Cover</a>
                                </div>
                            </div>
                        </div>
                        <!-- CATEGORY WITH DROPDOWN: Car Exterior -->
                        <div class="category-item">
                            <button onclick="toggleDropdown('exterior')"
                                class="w-full flex items-center justify-between p-3 hover:bg-gray-50 rounded-xl transition-all group">
                                <div class="flex items-center gap-3">
                                    <img src="./assets/images/car-exterior.webp"
                                        class="w-8 h-8 rounded-full object-cover border border-gray-100" alt="icon">
                                    <span class="text-[14px] font-bold text-gray-800">Car Exterior</span>
                                </div>
                                <i id="icon-exterior"
                                    class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform duration-300"></i>
                            </button>
                            <div id="menu-exterior" class="overflow-hidden transition-all duration-300 max-h-0">
                                <div class="flex flex-col pb-2">
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Wheel
                                        and Rim Decor</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Car
                                        Sticker</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Foot
                                        Steps</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Back
                                        Support</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Car
                                        Brake Pedal Cover</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Car
                                        Steering Cover</a>
                                </div>
                            </div>
                        </div>

                        <!-- CATEGORY WITH DROPDOWN: Electronics & Gadgets -->
                        <div class="category-item">
                            <button onclick="toggleDropdown('electronics')"
                                class="w-full flex items-center justify-between p-3 hover:bg-gray-50 rounded-xl transition-all group">
                                <div class="flex items-center gap-3">
                                    <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/a91edaed-b69b-41f5-9686-132051fd7ce2.jpg"
                                        class="w-8 h-8 rounded-full object-cover border border-gray-100" alt="icon">
                                    <span class="text-[14px] font-bold text-gray-800">Electronics & Gadgets</span>
                                </div>
                                <i id="icon-electronics"
                                    class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform duration-300"></i>
                            </button>
                            <div id="menu-electronics" class="overflow-hidden transition-all duration-300 max-h-0">
                                <div class="flex flex-col pb-2">
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Car
                                        DVR & Cameras</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Android
                                        & DVD Players</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Mobile
                                        Accessories</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Car
                                        Fan & Heater</a>
                                </div>
                            </div>
                        </div>
                        <!-- CATEGORY WITH DROPDOWN: Car Care -->
                        <div class="category-item">
                            <button onclick="toggleDropdown('carec')"
                                class="w-full flex items-center justify-between p-3 hover:bg-gray-50 rounded-xl transition-all group">
                                <div class="flex items-center gap-3">
                                    <img src="./assets/images/car-care.webp"
                                        class="w-8 h-8 rounded-full object-cover border border-gray-100" alt="icon">
                                    <span class="text-[14px] font-bold text-gray-800">Car Care</span>
                                </div>
                                <i id="icon-carec"
                                    class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform duration-300"></i>
                            </button>
                            <div id="menu-carec" class="overflow-hidden transition-all duration-300 max-h-0">
                                <div class="flex flex-col pb-2">
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Car
                                        DVR & Cameras</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Android
                                        & DVD Players</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Mobile
                                        Accessories</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Car
                                        Fan & Heater</a>
                                </div>
                            </div>
                        </div>
                        <!-- CATEGORY WITH DROPDOWN: Perfume & Showpiece -->
                        <div class="category-item">
                            <button onclick="toggleDropdown('perfume')"
                                class="w-full flex items-center justify-between p-3 hover:bg-gray-50 rounded-xl transition-all group">
                                <div class="flex items-center gap-3">
                                    <img src="./assets/images/perfume.webp"
                                        class="w-8 h-8 rounded-full object-cover border border-gray-100" alt="icon">
                                    <span class="text-[14px] font-bold text-gray-800">Perfume & Showpiece</span>
                                </div>
                                <i id="icon-perfume"
                                    class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform duration-300"></i>
                            </button>
                            <div id="menu-perfume" class="overflow-hidden transition-all duration-300 max-h-0">
                                <div class="flex flex-col pb-2">
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Car
                                        DVR & Cameras</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Android
                                        & DVD Players</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Mobile
                                        Accessories</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Car
                                        Fan & Heater</a>
                                </div>
                            </div>
                        </div>
                        <!-- CATEGORY WITH DROPDOWN: Key Accesories -->
                        <div class="category-item">
                            <button onclick="toggleDropdown('keyAccessories')"
                                class="w-full flex items-center justify-between p-3 hover:bg-gray-50 rounded-xl transition-all group">
                                <div class="flex items-center gap-3">
                                    <img src="./assets/images/perfume.webp"
                                        class="w-8 h-8 rounded-full object-cover border border-gray-100" alt="icon">
                                    <span class="text-[14px] font-bold text-gray-800">Key Accesories</span>
                                </div>
                                <i id="icon-keyAccessories"
                                    class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform duration-300"></i>
                            </button>
                            <div id="menu-keyAccessories" class="overflow-hidden transition-all duration-300 max-h-0">
                                <div class="flex flex-col pb-2">
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Car
                                        DVR & Cameras</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Android
                                        & DVD Players</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Mobile
                                        Accessories</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Car
                                        Fan & Heater</a>
                                </div>
                            </div>
                        </div>

                        <!-- CATEGORY WITH DROPDOWN: Car Spare Parts -->
                        <div class="category-item">
                            <button onclick="toggleDropdown('spare-parts')"
                                class="w-full flex items-center justify-between p-3 hover:bg-gray-50 rounded-xl transition-all group">
                                <div class="flex items-center gap-3">
                                    <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/f96f396e-e36a-4ee8-8736-86bf409267f3.jpg"
                                        class="w-8 h-8 rounded-full object-cover border border-gray-100" alt="icon">
                                    <span class="text-[14px] font-bold text-gray-800">Car Spare Parts</span>
                                </div>
                                <i id="icon-spare-parts"
                                    class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform duration-300"></i>
                            </button>
                            <div id="menu-spare-parts" class="overflow-hidden transition-all duration-300 max-h-0">
                                <div class="flex flex-col pb-2">
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Wiper
                                        Blades</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Engine
                                        Parts</a>
                                    <a href="#"
                                        class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">Brake
                                        Pads</a>
                                </div>
                            </div>
                        </div>

                        <!-- Item: Modification (No Dropdown Example) -->
                        <a href="#" class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-xl transition-all">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/3bcc0f12-c2b8-49ad-a7c1-8d048801d4c9.jpg"
                                class="w-8 h-8 rounded-full object-cover" alt="icon">
                            <span class="text-[14px] font-bold text-gray-800">Modifications</span>
                        </a>

                    </div>
                </div>
            </div>

            <!-- 2. CENTER: Main Horizontal Auto-Slider -->
            <div class="flex-1 min-w-0 h-[300px] md:h-[400px] lg:h-full">
                <div class="relative h-full w-full rounded-2xl overflow-hidden shadow-sm bg-white">
                    <div id="main-slider" class="flex transition-transform duration-700 ease-in-out h-full w-full">
                        <div class="min-w-full h-full"><img src="{{ asset('images/template1/frontend/hero1.jpg') }}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{asset('images/template1/frontend/hero2.jpg')}}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{asset('images/template1/frontend/hero3.jpg')}}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{asset('images/template1/frontend/hero4.jpg')}}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{asset('images/template1/frontend/hero-right65.jpg')}}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{asset('images/template1/frontend/hero1.jpg')}}"
                                class="w-full h-full object-cover"></div>
                    </div>

                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5">
                        <div class="main-dot w-6 h-1 rounded-full bg-[#FF6A00] transition-all"></div>
                        <div class="main-dot w-2 h-1 rounded-full bg-white/50 transition-all"></div>
                        <div class="main-dot w-2 h-1 rounded-full bg-white/50 transition-all"></div>
                        <div class="main-dot w-2 h-1 rounded-full bg-white/50 transition-all"></div>
                        <div class="main-dot w-2 h-1 rounded-full bg-white/50 transition-all"></div>
                        <div class="main-dot w-2 h-1 rounded-full bg-white/50 transition-all"></div>
                    </div>
                </div>
            </div>

            <!-- 3. RIGHT SIDEBAR: Vertical Banner Slider -->
            <div class="hidden lg:block w-[260px] shrink-0">
                <div class="relative h-full rounded-2xl overflow-hidden bg-white">
                    <!-- Vertical Slider Container (৫টি ইমেজ) -->
                    <div id="vertical-slider"
                        class="flex flex-col transition-transform duration-700 ease-in-out h-full w-full">
                        <!-- Banner 1 -->
                        <div class="min-h-full w-full">
                            <img src="{{asset('images/template1/frontend/hero-right1.jpg')}}" class="w-full h-full object-cover rounded-2xl">
                        </div>
                        <!-- Banner 2 -->
                        <div class="min-h-full w-full">
                            <img src="{{asset('images/template1/frontend/hero-right2.jpg')}}" class="w-full h-full object-cover rounded-2xl">
                        </div>
                        <!-- Banner 3 -->
                        <div class="min-h-full w-full">
                            <img src="{{asset('images/template1/frontend/hero-right3.jpg')}}" class="w-full h-full object-cover rounded-2xl">
                        </div>
                        <!-- Banner 4 -->
                        <div class="min-h-full w-full">
                            <img src="{{asset('images/template1/frontend/hero-right4.jpg')}}" class="w-full h-full object-cover rounded-2xl">
                        </div>
                        <!-- Banner 5 -->
                        <div class="min-h-full w-full">
                            <img src="{{asset('images/template1/frontend/hero-right65.jpg')}}" class="w-full h-full object-cover rounded-2xl">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- TOP CATEGORIES SECTION -->
    <section class="py-6 container mx-auto">
        <!-- Main Card Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative">

            <!-- Section Heading -->
            <h2 class="text-lg font-bold text-gray-900 uppercase tracking-tight mb-8 px-2">
                Top Categories
            </h2>

            <!-- Carousel Wrapper -->
            <div class="relative group">

                <!-- Navigation Buttons -->
                <button onclick="scrollCats(-200)"
                    class="absolute -left-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-md z-10 hover:bg-gray-50 transition-all">
                    <i class="fas fa-chevron-left text-xs text-gray-600"></i>
                </button>

                <button onclick="scrollCats(200)"
                    class="absolute -right-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-md z-10 hover:bg-gray-50 transition-all">
                    <i class="fas fa-chevron-right text-xs text-gray-600"></i>
                </button>

                <!-- Categories Scroll Area -->
                <div id="cat-slider" class="flex items-start gap-8 overflow-x-auto no-scrollbar scroll-smooth">

                    <!-- 1. Summer -->
                    <a href="#" class="flex flex-col items-center min-w-[105px] group">
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-3">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/aef3608c-8655-423f-8892-22d775c97be0.jpg"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <span class="text-[14px] font-semibold text-gray-800 text-center truncate w-full px-1">Summer
                            Essential</span>
                    </a>

                    <!-- 2. Winter -->
                    <a href="#" class="flex flex-col items-center min-w-[105px] group">
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-3">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/d2f0ea5c-af11-4d88-b853-637fec50072e.jpg"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <span class="text-[14px] font-semibold text-gray-800 text-center w-full ">Winter Essential</span>
                    </a>

                    <!-- 3. Car Interior -->
                    <a href="#" class="flex flex-col items-center min-w-[105px] group">
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-3">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/f0fea668-d4e9-43a8-b27c-7eecc85bd40e.jpg"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <span class="text-[14px] font-semibold text-gray-800 text-center w-full">Car Interior</span>
                    </a>

                    <!-- 4. Car Exterior -->
                    <a href="#" class="flex flex-col items-center min-w-[105px] group">
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-3">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/e33c1db7-9d56-4311-9e99-cf5819240831.jpg"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <span class="text-[14px] font-semibold text-gray-800 text-center w-full">Car Exterior</span>
                    </a>

                    <!-- 5. Electronics -->
                    <a href="#" class="flex flex-col items-center min-w-[105px] group">
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-3">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/a91edaed-b69b-41f5-9686-132051fd7ce2.jpg"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <span class="text-[14px] font-semibold text-gray-800 text-center truncate w-full px-1">Electronics
                            & Gadgets</span>
                    </a>

                    <!-- 6. Car Care -->
                    <a href="#" class="flex flex-col items-center min-w-[105px] group">
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-3">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/1c00a65a-fa95-4f45-93a5-7a27db8e4ba0.jpg"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <span class="text-[14px] font-semibold text-gray-800 text-center w-full">Car Care</span>
                    </a>

                    <!-- 7. Perfume -->
                    <a href="#" class="flex flex-col items-center min-w-[105px] group">
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-3">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/a1c5ba92-b39d-4618-91aa-bb1fd6741454.jpg"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <span class="text-[14px] font-semibold text-gray-800 text-center truncate w-full px-1">Perfume &
                            Showpiece</span>
                    </a>

                    <!-- 8. Key Accessories -->
                    <a href="#" class="flex flex-col items-center min-w-[105px] group">
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-3">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/d906a5fe-80fb-4e52-80e7-7b1c6e03c6a8.jpg"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <span class="text-[14px] font-semibold text-gray-800 text-center truncate w-full px-1">Key
                            Accessories</span>
                    </a>

                    <!-- 9. Performance -->
                    <a href="#" class="flex flex-col items-center min-w-[105px] group">
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-3">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/045ec464-0547-42c3-848a-70ec553cceba.jpg"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <span class="text-[14px] font-semibold text-gray-800 text-center w-full">Performance</span>
                    </a>

                    <!-- 10. LED -->
                    <a href="#" class="flex flex-col items-center min-w-[105px] group">
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-3">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/8bbd7052-574b-4073-bc59-60025870ffb9.jpg"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <span class="text-[14px] font-semibold text-gray-800 text-center w-full">LED & Lighting</span>
                    </a>

                    <!-- 11. Modifications -->
                    <a href="#" class="flex flex-col items-center min-w-[105px] group">
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-3">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/3bcc0f12-c2b8-49ad-a7c1-8d048801d4c9.jpg"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <span
                            class="text-[14px] font-semibold text-gray-800 text-center truncate w-full px-1">Modifications</span>
                    </a>
                    <!-- 11. Modifications -->
                    <a href="#" class="flex flex-col items-center min-w-[105px] group">
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-3">
                            <img src="./assets/images/cover.webp"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <span
                            class="text-[14px] font-semibold text-gray-800 text-center truncate w-full px-1">Covers</span>
                    </a>
                    <!-- 11. Modifications -->
                    <a href="#" class="flex flex-col items-center min-w-[105px] group">
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-3">
                            <img src="./assets/images/safety.webp"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <span class="text-[14px] font-semibold text-gray-800 text-center truncate w-full px-1">Safety &
                            Emergency</span>
                    </a>

                </div>
            </div>
        </div>
    </section>
    <!-- NEW ARRIVALS SECTION -->
    <section class="py-6 container mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-black uppercase tracking-tight">New Arrivals</h2>
                <a href="/new-arrivals">
                    <button
                        class="bg-[#FF6A00] hover:bg-[#d44d1f] text-white text-sm font-bold px-5 py-2 rounded transition-colors shadow-sm">
                        View all
                    </button>
                </a>
            </div>

            <!-- Carousel Wrapper -->
            <div class="relative group">

                <!-- Left Arrow -->
                <button onclick="scrollNA(-280)"
                    class="absolute left-1 top-[35%] -translate-y-1/2 z-20 w-7 h-7 bg-gray-50 border border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-all text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>

                <!-- Track -->
                <div id="na-track" class="flex gap-4 overflow-x-auto scroll-smooth no-scrollbar pb-4"
                    style="-ms-overflow-style:none; scrollbar-width:none;">

                    <!-- Card 1 -->
                    <div class="flex-shrink-0 w-[240px] flex flex-col group/card cursor-pointer">
                        <a href="#" class="block">
                            <div
                                class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                                <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/b55d14e8-ae33-47e3-8b23-7296ac1ba55c.jpg"
                                    class="w-full h-full object-cover group-hover/card:scale-105 transition-transform duration-500"
                                    alt="">
                            </div>
                            <h3
                                class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2 px-1">
                                Professional Car Foam Washing Gun Kit – Adjustable Foam Cannon Bottle with High Pressure
                                Trigger Gun
                            </h3>
                            <div class="flex items-center gap-2 mb-2 px-1">
                                <span class="text-xl font-bold text-[#f15a24]">৳2090</span>
                            </div>
                            <div class="px-1">
                                <span
                                    class="inline-block bg-[#ff9800] text-white text-[11px] font-bold px-2 py-0.5 rounded uppercase">Free
                                    Delivery</span>
                            </div>
                        </a>
                    </div>

                    <!-- Card 2 -->
                    <div class="flex-shrink-0 w-[240px] flex flex-col group/card cursor-pointer">
                        <a href="#" class="block">
                            <div
                                class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                                <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/94df0668-09c1-4d75-a9e5-55e141c52f7b.jpg"
                                    class="w-full h-full object-cover group-hover/card:scale-105 transition-transform duration-500"
                                    alt="">
                            </div>
                            <h3
                                class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2 px-1">
                                Car Door lock Cover Pads – Silicone Anti-Collision Door Latch Protectors, Noise Reduction
                            </h3>
                            <div class="flex items-center gap-2 mb-2 px-1">
                                <span class="text-xl font-bold text-[#f15a24]">৳550</span>
                            </div>
                        </a>
                    </div>

                    <!-- Card 3 -->
                    <div class="flex-shrink-0 w-[240px] flex flex-col group/card cursor-pointer">
                        <a href="#" class="block">
                            <div
                                class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                                <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/0feacd2d-0253-4db0-8f2e-102acf38eff4.jpg"
                                    class="w-full h-full object-cover group-hover/card:scale-105 transition-transform duration-500"
                                    alt="">
                            </div>
                            <h3
                                class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2 px-1">
                                Car Front Corner Light Assembly kit | Clear Indicator Turn Signal Lamp Replacement
                            </h3>
                            <div class="flex items-center gap-2 mb-2 px-1">
                                <span class="text-xl font-bold text-[#f15a24]">৳5000</span>
                            </div>
                            <div class="px-1">
                                <span
                                    class="inline-block bg-[#ff9800] text-white text-[11px] font-bold px-2 py-0.5 rounded uppercase">Free
                                    Delivery</span>
                            </div>
                        </a>
                    </div>

                    <!-- Card 4 -->
                    <div class="flex-shrink-0 w-[240px] flex flex-col group/card cursor-pointer">
                        <a href="#" class="block">
                            <div
                                class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                                <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/d65bee94-9744-4fb4-ac1d-03f7c6213317.jpg"
                                    class="w-full h-full object-cover group-hover/card:scale-105 transition-transform duration-500"
                                    alt="">
                            </div>
                            <h3
                                class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2 px-1">
                                Car Headlight Housing Set – Clear Front Headlamp Replacement Kit
                            </h3>
                            <div class="flex items-center gap-2 mb-2 px-1">
                                <span class="text-xl font-bold text-[#f15a24]">৳6500</span>
                            </div>
                            <div class="px-1">
                                <span
                                    class="inline-block bg-[#ff9800] text-white text-[11px] font-bold px-2 py-0.5 rounded uppercase">Free
                                    Delivery</span>
                            </div>
                        </a>
                    </div>

                    <!-- Card 5 - Discounted Item -->
                    <div class="flex-shrink-0 w-[240px] flex flex-col group/card cursor-pointer">
                        <a href="#" class="block">
                            <div
                                class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                                <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/1f7d7cb1-5d4d-45b1-bdb9-bac914b90ef4.jpg"
                                    class="w-full h-full object-cover group-hover/card:scale-105 transition-transform duration-500"
                                    alt="">
                            </div>
                            <h3
                                class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2 px-1">
                                BMW Motorsport Folding Umbrella – Windproof & Waterproof Compact Travel Umbrella
                            </h3>
                            <div class="flex items-center gap-2 mb-2 px-1">
                                <span class="text-xl font-bold text-[#f15a24]">৳990</span>
                                <span class="text-sm text-gray-400 line-through">৳1780</span>
                                <span
                                    class="text-[11px] font-bold bg-[#fff0eb] text-[#f15a24] px-1.5 py-0.5 rounded">-44%</span>
                            </div>
                        </a>
                    </div>

                    <!-- Card 6 -->
                    <div class="flex-shrink-0 w-[240px] flex flex-col group/card cursor-pointer">
                        <a href="#" class="block">
                            <div
                                class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                                <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/c320b834-e437-41f4-8b51-1c8d6bc67d7f.jpg"
                                    class="w-full h-full object-cover group-hover/card:scale-105 transition-transform duration-500"
                                    alt="">
                            </div>
                            <h3
                                class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2 px-1">
                                Magnetic Car Seat Belt Holder Clip – Anti-Rattle Car Seatbelt Stabilizer
                            </h3>
                            <div class="flex items-center gap-2 mb-2 px-1">
                                <span class="text-xl font-bold text-[#f15a24]">৳390</span>
                            </div>
                        </a>
                    </div>
                    <!-- Card 6 -->
                    <div class="flex-shrink-0 w-[240px] flex flex-col group/card cursor-pointer">
                        <a href="#" class="block">
                            <div
                                class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                                <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/c320b834-e437-41f4-8b51-1c8d6bc67d7f.jpg"
                                    class="w-full h-full object-cover group-hover/card:scale-105 transition-transform duration-500"
                                    alt="">
                            </div>
                            <h3
                                class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2 px-1">
                                Magnetic Car Seat Belt Holder Clip – Anti-Rattle Car Seatbelt Stabilizer
                            </h3>
                            <div class="flex items-center gap-2 mb-2 px-1">
                                <span class="text-xl font-bold text-[#f15a24]">৳390</span>
                            </div>
                        </a>
                    </div>
                    <!-- Card 6 -->
                    <div class="flex-shrink-0 w-[240px] flex flex-col group/card cursor-pointer">
                        <a href="#" class="block">
                            <div
                                class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                                <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/c320b834-e437-41f4-8b51-1c8d6bc67d7f.jpg"
                                    class="w-full h-full object-cover group-hover/card:scale-105 transition-transform duration-500"
                                    alt="">
                            </div>
                            <h3
                                class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2 px-1">
                                Magnetic Car Seat Belt Holder Clip – Anti-Rattle Car Seatbelt Stabilizer
                            </h3>
                            <div class="flex items-center gap-2 mb-2 px-1">
                                <span class="text-xl font-bold text-[#f15a24]">৳390</span>
                            </div>
                        </a>
                    </div>

                </div><!-- /#na-track -->

                <!-- Right Arrow -->
                <button onclick="scrollNA(280)"
                    class="absolute right-1 top-[35%] -translate-y-1/2 z-20 w-7 h-7 bg-gray-50 border border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-all text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

            </div><!-- /.relative -->

        </div>
    </section>

    <!-- DUAL BANNER SECTION -->
    <section class="py-6 container mx-auto">
        <div class="flex flex-row gap-3 md:gap-5">

            <!-- Left Banner -->
            <div
                class="flex-1 overflow-hidden rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 cursor-pointer">
                <img src="https://orenmart.sgp1.digitaloceanspaces.com/banner/da1e494c-e05c-4725-b0cf-c90e56ccba1f.jpg"
                    alt="Promo Banner 1" loading="lazy"
                    class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-500">
            </div>

            <!-- Right Banner -->
            <div
                class="flex-1 overflow-hidden rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 cursor-pointer">
                <img src="https://orenmart.sgp1.digitaloceanspaces.com/banner/2e43fa93-a2ca-4371-bd4f-b6b4d4dca46e.jpg"
                    alt="Promo Banner 2" loading="lazy"
                    class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-500">
            </div>

        </div>
    </section>

    <!-- POPULAR BRANDS SECTION -->
    <section class="py-6 container mx-auto">
        <!-- Main Card Container -->
        <div class="bg-white rounded-lg shadow-xs border border-gray-200 p-6 relative">

            <!-- Header -->
            <div class="flex items-center mb-8">
                <h2 class="text-[17px] font-bold text-black uppercase tracking-tight">Popular Brands</h2>
            </div>

            <!-- Brands Slider -->
            <div class="relative group">

                <!-- Left Arrow -->
                <button onclick="scrollBrands(-400)"
                    class="absolute left-1 top-[38%] -translate-y-1/2 z-20 w-8 h-8 bg-white border border-gray-200 rounded-full shadow-md flex items-center justify-center hover:bg-gray-50 transition-all text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>

                <!-- Brand Track: Gap increased to 8 for more spacing -->
                <div id="brand-track" class="flex overflow-x-auto scroll-smooth no-scrollbar py-2 gap-8">

                    <!-- 1. Castrol -->
                    <a href="/brand/castrol"
                        class="flex-shrink-0 w-[calc(11.11%-28px)] min-w-[100px] flex flex-col items-center group/brand">
                        <div
                            class="w-full aspect-square rounded-full border border-gray-100 p-5 flex items-center justify-center bg-white transition-all duration-300 hover:border-gray-300">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/43690762-f37a-4461-b510-496df7e5aa13.jpg"
                                alt="" class="w-full max-h-full object-contain rounded-full">
                        </div>
                        <h4 class="mt-4 text-md font-semibold text-gray-800 text-center">Castrol</h4>
                    </a>

                    <!-- 2. JBL -->
                    <a href="/brand/jbl"
                        class="flex-shrink-0 w-[calc(11.11%-28px)] min-w-[100px] flex flex-col items-center group/brand">
                        <div
                            class="w-full aspect-square rounded-full border border-gray-100 p-5 flex items-center justify-center bg-white">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/d0b3929d-6c65-44c2-b603-84e7b83b6db7.jpg"
                                alt="" class="w-full max-h-full object-contain rounded-full">
                        </div>
                        <h4 class="mt-4 text-md font-semibold text-gray-800 text-center">JBL</h4>
                    </a>

                    <!-- 3. Total -->
                    <a href="/brand/total"
                        class="flex-shrink-0 w-[calc(11.11%-28px)] min-w-[100px] flex flex-col items-center group/brand">
                        <div
                            class="w-full aspect-square rounded-full border border-gray-100 p-5 flex items-center justify-center bg-white">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/8ab6da24-e58f-4a72-abdd-abd34455c0db.jpg"
                                alt="" class="w-full max-h-full object-contain rounded-full">
                        </div>
                        <h4 class="mt-4 text-md font-semibold text-gray-800 text-center">TOTAL</h4>
                    </a>

                    <!-- 4. OrenMart -->
                    <a href="/brand/orenmart"
                        class="flex-shrink-0 w-[calc(11.11%-28px)] min-w-[100px] flex flex-col items-center group/brand">
                        <div
                            class="w-full aspect-square rounded-full border border-gray-100 p-5 flex items-center justify-center bg-white">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/91d11a74-e29d-423b-9125-165ae05b2efb.jpg"
                                alt="" class="w-full max-h-full object-contain rounded-full">
                        </div>
                        <h4 class="mt-4 text-md font-semibold text-gray-800 text-center">OrenMart</h4>
                    </a>

                    <!-- 5. TypeR -->
                    <a href="/brand/typer"
                        class="flex-shrink-0 w-[calc(11.11%-28px)] min-w-[100px] flex flex-col items-center group/brand">
                        <div
                            class="w-full aspect-square rounded-full border border-gray-100 p-5 flex items-center justify-center bg-white">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/25914bf2-c1c6-4db0-a982-85ca5f83029e.jpg"
                                alt="" class="w-full max-h-full object-contain rounded-full">
                        </div>
                        <h4 class="mt-4 text-md font-semibold text-gray-800 text-center">TypeR</h4>
                    </a>

                    <!-- 6. SanDisk -->
                    <a href="/brand/sandisk"
                        class="flex-shrink-0 w-[calc(11.11%-28px)] min-w-[100px] flex flex-col items-center group/brand">
                        <div
                            class="w-full aspect-square rounded-full border border-gray-100 p-5 flex items-center justify-center bg-white">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/63882e33-8466-4dc3-b5c0-f499276add38.jpg"
                                alt="" class="w-full max-h-full object-contain rounded-full">
                        </div>
                        <h4 class="mt-4 text-md font-semibold text-gray-800 text-center">SanDisk</h4>
                    </a>

                    <!-- 7. Formula 1 -->
                    <a href="/brand/formula-1"
                        class="flex-shrink-0 w-[calc(11.11%-28px)] min-w-[100px] flex flex-col items-center group/brand">
                        <div
                            class="w-full aspect-square rounded-full border border-gray-100 p-5 flex items-center justify-center bg-white">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/20905360-a660-4427-9cea-427f5d13d90e.jpg"
                                alt="" class="w-full max-h-full object-contain rounded-full">
                        </div>
                        <h4 class="mt-4 text-md font-semibold text-gray-800 text-center">Formula 1</h4>
                    </a>

                    <!-- 8. Sony -->
                    <a href="/brand/sony"
                        class="flex-shrink-0 w-[calc(11.11%-28px)] min-w-[100px] flex flex-col items-center group/brand">
                        <div
                            class="w-full aspect-square rounded-full border border-gray-100 p-5 flex items-center justify-center bg-white">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/93bc333d-12e9-4223-b5e6-f35ecb43cc22.jpg"
                                alt="" class="w-full max-h-full object-contain rounded-full">
                        </div>
                        <h4 class="mt-4 text-md font-semibold text-gray-800 text-center">SONY</h4>
                    </a>

                    <!-- 9. Sakura -->
                    <a href="/brand/sakura"
                        class="flex-shrink-0 w-[calc(11.11%-28px)] min-w-[100px] flex flex-col items-center group/brand">
                        <div
                            class="w-full aspect-square rounded-full border border-gray-100 p-5 flex items-center justify-center bg-white">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/803d165c-737f-4c36-96b6-2d5c69316cf5.jpg"
                                alt="" class="w-full max-h-full object-contain rounded-full">
                        </div>
                        <h4 class="mt-4 text-md font-semibold text-gray-800 text-center">Sakura</h4>
                    </a>
                    <!-- 9. Sakura -->
                    <a href="/brand/sakura"
                        class="flex-shrink-0 w-[calc(11.11%-28px)] min-w-[100px] flex flex-col items-center group/brand">
                        <div
                            class="w-full aspect-square rounded-full border border-gray-100 p-5 flex items-center justify-center bg-white">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/803d165c-737f-4c36-96b6-2d5c69316cf5.jpg"
                                alt="" class="w-full max-h-full object-contain rounded-full">
                        </div>
                        <h4 class="mt-4 text-md font-semibold text-gray-800 text-center">Sakura</h4>
                    </a>
                    <!-- 9. Sakura -->
                    <a href="/brand/sakura"
                        class="flex-shrink-0 w-[calc(11.11%-28px)] min-w-[100px] flex flex-col items-center group/brand">
                        <div
                            class="w-full aspect-square rounded-full border border-gray-100 p-5 flex items-center justify-center bg-white">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/803d165c-737f-4c36-96b6-2d5c69316cf5.jpg"
                                alt="" class="w-full max-h-full object-contain rounded-full">
                        </div>
                        <h4 class="mt-4 text-md font-semibold text-gray-800 text-center">Sakura</h4>
                    </a>
                    <!-- 9. Sakura -->
                    <a href="/brand/sakura"
                        class="flex-shrink-0 w-[calc(11.11%-28px)] min-w-[100px] flex flex-col items-center group/brand">
                        <div
                            class="w-full aspect-square rounded-full border border-gray-100 p-5 flex items-center justify-center bg-white">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/803d165c-737f-4c36-96b6-2d5c69316cf5.jpg"
                                alt="" class="w-full max-h-full object-contain rounded-full">
                        </div>
                        <h4 class="mt-4 text-md font-semibold text-gray-800 text-center">Sakura</h4>
                    </a>
                    <!-- 9. Sakura -->
                    <a href="/brand/sakura"
                        class="flex-shrink-0 w-[calc(11.11%-28px)] min-w-[100px] flex flex-col items-center group/brand">
                        <div
                            class="w-full aspect-square rounded-full border border-gray-100 p-5 flex items-center justify-center bg-white">
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/category/803d165c-737f-4c36-96b6-2d5c69316cf5.jpg"
                                alt="" class="w-full max-h-full object-contain rounded-full">
                        </div>
                        <h4 class="mt-4 text-md font-semibold text-gray-800 text-center">Sakura</h4>
                    </a>

                </div><!-- /#brand-track -->

                <!-- Right Arrow -->
                <button onclick="scrollBrands(400)"
                    class="absolute right-1 top-[38%] -translate-y-1/2 z-20 w-8 h-8 bg-white border border-gray-200 rounded-full shadow-md flex items-center justify-center hover:bg-gray-50 transition-all text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

            </div>
        </div>
    </section>

    <!-- YOU MAY LIKE SECTION -->
    <section class="py-6 container mx-auto">
        <div class="bg-white rounded-lg shadow-xs border border-gray-200 p-6">

            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-[18px] font-bold text-black uppercase tracking-tight">You May Like</h2>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-6">

                <!-- Product 1 -->
                <a href="/product/fruit-design-pillow" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/3b816691-be2d-4497-9cae-3b7971362ca7.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Car Fruit Design Pillow 2pcs – Trendy and Comfortable Car Interior Cushion
                    </h3>
                    <div class="mt-auto">
                        <span class="text-[17px] font-bold text-[#f15a24]">৳890</span>
                    </div>
                </a>

                <!-- Product 2 -->
                <a href="/product/denso-car-wiper-blade" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/bb9b556b-513a-497e-b5fe-2f915a6753ff.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Denso Car Wiper Blade Windshield Cleaning Solution-Original Denso
                    </h3>
                    <div class="flex flex-col mt-auto gap-1">
                        <span class="text-[17px] font-bold text-[#f15a24]">৳1790</span>
                        <div class="mt-1">
                            <span
                                class="bg-[#f15a24] text-white text-[12px] font-bold px-2 py-0.5 rounded-xs shadow-sm inline-block">
                                denso
                            </span>
                        </div>
                    </div>
                </a>

                <!-- Product 3 -->
                <a href="/product/car-document-holder" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/7e412a34-6d43-4f92-b056-d18f344e864d.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Premium Car Document Holder – Organize Your Vehicle Documents Easily
                    </h3>
                    <div class="flex flex-col mt-auto gap-1">
                        <span class="text-[17px] font-bold text-[#f15a24]">৳499</span>
                        <div class="mt-1">
                            <span
                                class="bg-[#f15a24] text-white text-[12px] font-bold px-2 py-0.5 rounded-xs shadow-sm inline-block">
                                OrenMart
                            </span>
                        </div>
                    </div>
                </a>

                <!-- Product 4 -->
                <a href="/product/carbon-fiber-honda-vezel" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/bdad6fee-d479-4ca3-b145-f0805c4788fd.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Carbon Fiber Honda Vezel Car Shoulder Pad Protection 2pcs
                    </h3>
                    <div class="mt-auto">
                        <span class="text-[17px] font-bold text-[#f15a24]">৳490</span>
                    </div>
                </a>

                <!-- Product 5 -->
                <a href="/product/diamond-morning-car-perfume" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/1ae55fb6-ac18-4132-bcfa-443c706c5f1c.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Diamond Morning Car Perfume 75ml Liquid | Unique Car Dashboard Perfume
                    </h3>
                    <div class="mt-auto">
                        <span class="text-[17px] font-bold text-[#f15a24]">৳799</span>
                    </div>
                </a>

                <!-- Product 6 -->
                <a href="/product/car-solar-shark-fin" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/f98f52e6-4c3e-4f62-8f36-e6317c66c0d3.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Car Solar Shark Fin Antenna – Aerodynamic, Stylish, and Signal-Boosting
                    </h3>
                    <div class="mt-auto">
                        <span class="text-[17px] font-bold text-[#f15a24]">৳590</span>
                    </div>
                </a>

                <!-- Product 7 -->
                <a href="/product/trd-car-door-protector" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/f546833d-bb5f-450b-9da6-eca298f57401.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Car Door Corner Protector Black 60x45mm Anti-Opening Collision Scratch Proof
                    </h3>
                    <div class="mt-auto">
                        <span class="text-[17px] font-bold text-[#f15a24]">৳250</span>
                    </div>
                </a>

                <!-- Product 8 (With Discount) -->
                <a href="/product/carall-omnibus" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/7f300ba6-8e6f-47ca-9a0b-5d0b2a1c6ea5.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Carall Omnibus Car Perfume – Premium Japanese Car Fragrance
                    </h3>
                    <div class="flex flex-col mt-auto gap-1">
                        <div class="flex items-center gap-2">
                            <span class="text-[17px] font-bold text-[#f15a24]">৳1990</span>
                            <span class="text-[13px] text-gray-400 line-through">৳2100</span>
                            <span class="text-[11px] font-bold bg-[#fff0eb] text-[#f15a24] px-1 py-0.5 rounded">-5%</span>
                        </div>
                        <div class="mt-1">
                            <span
                                class="bg-[#f15a24] text-white text-[12px] font-bold px-2 py-0.5 rounded-xs shadow-sm inline-block">
                                CARALL
                            </span>
                        </div>
                    </div>
                </a>
                <!-- Product 1 -->
                <a href="/product/fruit-design-pillow" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/3b816691-be2d-4497-9cae-3b7971362ca7.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Car Fruit Design Pillow 2pcs – Trendy and Comfortable Car Interior Cushion
                    </h3>
                    <div class="mt-auto">
                        <span class="text-[17px] font-bold text-[#f15a24]">৳890</span>
                    </div>
                </a>

                <!-- Product 2 -->
                <a href="/product/denso-car-wiper-blade" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/bb9b556b-513a-497e-b5fe-2f915a6753ff.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Denso Car Wiper Blade Windshield Cleaning Solution-Original Denso
                    </h3>
                    <div class="flex flex-col mt-auto gap-1">
                        <span class="text-[17px] font-bold text-[#f15a24]">৳1790</span>
                        <div class="mt-1">
                            <span
                                class="bg-[#f15a24] text-white text-[12px] font-bold px-2 py-0.5 rounded-xs shadow-sm inline-block">
                                denso
                            </span>
                        </div>
                    </div>
                </a>

                <!-- Product 3 -->
                <a href="/product/car-document-holder" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/7e412a34-6d43-4f92-b056-d18f344e864d.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Premium Car Document Holder – Organize Your Vehicle Documents Easily
                    </h3>
                    <div class="flex flex-col mt-auto gap-1">
                        <span class="text-[17px] font-bold text-[#f15a24]">৳499</span>
                        <div class="mt-1">
                            <span
                                class="bg-[#f15a24] text-white text-[12px] font-bold px-2 py-0.5 rounded-xs shadow-sm inline-block">
                                OrenMart
                            </span>
                        </div>
                    </div>
                </a>

                <!-- Product 4 -->
                <a href="/product/carbon-fiber-honda-vezel" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/bdad6fee-d479-4ca3-b145-f0805c4788fd.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Carbon Fiber Honda Vezel Car Shoulder Pad Protection 2pcs
                    </h3>
                    <div class="mt-auto">
                        <span class="text-[17px] font-bold text-[#f15a24]">৳490</span>
                    </div>
                </a>

                <!-- Product 5 -->
                <a href="/product/diamond-morning-car-perfume" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/1ae55fb6-ac18-4132-bcfa-443c706c5f1c.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Diamond Morning Car Perfume 75ml Liquid | Unique Car Dashboard Perfume
                    </h3>
                    <div class="mt-auto">
                        <span class="text-[17px] font-bold text-[#f15a24]">৳799</span>
                    </div>
                </a>

                <!-- Product 6 -->
                <a href="/product/car-solar-shark-fin" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/f98f52e6-4c3e-4f62-8f36-e6317c66c0d3.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Car Solar Shark Fin Antenna – Aerodynamic, Stylish, and Signal-Boosting
                    </h3>
                    <div class="mt-auto">
                        <span class="text-[17px] font-bold text-[#f15a24]">৳590</span>
                    </div>
                </a>

                <!-- Product 7 -->
                <a href="/product/trd-car-door-protector" class="group flex flex-col cursor-pointer">
                    <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/f546833d-bb5f-450b-9da6-eca298f57401.jpg"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="">
                    </div>
                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        Car Door Corner Protector Black 60x45mm Anti-Opening Collision Scratch Proof
                    </h3>
                    <div class="mt-auto">
                        <span class="text-[17px] font-bold text-[#f15a24]">৳250</span>
                    </div>
                </a>

            </div>

            <!-- View More Button -->
            <div class="flex justify-center mt-10">
                <button
                    class="bg-[#FF6A00] text-white hover:bg-gray-50 hover:text-[#FF6A00] hover:font-semibold text-[#ff9800] font-semibold py-2 px-4 rounded-md transition-colors shadow-sm text-sm">
                    View More
                </button>
            </div>

        </div>
    </section>
@endsection
@push('scripts')
    <script>
        function scrollBrands(px) {
            document.getElementById('brand-track').scrollBy({
                left: px,
                behavior: 'smooth'
            });
        }

        function scrollNA(px) {
            document.getElementById('na-track').scrollBy({
                left: px,
                behavior: 'smooth'
            });
        }

        function scrollCats(distance) {
            document.getElementById('cat-slider').scrollBy({
                left: distance,
                behavior: 'smooth'
            });
        }
    </script>
    <script>
        const mainSlider = document.getElementById('main-slider');
        const mainDots = document.querySelectorAll('.main-dot');
        let mainIdx = 0;

        function slideMain() {
            mainIdx = (mainIdx + 1) % mainDots.length;
            mainSlider.style.transform = `translateX(-${mainIdx * 100}%)`;
            mainDots.forEach((dot, i) => {
                if (i === mainIdx) {
                    dot.classList.replace('w-2', 'w-6');
                    dot.classList.replace('bg-white/50', 'bg-[#FF6A00]');
                } else {
                    dot.classList.replace('w-6', 'w-2');
                    dot.classList.replace('bg-[#FF6A00]', 'bg-white/50');
                }
            });
        }
        setInterval(slideMain, 4000);

        const verticalSlider = document.getElementById('vertical-slider');
        let vertIdx = 0;
        const totalVert = verticalSlider.children.length;

        function slideVertical() {
            vertIdx = (vertIdx + 1) % totalVert;
            verticalSlider.style.transform = `translateY(-${vertIdx * 100}%)`;
        }

        setInterval(slideVertical, 5000);

        function toggleDropdown(id) {
            const menu = document.getElementById('menu-' + id);
            const icon = document.getElementById('icon-' + id);
            if (menu.style.maxHeight === "0px" || menu.style.maxHeight === "") {
                menu.style.maxHeight = menu.scrollHeight + "px";
                icon.style.transform = "rotate(-180deg)";
            } else {
                menu.style.maxHeight = "0px";
                icon.style.transform = "rotate(0deg)";
            }
        }
    </script>
@endpush
