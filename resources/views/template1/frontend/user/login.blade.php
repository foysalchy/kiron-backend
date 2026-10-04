@extends('template1.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.login-meta', ['setup' => $setup])
@endsection
@section('content')
    <section class="container py-6 mx-auto ">
        <!-- Login Card -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-xs max-w-lg mx-auto overflow-hidden">

            <!-- Card Header -->
            <div class="p-6 text-center border-b border-gray-50">
                <h1 class="text-2xl font-black text-gray-900 mb-2">Login</h1>
                <p class="text-gray-700 font-medium">Login to Your Account</p>
            </div>

            <!-- Login Form -->
            <div class="p-6">
                <form action="{{ route('user.login.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Email or Phone Field -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700 ml-1">Email or Phone Number</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="far fa-user text-sm"></i>
                            </span>
                            <input type="text" name="login" value="{{ old('login') }}" placeholder="Email or Phone Number" required
                                class="w-full pl-11 pr-4 py-3 rounded-lg border @error('login') border-red-500 @else border-gray-200 @enderror outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all text-sm">
                        </div>
                        @error('login')
                            <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700 ml-1">Password</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-lock text-sm"></i>
                            </span>
                            <input type="password" name="password" id="password" placeholder="••••••••" required
                                class="w-full pl-11 pr-12 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all text-sm">

                            <!-- Toggle Visibility Button -->
                            <button type="button" onclick="togglePassword()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#FF6A00]">
                                <i id="eye-icon" class="far fa-eye text-[14px]"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="text-left">
                        <a href="{{ route('password.forgot') }}"  style="color:var(--primary-color)"  class="text-sm font-medium hover:underline">Forgot Your Password?</a>
                    </div>

                    <!-- Login Button -->
                    <button type="submit"
                        class="w-full  primary-bg text-primary font-black py-3 rounded-lg shadow-xs text-md transition-all active:scale-[0.98]">
                        Login
                    </button>

                    <!-- Registration Link -->
                    <div class="text-center pt-2">
                        <p class="text-gray-500 font-medium">
                           Don't have an account?<a href="{{url('/register')}}"
                                style="color:var(--primary-color)"   class=" font-medium hover:underline ml-1">Register</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.replace('far', 'fas');
            eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.replace('fas', 'far');
            eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
@endpush
