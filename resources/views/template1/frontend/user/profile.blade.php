@extends('template1.layouts.front')

@section('content')
    <section class="container py-6 mx-auto ">

        <!-- Top Action Bar -->
        <div class="flex flex-col md:flex-row items-center justify-between mb-8 gap-4 px-4 lg:px-0">
            <div class="flex items-center gap-4">
                <a href="{{url('dashboard')}}"
                    class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                    <i class="fas fa-arrow-left text-xs"></i>
                    ড্যাশবোর্ডে ফিরুন
                </a>
                <h1 class="text-2xl font-bold text-gray-900">আমার অ্যাকাউন্ট</h1>
            </div>

            <div class="flex gap-2">
                <!-- এডিট মোডে এই বাটনটি দেখা যাবে -->
                <button id="header-cancel-btn"
                    class="hidden px-6 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50 transition-all">
                    বাতিল করুন
                </button>
                <!-- ভিউ মোডে এই বাটনটি দেখা যাবে -->
                <button id="edit-profile-btn"
                    class="bg-[#1D2128] hover:bg-black text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm">
                    প্রোফাইল এডিট করুন
                </button>
            </div>
        </div>

        <div class="max-w-3xl mx-auto space-y-6 px-4 lg:px-0">

            <!-- Profile Information Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">
                <div class="p-6 ">
                    <h2 class="text-xl font-bold text-gray-900">প্রোফাইল তথ্য</h2>
                </div>

                <div class="p-6">
                    <form id="profile-form" class="space-y-6">
                        <!-- Avatar Section -->
                        <div class="flex flex-col items-center">
                            <div class="relative">
                                <div
                                    class="w-32 h-32 rounded-full bg-gray-100 flex items-center justify-center border-4 border-white shadow-xs overflow-hidden">
                                    <i class="fas fa-user text-4xl text-gray-300"></i>
                                </div>
                                <!-- এডিট মোডে ক্যামেরা আইকন -->
                                <label id="camera-icon"
                                    class="hidden absolute bottom-1 right-2 w-8 h-8 bg-[#FF6A00] text-white rounded-full flex items-center justify-center cursor-pointer border-2 border-white shadow-sm hover:bg-orange-600 transition-colors">
                                    <i class="fas fa-camera text-xs"></i>
                                    <input type="file" class="hidden">
                                </label>
                            </div>
                            <p id="avatar-text" class="hidden mt-3 text-sm text-gray-500">ছবি পরিবর্তন করতে ক্যামেরা আইকনে
                                ক্লিক করুন</p>
                        </div>

                        <!-- Form Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-800 ml-1">পূর্ণ নাম</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1 -translate-y-1/2 text-gray-400"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-user absolute left-3 top-3 h-4 w-4 text-gray-400">
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg></span>
                                    <input type="text" value="John Doe" disabled
                                        class="profile-input w-full pl-11 pr-4 py-3 rounded-lg border border-gray-200 text-gray-600 text-sm outline-none transition-all disabled:cursor-not-allowed">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-800 ml-1">ইমেইল ঠিকানা</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1 -translate-y-1/2 text-gray-400"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-mail absolute left-3 top-3 h-4 w-4 text-gray-400">
                                            <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                        </svg></span>
                                    <input type="email" value="user@example.com" disabled
                                        class="profile-input w-full pl-11 pr-4 py-3 rounded-lg border border-gray-200 text-gray-600 text-sm outline-none transition-all disabled:cursor-not-allowed">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-800 ml-1">মোবাইল নম্বর</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1 -translate-y-1/2 text-gray-400"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-phone absolute left-3 top-3 h-4 w-4 text-gray-400">
                                            <path
                                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                            </path>
                                        </svg></span>
                                    <input type="tel" placeholder="আপনার মোবাইল নম্বর" disabled
                                        class="profile-input w-full pl-11 pr-4 py-3 rounded-lg border border-gray-200 text-gray-500 text-sm outline-none transition-all disabled:cursor-not-allowed">
                                </div>
                            </div>

                            <div class="md:col-span-2 space-y-2">
                                <label class="text-sm font-medium text-gray-800 ml-1">ঠিকানা</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1 text-gray-400"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-map-pin absolute left-3 top-3 h-4 w-4 text-gray-400">
                                            <path
                                                d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0">
                                            </path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg></span>
                                    <textarea placeholder="আপনার সম্পূর্ণ ঠিকানা" rows="3" disabled
                                        class="profile-input w-full pl-11 pr-4 py-3 rounded-lg border border-gray-200 text-gray-500 text-sm outline-none transition-all disabled:cursor-not-allowed resize-none"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- এডিট মোডে নিচের বাটনগুলো -->
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
                <div class="p-6 border-b border-gray-50">
                    <h2 class="text-xl font-bold text-gray-900">অ্যাকাউন্ট সেটিংস</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div
                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50/30 transition-colors gap-4">
                        <div>
                            <h4 class="font-medium text-gray-800">পাসওয়ার্ড পরিবর্তন</h4>
                            <p class="text-sm text-gray-500">আপনার অ্যাকাউন্টের পাসওয়ার্ড পরিবর্তন করুন</p>
                        </div>
                        <button
                            class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:border-[#FF6A00] hover:text-[#FF6A00] transition-all whitespace-nowrap">পরিবর্তন
                            করুন</button>
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
            const settingsCard = document.getElementById('settings-card');

            const toggleEditMode = (isEditing) => {
                if (isEditing) {
                    inputs.forEach(input => {
                        input.disabled = false;
                        input.classList.remove('bg-gray-50/50', 'text-gray-500', 'cursor-not-allowed');
                        input.classList.add('bg-white', 'text-gray-900', 'border-[#FF6A00]');
                    });
                    editBtn.classList.add('hidden');
                    headerCancelBtn.classList.remove('hidden');
                    cameraIcon.classList.remove('hidden');
                    avatarText.classList.remove('hidden');
                    formActions.classList.remove('hidden');
                    settingsCard.classList.add('opacity-50', 'pointer-events-none');
                } else {
                    inputs.forEach(input => {
                        input.disabled = true;
                        input.classList.add('bg-gray-50/50', 'text-gray-500', 'cursor-not-allowed');
                        input.classList.remove('bg-white', 'text-gray-900', 'border-[#FF6A00]');
                    });
                    editBtn.classList.remove('hidden');
                    headerCancelBtn.classList.add('hidden');
                    cameraIcon.classList.add('hidden');
                    avatarText.classList.add('hidden');
                    formActions.classList.add('hidden');
                    settingsCard.classList.remove('opacity-50', 'pointer-events-none');
                }
            };

            editBtn.addEventListener('click', () => toggleEditMode(true));
            headerCancelBtn.addEventListener('click', () => toggleEditMode(false));
            formCancelBtn.addEventListener('click', () => toggleEditMode(false));

            document.getElementById('profile-form').addEventListener('submit', (e) => {
                e.preventDefault();
                alert("আপনার প্রোফাইল সফলভাবে আপডেট করা হয়েছে!");
                toggleEditMode(false);
            });
        });
    </script>
@endpush
