@extends('saas.partner.layout')

@section('title', 'Partner Forgot Password')

@section('content')
<div class="max-w-md mx-auto my-8 bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
    <div class="text-center mb-6">
        <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-3 text-xl font-bold border border-brand-100">
            <i class="fa-solid fa-key"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Forgot Password?</h2>
        <p class="text-sm text-slate-500 mt-1">Choose how you'd like to receive your 6-digit verification code</p>
    </div>

    <form action="{{ route('partner.password.request-otp') }}" method="POST" id="forgotPasswordForm" class="space-y-4">
        @csrf

        <!-- Method selection -->
        <div class="space-y-3 mb-4">
            <label id="methodEmailCard" class="flex items-center gap-3 border-2 border-brand-600 bg-brand-50/50 rounded-xl p-4 cursor-pointer transition-all">
                <input type="radio" name="method" value="email" checked onchange="toggleResetMethod('email')" class="accent-[#13565e] w-4 h-4 text-brand-600 focus:ring-brand-500">
                <div class="w-8 h-8 rounded-lg bg-brand-100/70 text-brand-700 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Email Address</p>
                    <p class="text-xs text-slate-500">Receive verification OTP via email</p>
                </div>
            </label>

            <label id="methodSmsCard" class="flex items-center gap-3 border border-slate-200 hover:border-slate-300 rounded-xl p-4 cursor-pointer transition-all">
                <input type="radio" name="method" value="sms" onchange="toggleResetMethod('sms')" class="accent-[#13565e] w-4 h-4 text-brand-600 focus:ring-brand-500">
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-comment-sms"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">SMS / Phone</p>
                    <p class="text-xs text-slate-500">Receive verification OTP via text message</p>
                </div>
            </label>
        </div>

        <div>
            <label id="identifierLabel" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Your Email Address *
            </label>
            <div class="relative">
                <div id="identifierIcon" class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <input type="text" name="identifier" id="identifierInput" value="{{ old('identifier') }}" required autofocus
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                    placeholder="partner@example.com">
            </div>
            @error('identifier')
                <p class="text-xs text-rose-500 mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md shadow-brand-500/20 transition duration-150">
            Send Verification Code
        </button>
    </form>

    <div class="mt-6 pt-6 border-t border-slate-100 text-center text-sm text-slate-600">
        Remembered your password? 
        <a href="{{ route('partner.login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Back to Login</a>
    </div>
</div>

<script>
function toggleResetMethod(method) {
    const emailCard = document.getElementById('methodEmailCard');
    const smsCard = document.getElementById('methodSmsCard');
    const label = document.getElementById('identifierLabel');
    const input = document.getElementById('identifierInput');
    const icon = document.getElementById('identifierIcon');

    if (method === 'email') {
        emailCard.className = 'flex items-center gap-3 border-2 border-brand-600 bg-brand-50/50 rounded-xl p-4 cursor-pointer transition-all';
        smsCard.className = 'flex items-center gap-3 border border-slate-200 hover:border-slate-300 rounded-xl p-4 cursor-pointer transition-all';
        label.innerText = 'Your Email Address *';
        input.placeholder = 'partner@example.com';
        input.type = 'email';
        icon.innerHTML = '<i class="fa-solid fa-envelope"></i>';
    } else {
        smsCard.className = 'flex items-center gap-3 border-2 border-brand-600 bg-brand-50/50 rounded-xl p-4 cursor-pointer transition-all';
        emailCard.className = 'flex items-center gap-3 border border-slate-200 hover:border-slate-300 rounded-xl p-4 cursor-pointer transition-all';
        label.innerText = 'Your Phone Number *';
        input.placeholder = '01700000000';
        input.type = 'tel';
        icon.innerHTML = '<i class="fa-solid fa-phone"></i>';
    }
}
</script>
@endsection
