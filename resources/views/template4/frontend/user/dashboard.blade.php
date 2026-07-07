@extends('template4.layouts.front')

@section('content')
    <section class="container py-6 mx-auto px-4 lg:px-0">
        <!-- Dashboard Header -->
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-black text-[#1D2128]">Dashboard</h1>
            <form action="{{ route('user.logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="px-5 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                    Logout
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 md:gap-8">
            <!-- Left Sidebar: Persistent Profile & Nav -->
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-4 lg:p-6 text-center">
                    <!-- Avatar -->
                    <div class="flex justify-center mb-4">
                        <div
                            class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center border-4 border-white shadow-sm overflow-hidden">
                            @if ($user->profile)
                                <img src="{{ $user->profile_url }}" alt="profile image" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-user text-3xl text-gray-300"></i>
                            @endif
                        </div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500 font-medium">{{ $user->email }}</p>

                    <!-- Sidebar Menu -->
                    <nav class="mt-4 lg:mt-8 flex lg:flex-col gap-2 overflow-x-auto no-scrollbar lg:overflow-visible pb-1 lg:pb-0"
                        id="dashboard-nav">
                        <button onclick="showSection('overview', this)"
                            class="nav-link shrink-0 lg:w-full flex items-center gap-3 px-3 lg:px-4 py-2.5 lg:py-3 bg-[#1D2128] text-primary rounded-xl text-sm font-semibold transition-all">
                            <i class="far fa-user w-5 text-center"></i> Overview
                        </button>
                        <button onclick="showSection('orders', this)"
                            class="nav-link shrink-0 lg:w-full flex items-center gap-3 px-3 lg:px-4 py-2.5 lg:py-3 text-gray-600 hover:bg-orange-50 hover:text-[var(--primary-color)] rounded-xl text-sm font-semibold transition-all">
                            <i class="fas fa-shopping-bag w-5 text-center"></i> My Order
                        </button>
                        <button onclick="showSection('wishlist', this)"
                            class="nav-link shrink-0 lg:w-full flex items-center gap-3 px-3 lg:px-4 py-2.5 lg:py-3 text-gray-600 hover:bg-orange-50 hover:text-[var(--primary-color)] rounded-xl text-sm font-semibold transition-all">
                            <i class="far fa-heart w-5 text-center"></i> Wishlist
                        </button>
                        <button onclick="showSection('edit', this)"
                            class="nav-link shrink-0 lg:w-full flex items-center gap-3 px-3 lg:px-4 py-2.5 lg:py-3 text-gray-600 hover:bg-orange-50 hover:text-[var(--primary-color)] rounded-xl text-sm font-semibold transition-all">
                            <i class="far fa-edit w-5 text-center"></i> Profile update
                        </button>
                        <button onclick="showSection('password', this)"
                            class="nav-link shrink-0 lg:w-full flex items-center gap-3 px-3 lg:px-4 py-2.5 lg:py-3 text-gray-600 hover:bg-orange-50 hover:text-[var(--primary-color)] rounded-xl text-sm font-semibold transition-all">
                            <i class="fas fa-lock w-5 text-center"></i> Password Change
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Right Side Content Area -->
            <div class="lg:col-span-3">
                @if (session('success'))
                    <div
                        class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg shadow-xs flex items-center gap-3">
                        <i class="fas fa-check-circle text-lg"></i>
                        <span class="font-bold">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div
                        class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg shadow-xs flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-lg"></i>
                        <span class="font-bold">{{ session('error') }}</span>
                    </div>
                @endif
                <!-- 1. SECTION: OVERVIEW (Default) -->
                <div id="overview-section" class="dashboard-content space-y-6">
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-6">
                        <div
                            class="bg-white p-4 md:p-6 rounded-lg border border-gray-200 shadow-xs flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-1">Total Order</p>
                                <h4 class="text-2xl font-semibold text-gray-900">{{ $totalOrders ?? 0 }}</h4>
                            </div>
                            <i class="fas fa-shopping-bag h-8 w-8 text-orange-500 text-2xl"></i>
                        </div>
                        <div
                            class="bg-white p-4 md:p-6 rounded-lg border border-gray-200 shadow-xs flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-1">Total Cost</p>
                                <h4 class="text-2xl font-semibold text-gray-900">{{ $setup->currency }}
                                    {{ number_format($totalSpent ?? 0) }}</h4>
                            </div>
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
                        <div
                            class="bg-white p-4 md:p-6 rounded-lg border border-gray-200 shadow-xs flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-1">Wishlist</p>
                                <h4 class="text-2xl font-semibold text-gray-900 wishlist-count-val">
                                    {{ $wishlistCount ?? 0 }}</h4>
                            </div>
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
                                Recent Order
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            @forelse($recentOrders ?? [] as $order)
                                <div
                                    class="flex flex-col md:flex-row items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-all gap-4">
                                    <div class="text-left">
                                        <p class="font-medium text-gray-900">{{ $order->order_number }}</p>
                                        <p class="text-sm text-gray-500">{{ $order->created_at->format('Y-m-d') }}</p>
                                    </div>
                                    <span
                                        class="px-3 py-1 {{ $order->status_color }} text-[10px] font-bold rounded-full uppercase">
                                        {{ $order->status_label }}
                                    </span>
                                    <div class="flex items-center gap-6">
                                        <p class="font-semibold text-gray-900">{{ $setup->currency }}
                                            {{ number_format($order->grand_total) }}
                                        </p>
                                        <a href="{{ route('user.order.details', $order->id) }}"
                                            class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:text-[var(--primary-color)]">View</a>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-gray-500 py-6">You have no orders!!!</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- 2. SECTION: ALL MY ORDERS  -->
                <div id="orders-section" class="dashboard-content hidden space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-xl md:text-2xl font-bold text-gray-900">
                                My Orders
                            </h2>
                        </div>
                        <div class="p-6 space-y-8">
                            @forelse($allOrders as $order)
                                <div class="border border-gray-200 rounded-lg p-6 space-y-6">
                                    <div
                                        class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                        <div>
                                            <h4 class="font-semibold text-md text-gray-900">
                                                #{{ $order->order_no ?? $order->id }}
                                            </h4>
                                            <p class="text-sm text-gray-700 font-medium">
                                                Order Date: {{ $order->created_at->format('d M, Y') }}
                                            </p>
                                        </div>

                                        <div class="flex flex-col md:items-end gap-2 w-full md:w-auto">
                                            <span
                                                class="px-3 py-1 {{ $order->status_color }} text-[10px] font-bold rounded-full uppercase">
                                                {{ $order->status_label }}
                                            </span>

                                            <span
                                                class="px-3 py-1 {{ $order->payment_status_color }} text-[10px] font-bold rounded-full flex items-center gap-1.5 w-fit uppercase border border-current/10">
                                                {{ $order->payment_status_label }}
                                            </span>

                                            <p class="text-lg font-bold text-gray-900 leading-none mt-1">
                                                {{ $setup->currency }} {{ number_format($order->grand_total) }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Items inside this order -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        @foreach ($order->orderDetails as $item)
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shrink-0 border border-gray-100 overflow-hidden">
                                                    @if ($item->product && $item->product->thumbnail)
                                                        <img src="{{ $item->product->thumbnail_url }}"
                                                            onerror="this.src='{{ $item->product->thumbnail_url }}'"
                                                            class="w-full h-full object-cover">
                                                    @endif
                                                </div>
                                                <div class="text-sm">
                                                    <p class="text-gray-800 leading-tight">
                                                        {{ $item->product->title ?? 'Product Deleted' }}</p>
                                                    <p class="text-gray-500 font-medium">
                                                        {{ $setup->currency }} {{ number_format($item->price) }} x
                                                        {{ $item->quantity }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <!-- View Details Button -->
                                        <a href="{{ route('user.order.details', $order->id) }}"
                                            class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-600 hover:text-[var(--primary-color)] flex items-center gap-2">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>

                                        <!-- Invoice Button -->
                                        <a href="{{ route('order.invoice', $order->id) }}"
                                            class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-600 hover:text-[var(--primary-color)] flex items-center gap-2">
                                            <i class="fas fa-download"></i> Invoice
                                        </a>

                                        <!-- Review Button (Shown only if Delivered) -->
                                        @if ($order->status === \App\Enums\Status::Delivered->value)
                                            <a href="{{ route('user.order.details', $order->id) }}"
                                                class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-600 hover:text-[var(--primary-color)] flex items-center gap-2">
                                                <i class="fa-solid fa-star-half-stroke"></i> Review
                                            </a>
                                        @endif

                                        @if (
                                            $order->payment_status === \App\Models\Order::PAYMENT_UNPAID &&
                                                $order->status !== \App\Enums\Status::Cancelled->value)
                                            <button type="button"
                                                onclick="openPaymentModal('{{ $order->id }}', '{{ $order->grand_total }}')"
                                                class="px-4 py-2 primary-bg hover:bg-[#e65f00] text-primary border border-[var(--primary-color)] rounded-lg text-sm font-bold flex items-center gap-2">
                                                <i class="fa-brands fa-amazon-pay"></i> Pay Now
                                            </button>
                                        @elseif($order->payment_status === \App\Models\Order::PAYMENT_PENDING)
                                            <span
                                                class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg text-xs font-bold flex items-center gap-2 border border-yellow-200">
                                                <i class="fas fa-history"></i> Verification Pending
                                            </span>
                                        @elseif($order->payment_status === \App\Models\Order::PAYMENT_PAID)
                                            <span
                                                class="px-4 py-2 bg-green-100 text-green-700 rounded-lg text-xs font-bold flex items-center gap-2">
                                                <i class="fas fa-check-circle"></i> Paid
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10">
                                    <p class="text-gray-500">No Orders Found</p>
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
                            <h2 class="text-xl font-bold text-gray-900">My Wishlist</h2>
                        </div>

                        <div class="p-6">
                            <!-- Grid -->
                            @if ($wishlistItems->count() > 0)
                                <!-- Wishlist Grid (Calling Product Card Component) -->
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                                    @foreach ($wishlistItems as $item)
                                        <x-template1.product-card :product="$item->product" />
                                    @endforeach
                                </div>
                            @else
                                <!-- Empty State -->
                                <div class="text-center py-24 bg-white rounded-3xl border border-dashed border-gray-200">
                                    <div
                                        class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                        <i class="far fa-heart text-3xl text-gray-300"></i>
                                    </div>
                                    <h2 class="text-xl font-bold text-gray-800">Your wishlist is currently empty.</h2>
                                    <a href="{{ route('shop.index') }}"
                                        class="inline-block mt-8 primary-bg text-primary px-10 py-3 rounded-xl font-bold shadow-lg hover:primary-bg transition-all">শপিং
                                        Start Shopping</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 4. SECTION: PROFILE EDIT -->
                <div id="edit-section" class="dashboard-content hidden space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Update Your Profile</h2>

                        <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data"
                            class="space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- নাম -->
                                <div>
                                    <label class="text-sm font-bold text-gray-700">Name</label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                        class="w-full px-4 py-3 rounded-lg border @error('name') border-red-500 @else border-gray-100 @enderror bg-gray-50 text-sm focus:border-[#016738] outline-none">
                                    @error('name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- ইমেইল -->
                                <div>
                                    <label class="text-sm font-bold text-gray-700">Email</label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                        class="w-full px-4 py-3 rounded-lg border @error('email') border-red-500 @else border-gray-100 @enderror bg-gray-50 text-sm focus:border-[#016738] outline-none">
                                    @error('email')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- ফোন -->
                                <div>
                                    <label class="text-sm font-bold text-gray-700">Phone</label>
                                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                                        class="w-full px-4 py-3 rounded-lg border @error('phone') border-red-500 @else border-gray-100 @enderror bg-gray-50 text-sm focus:border-[#016738] outline-none">
                                    @error('phone')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="text-sm font-bold text-gray-700">Address</label>
                                    <input type="text" name="address" value="{{ old('address', $user->address) }}"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-100 bg-gray-50 text-sm focus:border-[var(--primary-color)] outline-none">
                                </div>

                                <div class="md:col-span-2 flex items-center gap-6">
                                    <div class="shrink-0">
                                        <img id="image-preview"
                                            src="{{ $user->profile_url ?? '' }}"
                                            class="h-16 w-16 object-cover rounded-full border-2 border-orange-100 shadow-sm">
                                    </div>
                                    <div class="flex-1">
                                        <label class="text-sm font-bold text-gray-700">Change Profile Picture</label>
                                        <input type="file" name="profile" id="profile-input"
                                            onchange="previewImage(this)"
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-[var(--primary-color)] hover:file:bg-orange-100">
                                        @error('profile')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                class="primary-bg text-primary px-8 py-3 rounded-lg text-sm font-bold hover:primary-bg transition-all shadow-md">
                                Save information
                            </button>
                        </form>
                    </div>
                </div>
                <!-- 5. SECTION: PASSWORD CHANGE -->
                <div id="password-section"
                    class="dashboard-content {{ session('active_tab') == 'password' ? '' : 'hidden' }} space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Change Your Password</h2>
                        <form action="{{ route('user.password.update') }}" method="POST" class="space-y-5 max-w-2xl">
                            @csrf
                            <div>
                                <label class="text-sm text-gray-800">Current Password</label>
                                <input type="password" name="current_password" required
                                    class="w-full px-4 py-3 rounded-lg border @error('current_password') border-red-500 @else border-gray-200 @enderror focus:border-[#016738] outline-none text-sm">
                                @error('current_password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="text-sm text-gray-800">New Password</label>
                                <input type="password" name="password" required
                                    class="w-full px-4 py-3 rounded-lg border @error('password') border-red-500 @else border-gray-200 @enderror focus:border-[var(--primary-color)] outline-none text-sm">
                                @error('password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="text-sm text-gray-800">Confirm Your Password</label>
                                <input type="password" name="password_confirmation" required
                                    class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-[var(--primary-color)] outline-none text-sm">
                            </div>
                            <button type="submit"
                                class="primary-bg text-primary px-6 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm">Update</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <x-template1.payment-modal :methods="$paymentMethods" :currency="$setup->currency" />
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
                link.classList.remove("bg-[#1D2128]", "text-primary");
                link.classList.add("text-gray-600", "hover:bg-orange-50");
            });

            element.classList.add("bg-[#1D2128]", "text-primary");
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
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('image-preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            @if (session('active_tab') == 'password' || $errors->has('password') || $errors->has('current_password'))
                const passwordBtn = document.querySelector('button[onclick*="password"]');
                showSection('password', passwordBtn);
            @endif
        });

        function showSection(sectionName, element) {
            const sections = document.querySelectorAll(".dashboard-content");
            sections.forEach((s) => s.classList.add("hidden"));

            const target = document.getElementById(sectionName + "-section");
            if (target) target.classList.remove("hidden");

            const navLinks = document.querySelectorAll(".nav-link, .nav-link-custom");
            navLinks.forEach((link) => {
                link.classList.remove("bg-[#1D2128]", "text-primary");
                link.classList.add("text-gray-600", "hover:bg-orange-50");
            });

            element.classList.add("bg-[#1D2128]", "text-primary");
            element.classList.remove("text-gray-600", "hover:bg-orange-50");
        }
    </script>
    <script>
        // ১. ভ্যালিডেশন এররগুলো দেখানোর জন্য (যেমন: Transaction ID Already Used)
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error("{{ $error }}", "Error", {
                    positionClass: "toast-top-right",
                    progressBar: true,
                    timeOut: 5000
                });
            @endforeach
        @endif

        // ২. সাকসেস মেসেজ দেখানোর জন্য
        @if (session('success'))
            toastr.success("{{ session('success') }}", "Success");
        @endif

        // ৩. জেনারেল এরর মেসেজ দেখানোর জন্য
        @if (session('error'))
            toastr.error("{{ session('error') }}", "Error");
        @endif
    </script>
@endpush
