@extends('template1.layouts.front')

@section('content')
    <section class="container py-6 mx-auto px-4 lg:px-0">
        <!-- Dashboard Header -->
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-black text-[#1D2128]">আমার ড্যাশবোর্ড</h1>
            <form action="{{ route('user.logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="px-5 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                    লগআউট
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Left Sidebar: Persistent Profile & Nav -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6 text-center">
                    <!-- Avatar -->
                    <div class="flex justify-center mb-4">
                        <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center border-4 border-white shadow-sm overflow-hidden">
                            @if($user->profile)
                                <img src="{{ $user->profile_url }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-user text-3xl text-gray-300"></i>
                            @endif
                        </div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500 font-medium">{{ $user->email }}</p>

                    <!-- Sidebar Menu -->
                    <nav class="mt-8 space-y-2" id="dashboard-nav">
                        <button onclick="showSection('overview', this)"
                            class="nav-link w-full flex items-center gap-3 px-4 py-3 bg-[#1D2128] text-white rounded-xl text-sm font-semibold transition-all">
                            <i class="far fa-user w-5 text-center"></i> ওভারভিউ
                        </button>
                        <button onclick="showSection('orders', this)"
                            class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-orange-50 hover:text-[#FF6A00] rounded-xl text-sm font-semibold transition-all">
                            <i class="fas fa-shopping-bag w-5 text-center"></i> আমার
                            অর্ডার
                        </button>
                        <button onclick="showSection('wishlist', this)"
                            class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-orange-50 hover:text-[#FF6A00] rounded-xl text-sm font-semibold transition-all">
                            <i class="far fa-heart w-5 text-center"></i> উইশলিস্ট
                        </button>
                        <button onclick="showSection('edit', this)"
                            class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-orange-50 hover:text-[#FF6A00] rounded-xl text-sm font-semibold transition-all">
                            <i class="far fa-edit w-5 text-center"></i> প্রোফাইল এডিট
                        </button>
                        <button onclick="showSection('password', this)"
                            class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-orange-50 hover:text-[#FF6A00] rounded-xl text-sm font-semibold transition-all">
                            <i class="fas fa-lock w-5 text-center"></i> পাসওয়ার্ড পরিবর্তন
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Right Side Content Area -->
            <div class="lg:col-span-3">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg shadow-xs flex items-center gap-3">
                        <i class="fas fa-check-circle text-lg"></i>
                        <span class="font-bold">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg shadow-xs flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-lg"></i>
                        <span class="font-bold">{{ session('error') }}</span>
                    </div>
                @endif
                <!-- 1. SECTION: OVERVIEW (Default) -->
                <div id="overview-section" class="dashboard-content space-y-6">
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-xs flex items-center justify-between">
                            <div><p class="text-sm font-medium text-gray-700 mb-1">মোট অর্ডার</p>
                                <h4 class="text-2xl font-semibold text-gray-900">{{ $totalOrders ?? 0 }}</h4></div>
                            <i class="fas fa-shopping-bag h-8 w-8 text-orange-500 text-2xl"></i>
                        </div>
                        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-xs flex items-center justify-between">
                            <div><p class="text-sm font-medium text-gray-700 mb-1">মোট খরচ</p>
                                <h4 class="text-2xl font-semibold text-gray-900">৳{{ number_format($totalSpent ?? 0) }}</h4></div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-package h-8 w-8 text-green-500">
                                    <path
                                        d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z">
                                    </path>
                                    <path d="M12 22V12"></path>
                                    <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                                    <path d="m7.5 4.27 9 5.15"></path>
                                </svg>
                        </div>
                        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-xs flex items-center justify-between">
                            <div><p class="text-sm font-medium text-gray-700 mb-1">উইশলিস্ট</p>
                                <h4 class="text-2xl font-semibold text-gray-900">{{ $wishlistCount ?? 0 }}</h4></div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-heart h-8 w-8 text-red-500">
                                    <path
                                        d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z">
                                    </path>
                                </svg>
                        </div>
                    </div>

                    <!-- Recent Orders Table -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-xl md:text-2xl font-bold text-gray-900">
                                সাম্প্রতিক অর্ডার
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            @forelse($recentOrders ?? [] as $order)
                                <div class="flex flex-col md:flex-row items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-all gap-4">
                                    <div class="text-left">
                                        <p class="font-medium text-gray-900">{{ $order->order_number }}</p>
                                        <p class="text-sm text-gray-500">{{ $order->created_at->format('Y-m-d') }}</p>
                                    </div>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm font-semibold rounded-full uppercase">{{ $order->status }}</span>
                                    <div class="flex items-center gap-6">
                                        <p class="font-semibold text-gray-900">৳{{ number_format($order->grand_total) }}</p>
                                        <a href="" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:text-[#FF6A00]">দেখুন</a>
                                        {{-- <a href="{{ route('user.order.details', $order->id) }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:text-[#FF6A00]">দেখুন</a> --}}
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-gray-500 py-6">আপনার কোনো অর্ডার নেই।</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- 2. SECTION: ALL MY ORDERS  -->
                <div id="orders-section" class="dashboard-content hidden space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-xl md:text-2xl font-bold text-gray-900">
                                আমার সব অর্ডার
                            </h2>
                        </div>
                        <div class="p-6 space-y-8">
                            @forelse($allOrders as $order)
                                <div class="border border-gray-200 rounded-lg p-6 space-y-6">
                                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                        <div>
                                            <h4 class="font-semibold text-md text-gray-900">#{{ $order->order_number }}</h4>
                                            <p class="text-sm text-gray-700 font-medium">
                                                অর্ডার তারিখ: {{ $order->created_at->format('d M, Y') }}
                                            </p>
                                        </div>

                                        <div class="flex flex-col md:items-end gap-2 w-full md:w-auto">
                                            @php
                                                $statusClasses = [
                                                    'pending' => 'bg-orange-100 text-orange-700',
                                                    'processing' => 'bg-blue-100 text-blue-700',
                                                    'delivered' => 'bg-green-100 text-green-700',
                                                    'cancelled' => 'bg-red-100 text-red-700',
                                                ];
                                                $currentClass = $statusClasses[strtolower($order->status)] ?? 'bg-gray-100 text-gray-700';
                                            @endphp
                                            <span class="px-3 py-1 {{ $currentClass }} text-xs font-semibold rounded-full flex items-center gap-1.5 w-fit">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                            <p class="text-lg font-bold text-gray-900 leading-none">৳{{ number_format($order->grand_total) }}</p>
                                        </div>
                                    </div>

                                    <!-- Items inside this order -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        @foreach($order->orderDetails as $item)
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shrink-0 border border-gray-100 overflow-hidden">
                                                    @if($item->product && $item->product->image_url)
                                                        <img src="{{ $item->product->image_url }}" class="w-full h-full object-cover">
                                                    @else
                                                        <i class="fas fa-image text-gray-200"></i>
                                                    @endif
                                                </div>
                                                <div class="text-sm">
                                                    <p class="text-gray-800 leading-tight">{{ $item->product->name ?? 'Product Deleted' }}</p>
                                                    <p class="text-gray-500 font-medium">৳{{ number_format($item->price) }} x {{ $item->quantity }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="flex gap-3">
                                        <a href="" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-600 hover:text-[#FF6A00] flex items-center gap-2">
                                            <i class="fas fa-eye"></i> বিস্তারিত
                                        </a>
                                        <a href="/" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-600 hover:text-[#FF6A00] flex items-center gap-2">
                                            <i class="fas fa-download"></i> ইনভয়েস
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10">
                                    <p class="text-gray-500">আপনার কোনো অর্ডার পাওয়া যায়নি।</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- 3. SECTION: WISHLIST (Initially Hidden) -->
                <div id="wishlist-section" class="dashboard-content hidden space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">
                        <!-- Header -->
                        <div class="p-6">
                            <h2 class="text-xl font-bold text-gray-900">আমার উইশলিস্ট</h2>
                        </div>

                        <div class="p-6">
                            <!-- Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <!-- Wishlist Card 1 (In Stock) -->
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <!-- Image Placeholder -->
                                    <div
                                        class="w-full h-40 bg-gray-100 rounded-xl mb-4 flex items-center justify-center relative">
                                        <i class="far fa-image text-4xl text-gray-200"></i>
                                    </div>

                                    <h3 class="text-md text-gray-800 mb-3 line-clamp-2 h-10 leading-tight">
                                        Crystal Car Hanging Logo - Premium
                                    </h3>

                                    <div class="flex items-end justify-between mb-4">
                                        <div>
                                            <p class="text-lg font-semibold text-[#FF6A00]">৳1250</p>
                                            <p class="text-xs text-gray-400 line-through font-medium">
                                                ৳1500
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 bg-[#1D2128] text-white text-sm rounded-full">
                                            স্টকে আছে
                                        </span>
                                    </div>

                                    <div class="flex gap-2">
                                        <button
                                            class="flex-1 py-2.5 bg-[#1D2128] text-white rounded-lg text-sm hover:bg-black transition-all">
                                            কার্টে যোগ করুন
                                        </button>
                                        <button
                                            class="w-10 h-10 flex items-center justify-center border border-gray-200 rounded-lg text-gray-600 hover:text-red-500 hover:border-red-500 transition-all">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Wishlist Card 2 (Out of Stock) -->
                                <div
                                    class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow opacity-90">
                                    <div class="w-full h-40 bg-gray-100 rounded-xl mb-4 flex items-center justify-center">
                                        <i class="far fa-image text-4xl text-gray-200"></i>
                                    </div>

                                    <h3 class="text-md text-gray-800 mb-3 line-clamp-2 h-10 leading-tight">
                                        Heavy Vehicle Camera Solution Set
                                    </h3>

                                    <div class="flex items-end justify-between mb-4">
                                        <div>
                                            <p class="text-lg font-semibold text-[#FF6A00]">
                                                ৳15000
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 bg-red-500 text-white text-sm rounded-full">
                                            স্টক নেই
                                        </span>
                                    </div>

                                    <div class="flex gap-2">
                                        <button disabled
                                            class="flex-1 py-2.5 bg-gray-400 text-white rounded-lg text-sm cursor-not-allowed">
                                            কার্টে যোগ করুন
                                        </button>
                                        <button
                                            class="w-10 h-10 flex items-center justify-center border border-gray-200 rounded-lg text-gray-600 hover:text-red-500 hover:border-red-500 transition-all">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Wishlist Card 3 -->
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <!-- Image Placeholder -->
                                    <div
                                        class="w-full h-40 bg-gray-100 rounded-xl mb-4 flex items-center justify-center relative">
                                        <i class="far fa-image text-4xl text-gray-200"></i>
                                    </div>

                                    <h3 class="text-md text-gray-800 mb-3 line-clamp-2 h-10 leading-tight">
                                        Crystal Car Hanging Logo - Premium
                                    </h3>

                                    <div class="flex items-end justify-between mb-4">
                                        <div>
                                            <p class="text-lg font-semibold text-[#FF6A00]">৳1550</p>
                                            <p class="text-xs text-gray-400 line-through font-medium">
                                                ৳1500
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 bg-[#1D2128] text-white text-sm rounded-full">
                                            স্টকে আছে
                                        </span>
                                    </div>

                                    <div class="flex gap-2">
                                        <button
                                            class="flex-1 py-2.5 bg-[#1D2128] text-white rounded-lg text-sm hover:bg-black transition-all">
                                            কার্টে যোগ করুন
                                        </button>
                                        <button
                                            class="w-10 h-10 flex items-center justify-center border border-gray-200 rounded-lg text-gray-600 hover:text-red-500 hover:border-red-500 transition-all">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. SECTION: PROFILE EDIT (Initially Hidden) -->
                <div id="edit-section" class="dashboard-content hidden space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">প্রোফাইল আপডেট করুন</h2>
                        <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="text-sm text-gray-700">নাম</label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-3 rounded-lg border border-gray-100 bg-gray-50 text-sm focus:border-[#FF6A00] outline-none">
                                </div>
                                <div>
                                    <label class="text-sm text-gray-700">ইমেইল</label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-3 rounded-lg border border-gray-100 bg-gray-50 text-sm focus:border-[#FF6A00] outline-none">
                                </div>
                                <div>
                                    <label class="text-sm text-gray-700">ফোন</label>
                                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-3 rounded-lg border border-gray-100 bg-gray-50 text-sm focus:border-[#FF6A00] outline-none">
                                </div>
                                <div>
                                    <label class="text-sm text-gray-700">ঠিকানা</label>
                                    <input type="text" name="address" value="{{ old('address', $user->address) }}" class="w-full px-4 py-3 rounded-lg border border-gray-100 bg-gray-50 text-sm focus:border-[#FF6A00] outline-none">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-sm text-gray-700">প্রোফাইল ইমেজ</label>
                                    <input type="file" name="profile" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-[#FF6A00] hover:file:bg-orange-100">
                                </div>
                            </div>
                            <button type="submit" class="bg-[#FF6A00] text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-orange-600 transition-all shadow-sm">প্রোফাইল আপডেট করুন</button>
                        </form>
                    </div>
                </div>

                <!-- 5. SECTION: PASSWORD CHANGE (Initially Hidden) -->
                <div id="password-section" class="dashboard-content hidden space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">পাসওয়ার্ড পরিবর্তন করুন</h2>
                        <form action="{{ route('user.password.update') }}" method="POST" class="space-y-5 max-w-2xl">
                            @csrf
                            <div>
                                <label class="text-sm text-gray-800">বর্তমান পাসওয়ার্ড</label>
                                <input type="password" name="current_password" required class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-[#FF6A00] outline-none text-sm">
                            </div>
                            <div>
                                <label class="text-sm text-gray-800">নতুন পাসওয়ার্ড</label>
                                <input type="password" name="password" required class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-[#FF6A00] outline-none text-sm">
                            </div>
                            <div>
                                <label class="text-sm text-gray-800">পাসওয়ার্ড নিশ্চিত করুন</label>
                                <input type="password" name="password_confirmation" required class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-[#FF6A00] outline-none text-sm">
                            </div>
                            <button type="submit" class="bg-[#FF6A00] text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm">আপডেট করুন</button>
                        </form>
                    </div>
                </div>


            </div>
        </div>
    </section>
@endsection
@push('scripts')
    <script>
        function showSection(sectionName, element) {
            const sections = document.querySelectorAll(".dashboard-content");
            sections.forEach((s) => s.classList.add("hidden"));

            const target = document.getElementById(sectionName + "-section");
            if (target) target.classList.remove("hidden");

            const navLinks = document.querySelectorAll(".nav-link");
            navLinks.forEach((link) => {
                link.classList.remove("bg-[#1D2128]", "text-white");
                link.classList.add("text-gray-600", "hover:bg-orange-50");
            });

            element.classList.add("bg-[#1D2128]", "text-white");
            element.classList.remove("text-gray-600", "hover:bg-orange-50");
        }
    </script>
    <script>
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-50, .bg-red-50');

            alerts.forEach(function(alert) {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 500);
            });
        }, 4000);
    </script>
@endpush
