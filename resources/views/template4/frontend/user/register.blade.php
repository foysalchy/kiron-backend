@extends('template4.layouts.front')

@section('content')
    <section class="container py-6 mx-auto">
        <div class="max-w-xl mx-auto">
            <!-- Registration Card -->
            <div class="bg-white rounded-lg   shadow-xs overflow-hidden">

                <!-- Header -->
                <div class="p-8 text-center border-b border-gray-50">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Register</h1>
                    <p class="text-gray-500 font-medium">Create Your New Account</p>
                </div>

                <!-- Form -->
                <div class="p-8">
                    <form action="{{ route('user.register.store') }}" method="POST" class="space-y-5">
                        @csrf

                        <!-- Full Name -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 ml-1">Full Name</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="far fa-user text-sm"></i>
                                </span>
                                <input type="text" name="name" value="{{ old('name') }}"
                                    placeholder="Enter Your Full Name" required
                                    class="w-full pl-11 pr-4 py-3 rounded-lg border @error('name') border-red-500 @else border-gray-200 @enderror outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all text-sm">
                            </div>
                            @error('name')
                                <span class="text-red-500 text-xs ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Email Address -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 ml-1">Email Address</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="far fa-envelope text-sm"></i>
                                </span>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    placeholder="user@example.com" required
                                    class="w-full pl-11 pr-4 py-3 rounded-lg border @error('email') border-red-500 @else border-gray-200 @enderror outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all text-sm">
                            </div>
                            @error('email')
                                <span class="text-red-500 text-xs ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Mobile Number -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 ml-1">Pnone Number</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-phone-alt text-sm"></i>
                                </span>
                                <input type="tel" name="phone" value="{{ old('phone') }}"
                                    placeholder="Enter Your Phone Number.." required
                                    class="w-full pl-11 pr-4 py-3 rounded-lg border @error('phone') border-red-500 @else border-gray-200 @enderror outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all text-sm">
                            </div>
                            @error('phone')
                                <span class="text-red-500 text-xs ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 ml-1">Address</label>
                            <div class="relative">
                                <span class="absolute left-4 top-4 text-gray-400">
                                    <i class="fas fa-map-marker-alt text-sm"></i>
                                </span>
                                <textarea name="address" placeholder="Enter Your Full Address" rows="3" required
                                    class="w-full pl-11 pr-4 py-3 rounded-lg border @error('address') border-red-500 @else border-gray-200 @enderror outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all text-sm">{{ old('address') }}</textarea>
                            </div>
                            @error('address')
                                <span class="text-red-500 text-xs ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 ml-1">Password</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-lock text-sm"></i>
                                </span>
                                <input type="password" name="password" id="password" placeholder="••••••••" required
                                    class="w-full pl-11 pr-12 py-3 rounded-lg border @error('password') border-red-500 @else border-gray-200 @enderror outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all text-sm">
                                <button type="button" onclick="togglePassword('password', this)"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#66267b]">
                                    <i class="far fa-eye text-[14px]"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="text-red-500 text-xs ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 ml-1">Confirm Your Password</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-lock text-sm"></i>
                                </span>
                                {{-- পাসওয়ার্ড কনফার্মেশনের জন্য নাম অবশ্যই password_confirmation হতে হবে --}}
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    placeholder="Re-enter password" required
                                    class="w-full pl-11 pr-12 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all text-sm">
                                <button type="button" onclick="togglePassword('password_confirmation', this)"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#66267b]">
                                    <i class="far fa-eye text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="flex items-start gap-3 py-2">
                            <input type="checkbox" name="terms" id="terms" required
                                class="mt-1 w-4 h-4 accent-[#66267b] cursor-pointer">
                            <label for="terms" class="text-sm font-medium text-gray-600 cursor-pointer">
                                I accept the <a href=" "
                                    class="text-[#66267b] hover:underline">Terms and
                                    Conditions</a> and <a href=" "
                                    class="text-[#66267b] hover:underline">Privacy
                                    Policy</a>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full bg-[#66267b] text-primary font-black py-3 text-md rounded-lg shadow-xs shadow-orange-100 transition-all active:scale-[0.98]">
                            Register
                        </button>

                        <!-- Login Link -->
                        <div class="text-center pt-4 text-sm">
                            <p class="text-gray-500 font-medium">
                                Already have an account? <a href="{{ route('user.login') }}"
                                    class="text-[#66267b] font-medium hover:underline ml-1">Login</a>
                            </p>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('far', 'fa-eye');
                icon.classList.add('fas', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fas', 'fa-eye-slash');
                icon.classList.add('far', 'fa-eye');
            }
        }
    </script>
@endpush
