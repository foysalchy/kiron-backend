@extends('template1.layouts.front')

@section('content')
    <!-- Header Text Section -->
    <section class="container mx-auto py-6 max-w-4xl">
        <!-- Main Header -->
        <div class="mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Privacy Policy</h1>
            <p class="text-lg text-gray-500">Last updated: 12/12/2025</p>
        </div>

        <!-- Privacy Policy Sections -->
        <div class="space-y-6">

            <!-- 1. Information We Collect -->
            <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-xs">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Information We Collect</h2>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-medium text-gray-800 mb-2">Personal Information</h3>
                        <p class="text-gray-600 text-md leading-relaxed">We collect information you provide directly to us,
                            such as when you create an account, make a purchase, or contact us for support. This may
                            include:</p>
                        <ul class="list-disc list-inside mt-3 space-y-1.5 text-gray-600 text-md">
                            <li>Name and contact information</li>
                            <li>Billing and shipping addresses</li>
                            <li>Payment information</li>
                            <li>Account credentials</li>
                            <li>Communication preferences</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-xl font-medium text-gray-800 mb-2">Automatically Collected Information</h3>
                        <p class="text-gray-600 text-[15px] leading-relaxed">We automatically collect certain information
                            about your device and usage of our services:</p>
                        <ul class="list-disc list-inside mt-3 space-y-1.5 text-gray-600 text-md">
                            <li>IP address and location data</li>
                            <li>Browser type and version</li>
                            <li>Device information</li>
                            <li>Usage patterns and preferences</li>
                            <li>Cookies and similar technologies</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 2. How We Use -->
            <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-xs">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">How We Use Your Information</h2>
                <p class="text-gray-600 text-md leading-relaxed mb-4">We use the information we collect for various
                    purposes, including:</p>
                <ul class="list-disc list-inside space-y-2 text-gray-600 text-md">
                    <li>Processing and fulfilling your orders</li>
                    <li>Providing customer support and responding to inquiries</li>
                    <li>Personalizing your shopping experience</li>
                    <li>Sending promotional communications (with your consent)</li>
                    <li>Improving our products and services</li>
                    <li>Preventing fraud and ensuring security</li>
                    <li>Complying with legal obligations</li>
                </ul>
            </div>

            <!-- 3. Information Sharing -->
            <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-xs">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Information Sharing</h2>
                <p class="text-gray-600 text-md leading-relaxed mb-4">We do not sell, trade, or rent your personal
                    information to third parties. We may share your information in the following circumstances:</p>
                <ul class="list-disc list-inside space-y-2 text-gray-600 text-md">
                    <li>With service providers who assist us in operating our platform</li>
                    <li>With sellers to fulfill your orders</li>
                    <li>When required by law or to protect our rights</li>
                    <li>In connection with a business transfer or merger</li>
                    <li>With your explicit consent</li>
                </ul>
            </div>

            <!-- 4. Data Security -->
            <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-xs">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Data Security</h2>
                <p class="text-gray-600 text-md leading-relaxed">We implement appropriate technical and organizational
                    measures to protect your personal information against unauthorized access, alteration, disclosure, or
                    destruction. These measures include encryption, secure servers, and regular security assessments.</p>
            </div>

            <!-- 5. Your Rights -->
            <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-xs">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Your Rights</h2>
                <p class="text-gray-600 text-[15px] leading-relaxed mb-4">You have certain rights regarding your personal
                    information:</p>
                <ul class="list-disc list-inside space-y-2 text-gray-600 text-md">
                    <li>Access and review your personal information</li>
                    <li>Correct inaccurate or incomplete information</li>
                    <li>Delete your account and personal information</li>
                    <li>Opt-out of marketing communications</li>
                    <li>Data portability</li>
                    <li>Object to certain processing activities</li>
                </ul>
            </div>

            <!-- 6. Cookies and Tracking -->
            <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-xs">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Cookies and Tracking</h2>
                <p class="text-gray-600 text-md leading-relaxed mb-4">We use cookies and similar tracking technologies to
                    enhance your experience:</p>
                <ul class="list-disc list-inside space-y-2 text-gray-600 text-md">
                    <li>Essential cookies for site functionality</li>
                    <li>Analytics cookies to understand usage patterns</li>
                    <li>Marketing cookies for personalized advertising</li>
                    <li>Preference cookies to remember your settings</li>
                </ul>
                <p class="text-gray-600 text-md mt-4">You can control cookie settings through your browser preferences.</p>
            </div>

            <!-- 7. Children's Privacy -->
            <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-xs">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Children's Privacy</h2>
                <p class="text-gray-600 text-md leading-relaxed">Our services are not intended for children under 13 years
                    of age. We do not knowingly collect personal information from children under 13. If we become aware that
                    we have collected such information, we will take steps to delete it promptly.</p>
            </div>

            <!-- 8. International Transfers -->
            <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-xs">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">International Transfers</h2>
                <p class="text-gray-600 text-md leading-relaxed">Your information may be transferred to and processed in
                    countries other than your own. We ensure appropriate safeguards are in place to protect your information
                    in accordance with applicable data protection laws.</p>
            </div>

            <!-- 9. Changes to This Policy -->
            <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-xs">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Changes to This Policy</h2>
                <p class="text-gray-600 text-md leading-relaxed">We may update this Privacy Policy from time to time. We
                    will notify you of any material changes by posting the new policy on this page and updating the "Last
                    updated" date. Your continued use of our services after such changes constitutes acceptance of the
                    updated policy.</p>
            </div>

            <!-- 10. Contact Us -->
            <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-xs">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Contact Us</h2>
                <p class="text-gray-600 text-md leading-relaxed mb-5">If you have any questions about this Privacy Policy or
                    our data practices, please contact us:</p>
                <div class="space-y-2.5 text-md text-gray-700">
                    <p><span class="font-bold">Email:</span> {{$setup->email ?? ''}}</p>
                    <p><span class="font-bold">Phone:</span>{{ $setup->phone ?? 'নম্বর পাওয়া যায়নি' }}</p>
                    <p><span class="font-bold">Address:</span> {{ $setup->corporate_address ?? '' }}</p>
                </div>
            </div>

        </div>

        <!-- Copyright Footer -->
        <div class="mt-12 text-center text-gray-500 text-md border-t border-gray-200">
            <p class="mt-6">© 2024 OrenMart. All rights reserved.</p>
        </div>
    </section>
@endsection
