@extends('template1.layouts.front')

@section('content')
    <section class="container py-6 mx-auto">

        <!-- Top Action Bar -->
        <div class="flex flex-col md:flex-row items-center justify-between mb-8 gap-4 px-4 lg:px-0">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.dashboard') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                    <i class="fas fa-arrow-left text-xs"></i>
                    ড্যাশবোর্ডে ফিরুন
                </a>
                <h1 class="text-2xl font-bold text-gray-900">আমার অ্যাকাউন্ট</h1>
            </div>

            <div class="flex gap-2">
                <button id="header-cancel-btn"
                    class="hidden px-6 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50 transition-all">
                    বাতিল করুন
                </button>
                <button id="edit-profile-btn"
                    class="bg-[#1D2128] hover:bg-black text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm">
                    প্রোফাইল এডিট করুন
                </button>
            </div>
        </div>

        <div class="max-w-3xl mx-auto space-y-6 px-4 lg:px-0">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Profile Information Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-900">প্রোফাইল তথ্য</h2>
                </div>

                <div class="p-6">
                    <form id="profile-form" action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <!-- Avatar Section -->
                        <div class="flex flex-col items-center">
                            <div class="relative">
                                <div class="w-32 h-32 rounded-full bg-gray-100 flex items-center justify-center border-4 border-white shadow-xs overflow-hidden">
                                    @if($user->profile)
                                        <img src="{{ $user->profile_url }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-user text-4xl text-gray-300"></i>
                                    @endif
                                </div>
                                <label id="camera-icon"
                                    class="hidden absolute bottom-1 right-2 w-8 h-8 bg-[#FF6A00] text-white rounded-full flex items-center justify-center cursor-pointer border-2 border-white shadow-sm hover:bg-orange-600 transition-colors">
                                    <i class="fas fa-camera text-xs"></i>
                                    <input type="file" name="profile" class="hidden">
                                </label>
                            </div>
                            <p id="avatar-text" class="hidden mt-3 text-sm text-gray-500">ছবি পরিবর্তন করতে ক্যামেরা আইকনে ক্লিক করুন</p>
                        </div>

                        <!-- Form Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Full Name -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-800 ml-1">পূর্ণ নাম</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user h-4 w-4">
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                    </span>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" disabled
                                        class="profile-input w-full pl-11 pr-4 py-3 rounded-lg border border-gray-200 text-gray-600 text-sm outline-none transition-all">
                                </div>
                            </div>

                            <!-- Email Address -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-800 ml-1">ইমেইল ঠিকানা</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail h-4 w-4">
                                            <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                        </svg>
                                    </span>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" disabled
                                        class="profile-input w-full pl-11 pr-4 py-3 rounded-lg border border-gray-200 text-gray-600 text-sm outline-none transition-all">
                                </div>
                            </div>

                            <!-- Mobile Number -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-800 ml-1">মোবাইল নম্বর</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone h-4 w-4">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                    </span>
                                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="আপনার মোবাইল নম্বর" disabled
                                        class="profile-input w-full pl-11 pr-4 py-3 rounded-lg border border-gray-200 text-gray-600 text-sm outline-none transition-all ">
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-sm font-medium text-gray-800 ml-1">ঠিকানা</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-4 text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin h-4 w-4">
                                            <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                    </span>
                                    <textarea name="address" placeholder="আপনার সম্পূর্ণ ঠিকানা" rows="3" disabled
                                        class="profile-input w-full pl-11 pr-4 py-3 rounded-lg border border-gray-200 text-gray-600 text-sm outline-none transition-all resize-none">{{ old('address', $user->address) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div id="form-actions" class="hidden flex justify-end gap-3 pt-4 border-t border-gray-50">
                            <button type="button" id="form-cancel-btn"
                                class="px-6 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50 transition-all">
                                বাতিল করুন
                            </button>
                            <button type="submit"
                                class="flex items-center gap-2 px-6 py-2 bg-[#FF6A00] text-white rounded-lg text-sm font-medium hover:bg-orange-600 transition-all">
                                <i class="fas fa-save text-xs"></i>
                                পরিবর্তন সেভ করুন
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Settings Card -->
            <div id="settings-card"
                class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden transition-all">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-900">অ্যাকাউন্ট সেটিংস</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div
                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50/30 transition-colors gap-4">
                        <div>
                            <h4 class="font-medium text-gray-800">পাসওয়ার্ড পরিবর্তন</h4>
                            <p class="text-sm text-gray-500">আপনার অ্যাকাউন্টের পাসওয়ার্ড পরিবর্তন করুন</p>
                        </div>
                        <a href="{{ route('user.dashboard') }}"
                            class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:border-[#FF6A00] hover:text-[#FF6A00] transition-all whitespace-nowrap">পরিবর্তন
                            করুন
                        </a>
                    </div>
                    <div
                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50/30 transition-colors gap-4">
                        <div>
                            <h4 class="font-medium text-gray-800">ইমেইল নোটিফিকেশন</h4>
                            <p class="text-sm text-gray-500">অর্ডার আপডেট এবং প্রমোশনাল ইমেইল পান</p>
                        </div>
                        <button
                            class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:border-[#FF6A00] hover:text-[#FF6A00] transition-all whitespace-nowrap">সেটিংস</button>
                    </div>
                    <div
                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50/30 transition-colors gap-4">
                        <div>
                            <h4 class="font-medium text-gray-800">প্রাইভেসি সেটিংস</h4>
                            <p class="text-sm text-gray-500">আপনার ডেটা এবং প্রাইভেসি নিয়ন্ত্রণ করুন</p>
                        </div>
                        <button
                            class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:border-[#FF6A00] hover:text-[#FF6A00] transition-all whitespace-nowrap">ম্যানেজ
                            করুন</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editBtn = document.getElementById('edit-profile-btn');
            const headerCancelBtn = document.getElementById('header-cancel-btn');
            const formCancelBtn = document.getElementById('form-cancel-btn');
            const inputs = document.querySelectorAll('.profile-input');
            const cameraIcon = document.getElementById('camera-icon');
            const avatarText = document.getElementById('avatar-text');
            const formActions = document.getElementById('form-actions');

            const toggleEditMode = (isEditing) => {
                inputs.forEach(input => {
                    input.disabled = !isEditing;
                    if (isEditing) {
                        input.classList.add('border-[#FF6A00]');
                    } else {
                        input.classList.remove('border-[#FF6A00]');
                    }
                });
                editBtn.classList.toggle('hidden', isEditing);
                headerCancelBtn.classList.toggle('hidden', !isEditing);
                cameraIcon.classList.toggle('hidden', !isEditing);
                avatarText.classList.toggle('hidden', !isEditing);
                formActions.classList.toggle('hidden', !isEditing);
            };

            editBtn.addEventListener('click', () => toggleEditMode(true));
            headerCancelBtn.addEventListener('click', () => toggleEditMode(false));
            formCancelBtn.addEventListener('click', () => toggleEditMode(false));

            // Submit ইভেন্ট থেকে e.preventDefault() সরিয়ে ফেলা হয়েছে যাতে লারাভেল ফর্মটি রিসিভ করতে পারে
        });
    </script>
@endpush
