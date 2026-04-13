@extends('template1.layouts.front')

@section('content')
    <!-- Header Text Section -->
    <section class="container mx-auto py-6">
        <a href="../index.html"
            class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-md text-sm text-gray-700 hover:bg-gray-50 transition-all mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-arrow-left h-4 w-4 mr-2">
                <path d="m12 19-7-7 7-7"></path>
                <path d="M19 12H5"></path>
            </svg>
            Return to Home Page
        </a>
        <h1 class="text-2xl md:text-3xl font-black text-[#1D2128] mb-2 tracking-tight">Terms and Conditions</h1>
        <p class="text-gray-500 font-medium">Last Updated: January 1, 2024</p>
    </section>
    <!-- Header Text Section -->
    <section class="container mx-auto py-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

            <!-- LEFT SIDEBAR -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm">
                    <div class="p-5 border-b border-gray-100 bg-gray-50">
                        <h2 class="font-bold text-gray-900">Table of Contents</h3>
                    </div>
                    <nav class="flex flex-col">
                        <a href="#section1"
                            class="flex items-center gap-3 px-5 py-4 text-sm bg-orange-100 text-[#FF6A00] border-r-2 border-[#FF6A00]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-file-text h-4 w-4">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                <path d="M10 9H8"></path>
                                <path d="M16 13H8"></path>
                                <path d="M16 17H8"></path>
                            </svg>
                            Acceptance of Terms
                        </a>
                        <a href="#section2"
                            class="flex items-center gap-3 px-5 py-4 text-sm text-gray-600 hover:bg-gray-50 border-r-4 border-transparent transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-shield h-4 w-4">
                                <path
                                    d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                                </path>
                            </svg>
                            Account Rules
                        </a>
                        <a href="#section3"
                            class="flex items-center gap-3 px-5 py-4 text-sm text-gray-600 hover:bg-gray-50 border-r-4 border-transparent transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-credit-card h-4 w-4">
                                <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                <line x1="2" x2="22" y1="10" y2="10"></line>
                            </svg>
                            Order and Payment
                        </a>
                        <a href="#section4"
                            class="flex items-center gap-3 px-5 py-4 text-sm text-gray-600 hover:bg-gray-50 border-r-4 border-transparent transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-truck h-4 w-4">
                                <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                                <path d="M15 18H9"></path>
                                <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14">
                                </path>
                                <circle cx="17" cy="18" r="2"></circle>
                                <circle cx="7" cy="18" r="2"></circle>
                            </svg>
                            Delivery Policy
                        </a>
                        <a href="#section5"
                            class="flex items-center gap-3 px-5 py-4 text-sm text-gray-600 hover:bg-gray-50 border-r-4 border-transparent transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-rotate-ccw h-4 w-4">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <path d="M3 3v5h5"></path>
                            </svg>
                            Returns & Refunds
                        </a>
                        <a href="#section6"
                            class="flex items-center gap-3 px-5 py-4 text-sm text-gray-600 hover:bg-gray-50 border-r-4 border-transparent transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-triangle-alert h-4 w-4">
                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path>
                                <path d="M12 9v4"></path>
                                <path d="M12 17h.01"></path>
                            </svg>
                            Prohibited Activities
                        </a>
                        <a href="#section7"
                            class="flex items-center gap-3 px-5 py-4 text-sm text-gray-600 hover:bg-gray-50 border-r-4 border-transparent transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-scale h-4 w-4">
                                <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"></path>
                                <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"></path>
                                <path d="M7 21h10"></path>
                                <path d="M12 3v18"></path>
                                <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"></path>
                            </svg>
                            Limitation of Liability
                        </a>
                        <a href="#section8"
                            class="flex items-center gap-3 px-5 py-4 text-sm text-gray-600 hover:bg-gray-50 border-r-4 border-transparent transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-square-pen h-4 w-4">
                                <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path
                                    d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z">
                                </path>
                            </svg>
                            Changes to Terms
                        </a>
                    </nav>
                </div>
            </div>

            <!-- RIGHT CONTEN-->
            <div class="lg:col-span-3 space-y-8">

                <!-- (Blue) -->
                <section id="section1" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-blue-50 flex items-center gap-3 text-blue-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-file-text h-5 w-5">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                            <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                            <path d="M10 9H8"></path>
                            <path d="M16 13H8"></path>
                            <path d="M16 17H8"></path>
                        </svg>
                        <span>1. Acceptance of Terms</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-4">
                        <p>By using the OrenMart website, you agree to these Terms and Conditions. If you do not agree with
                            these terms, please do not use our services.</p>
                        <ul class="list-disc list-inside space-y-2 ml-4 marker:text-blue-500">
                            <li>These terms apply to all users</li>
                            <li>By using our services, you are obligated to comply with these rules</li>
                            <li>We reserve the right to suspend your account for violating the terms</li>
                        </ul>
                    </div>
                </section>

                <!-- 2. Account Rules (Green) -->
                <section id="section2" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-green-50 flex items-center gap-3 text-green-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-shield h-5 w-5">
                            <path
                                d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                            </path>
                        </svg>
                        <span>2. Account Rules</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-6">
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">Account Creation:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>You must provide accurate and complete information</li>
                                <li>You must use a valid email address</li>
                                <li>You are responsible for keeping your password confidential</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">Account Security:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>Use a strong password</li>
                                <li>Do not share your login details with others</li>
                                <li>Notify us if you notice suspicious activity</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- order payment -->
                <section id="section3" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-purple-50 flex items-center gap-3 text-purple-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-credit-card h-5 w-5">
                            <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                            <line x1="2" x2="22" y1="10" y2="10"></line>
                        </svg>
                        <span>3. Order and Payment</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-6">
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">Order Process:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>Verify all information before confirming your order</li>
                                <li>You will receive order confirmation within 24 hours of placing the order</li>
                                <li>We will inform you if the item is out of stock</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">Payment Policy:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>Cash on Delivery (COD) is available</li>
                                <li>Online payments are secure and encrypted</li>
                                <li>The order will be canceled if the payment fails</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 4. Delivery Policy (Orange) -->
                <section id="section4" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-orange-50 flex items-center gap-3 text-orange-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-truck h-5 w-5">
                            <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                            <path d="M15 18H9"></path>
                            <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14">
                            </path>
                            <circle cx="17" cy="18" r="2"></circle>
                            <circle cx="7" cy="18" r="2"></circle>
                        </svg>
                        <span>4. Delivery Policy</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-6">
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">Delivery Time:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>Within Dhaka: 1-2 business days</li>
                                <li>Outside Dhaka: 3-5 business days</li>
                                <li>Special items may require additional time</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">Delivery Charges:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>Within Dhaka: 60 BDT</li>
                                <li>Outside Dhaka: 100 BDT</li>
                                <li>Free delivery for orders over 1000 BDT</li>
                            </ul>
                        </div>
                    </div>
                </section>
                <!-- 5. Returns & Refunds (Red) -->
                <section id="section5" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-red-50 flex items-center gap-3 text-red-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-rotate-ccw h-5 w-5">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                            <path d="M3 3v5h5"></path>
                        </svg>
                        <span>5. Returns & Refunds</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-6">
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">Return Policy:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>Returns are accepted within 7 days of delivery</li>
                                <li>The product must be unused and in its original packaging</li>
                                <li>Free returns for damaged or incorrect items</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">Refund Process:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>Refunds within 5-7 business days after return acceptance</li>
                                <li>Refunds for online payments will be issued via the original payment method</li>
                                <li>Bank transfer for COD orders</li>
                            </ul>
                        </div>
                    </div>
                </section>
                <!-- 6. Prohibited Activities (Yellow) -->
                <section id="section6" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-yellow-50 flex items-center gap-3 text-yellow-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-triangle-alert h-5 w-5">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path>
                            <path d="M12 9v4"></path>
                            <path d="M12 17h.01"></path>
                        </svg>
                        <span>6. Prohibited Activities</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">The following activities are strictly prohibited:
                            </h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>Providing false information</li>
                                <li>Attempting to hack another user's account</li>
                                <li>Sending spam or unsolicited messages</li>
                                <li>Violating copyright</li>
                                <li>Attempting to breach website security</li>
                                <li>Uploading obscene or offensive content</li>
                            </ul>
                        </div>

                    </div>
                </section>
                <!-- 7. Limitation of Liability (Indigo) -->
                <section id="section7" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-indigo-50 flex items-center gap-3 text-indigo-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-scale h-5 w-5">
                            <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"></path>
                            <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"></path>
                            <path d="M7 21h10"></path>
                            <path d="M12 3v18"></path>
                            <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"></path>
                        </svg>
                        <span>7. Limitation of Liability</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">OrenMart is not liable for the following:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>The quality of third-party services or products</li>
                                <li>Delivery delays due to natural disasters</li>
                                <li>Issues caused by incorrect user information</li>
                                <li>Internet connectivity or technical problems</li>
                                <li>Unauthorized account use</li>

                            </ul>
                        </div>

                    </div>
                </section>
                <!-- 8. Changes to Terms (Teal) -->
                <section id="section8" class="scroll-mt-24 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="p-6 bg-teal-50 flex items-center gap-3 text-teal-700 font-black text-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-square-pen h-5 w-5">
                            <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path
                                d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z">
                            </path>
                        </svg>
                        <span>8. Changes to Terms</span>
                    </div>
                    <div class="p-8 text-gray-700 space-y-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">We reserve the right to change these terms at any
                                time. In case of changes:</h4>
                            <ul class="list-disc list-inside space-y-2 ml-4">
                                <li>A notice will be posted on the website</li>
                                <li>You will be notified by email</li>
                                <li>The effective date of the change will be stated</li>
                                <li>Using the service after the change means you accept the new terms</li>
                            </ul>
                        </div>

                    </div>
                </section>

                <!-- CONTACT SECTION (Gray) -->
                <section class="border border-gray-200 rounded-xl overflow-hidden bg-gray-50">
                    <div class="p-8 space-y-4">
                        <h3 class="text-xl font-bold text-gray-900">Contact</h3>
                        <p class="text-gray-600 font-medium">If you have any questions about these terms, please contact
                            us:</p>
                        <div class="space-y-2 text-gray-700">
                            <p><span class="font-bold">Email:</span> {{ $setup->email ?? '' }}</p>
                            <p><span class="font-bold">Phone:</span> {{ $setup->phone ?? 'Number not found' }}</p>
                            <p><span class="font-bold">Address:</span> {{ $setup->corporate_address ?? '' }}</p>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </section>
@endsection
@push('scripts')
    <script>
        const navLinks = document.querySelectorAll('nav a');
        const sections = document.querySelectorAll('section[id]');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= (sectionTop - 150)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('bg-orange-100', 'text-[#FF6A00]', 'border-[#FF6A00]');
                link.classList.add('text-gray-600', 'border-transparent');

                if (link.getAttribute('href').includes(current)) {
                    link.classList.add('bg-orange-100', 'text-[#FF6A00]', 'border-[#FF6A00]');
                    link.classList.remove('text-gray-600', 'border-transparent');
                }
            });
        });
    </script>
@endpush
