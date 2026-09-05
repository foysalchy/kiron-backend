@extends('template4.layouts.front')
@section('content')
<section class="container py-6 mx-auto">
    <div class="bg-white rounded-lg border border-gray-200 shadow-xs max-w-lg mx-auto overflow-hidden">

        <div class="p-6 text-center border-b border-gray-50">
            <h1 class="text-2xl font-black text-gray-900 mb-2">Forgot Password</h1>
            <p class="text-gray-700 font-medium">Reset your password using {{ ($smsEnabled ?? ($setup->sms_forget_password ?? true)) ? 'Email or Phone' : 'Email' }}</p>
        </div>

        <div class="p-6">

            @if($smsEnabled ?? ($setup->sms_forget_password ?? true))
            <!-- Method Toggle -->
            <div class="flex gap-2 mb-6 bg-gray-50 p-1 rounded-lg">
                <button type="button" id="method-email-btn" onclick="switchMethod('email')"
                    class="flex-1 py-2 rounded-md text-sm font-bold transition-colors bg-white shadow-sm text-gray-900">
                    <i class="far fa-envelope mr-1"></i> Email
                </button>
                <button type="button" id="method-sms-btn" onclick="switchMethod('sms')"
                    class="flex-1 py-2 rounded-md text-sm font-bold transition-colors text-gray-500">
                    <i class="fas fa-mobile-alt mr-1"></i> Phone (SMS)
                </button>
            </div>
            @endif

            <!-- STEP 1: Identifier Input -->
            <div id="step-1">
                <div class="space-y-2 mb-4">
                    <label id="identifier-label" class="text-sm font-medium text-gray-700 ml-1">Email Address</label>
                    <input type="text" id="identifier-input" placeholder="user@example.com"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[var(--primary-color)] focus:ring-4 focus:ring-green-50 transition-all text-sm">
                    <p id="identifier-error" class="text-red-500 text-xs mt-1 ml-1 hidden"></p>
                </div>

                <button type="button" id="send-otp-btn" onclick="requestOtp()"
                    class="w-full primary-bg text-primary font-black py-3 rounded-lg shadow-xs text-md transition-all active:scale-[0.98]">
                    Send Code
                </button>
            </div>

            <!-- STEP 2: OTP + New Password (initially hidden) -->
            <div id="step-2" class="hidden">
                <p class="text-sm text-gray-500 mb-4">
                    We've sent a 6-digit code to <span id="sent-to-identifier" class="font-bold text-gray-800"></span>.
                    <button type="button" onclick="backToStep1()" class="text-[var(--primary-color)] font-medium hover:underline ml-1">Change</button>
                </p>

                <div class="space-y-2 mb-4">
                    <label class="text-sm font-medium text-gray-700 ml-1">Verification Code</label>
                    <input type="text" id="otp-input" maxlength="6" placeholder="000000"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[var(--primary-color)] focus:ring-4 focus:ring-green-50 transition-all text-sm tracking-widest text-center font-mono text-lg">
                </div>

                <div class="space-y-2 mb-4">
                    <label class="text-sm font-medium text-gray-700 ml-1">New Password</label>
                    <input type="password" id="new-password-input" placeholder="••••••••"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[var(--primary-color)] focus:ring-4 focus:ring-green-50 transition-all text-sm">
                </div>

                <div class="space-y-2 mb-4">
                    <label class="text-sm font-medium text-gray-700 ml-1">Confirm New Password</label>
                    <input type="password" id="confirm-password-input" placeholder="••••••••"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[var(--primary-color)] focus:ring-4 focus:ring-green-50 transition-all text-sm">
                </div>

                <p id="reset-error" class="text-red-500 text-xs mb-3 hidden"></p>

                <button type="button" id="reset-password-btn" onclick="verifyAndReset()"
                    class="w-full primary-bg text-primary font-black py-3 rounded-lg shadow-xs text-md transition-all active:scale-[0.98]">
                    Reset Password
                </button>

                <button type="button" onclick="requestOtp(true)" class="w-full text-center text-sm text-gray-500 hover:text-[var(--primary-color)] mt-3 font-medium">
                    Didn't get the code? Resend
                </button>
            </div>

            <div class="text-center pt-4">
                <a href="{{ route('user.login') }}" class="text-gray-500 text-sm font-medium hover:underline">
                    <i class="fas fa-arrow-left text-xs mr-1"></i> Back to Login
                </a>
            </div>

        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    let currentMethod = 'email';

    function switchMethod(method) {
        currentMethod = method;

        const emailBtn = document.getElementById('method-email-btn');
        const smsBtn = document.getElementById('method-sms-btn');
        const label = document.getElementById('identifier-label');
        const input = document.getElementById('identifier-input');

        if (emailBtn && smsBtn) {
            if (method === 'email') {
                emailBtn.classList.add('bg-white', 'shadow-sm', 'text-gray-900');
                emailBtn.classList.remove('text-gray-500');
                smsBtn.classList.remove('bg-white', 'shadow-sm', 'text-gray-900');
                smsBtn.classList.add('text-gray-500');

                label.innerText = 'Email Address';
                input.type = 'email';
                input.placeholder = 'user@example.com';
            } else {
                smsBtn.classList.add('bg-white', 'shadow-sm', 'text-gray-900');
                smsBtn.classList.remove('text-gray-500');
                emailBtn.classList.remove('bg-white', 'shadow-sm', 'text-gray-900');
                emailBtn.classList.add('text-gray-500');

                label.innerText = 'Phone Number';
                input.type = 'tel';
                input.placeholder = '01XXXXXXXXX';
            }
        }

        input.value = '';
        hideError('identifier-error');
    }

    function showError(elId, message) {
        const el = document.getElementById(elId);
        if (el) {
            el.innerText = message;
            el.classList.remove('hidden');
        }
    }

    function hideError(elId) {
        const el = document.getElementById(elId);
        if (el) {
            el.classList.add('hidden');
        }
    }

    function requestOtp(isResend = false) {
        const identifier = isResend
            ? document.getElementById('sent-to-identifier').innerText
            : document.getElementById('identifier-input').value.trim();

        if (!identifier) {
            showError('identifier-error', 'Please enter your ' + (currentMethod === 'email' ? 'email' : 'phone number'));
            return;
        }

        hideError('identifier-error');

        const btn = document.getElementById('send-otp-btn');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Sending...';

        fetch("{{ route('password.otp.request') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                method: currentMethod,
                identifier: identifier
            })
        })
        .then(async res => {
            const data = await res.json();
            if (res.ok && data.status === 'success') {
                document.getElementById('sent-to-identifier').innerText = identifier;
                document.getElementById('step-1').classList.add('hidden');
                document.getElementById('step-2').classList.remove('hidden');
                if (typeof toastr !== 'undefined') {
                    toastr.success(data.message);
                } else {
                    alert(data.message);
                }
            } else {
                showError('identifier-error', data.message || 'Something went wrong.');
            }
        })
        .catch(() => {
            showError('identifier-error', 'Something went wrong. Please try again.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = originalText;
        });
    }

    function backToStep1() {
        document.getElementById('step-2').classList.add('hidden');
        document.getElementById('step-1').classList.remove('hidden');
    }

    function verifyAndReset() {
        const identifier = document.getElementById('sent-to-identifier').innerText;
        const otp = document.getElementById('otp-input').value.trim();
        const newPassword = document.getElementById('new-password-input').value;
        const confirmPassword = document.getElementById('confirm-password-input').value;

        hideError('reset-error');

        if (otp.length !== 6) {
            showError('reset-error', 'Please enter the 6-digit code.');
            return;
        }
        if (newPassword.length < 8) {
            showError('reset-error', 'Password must be at least 8 characters.');
            return;
        }
        if (newPassword !== confirmPassword) {
            showError('reset-error', 'Passwords do not match.');
            return;
        }

        const btn = document.getElementById('reset-password-btn');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Resetting...';

        fetch("{{ route('password.otp.verify') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                method: currentMethod,
                identifier: identifier,
                otp: otp,
                new_password: newPassword,
                new_password_confirmation: confirmPassword
            })
        })
        .then(async res => {
            const data = await res.json();
            if (res.ok && data.status === 'success') {
                if (typeof toastr !== 'undefined') {
                    toastr.success(data.message);
                }
                setTimeout(() => {
                    window.location.href = "{{ route('user.login') }}";
                }, 1500);
            } else {
                showError('reset-error', data.message || 'Something went wrong.');
            }
        })
        .catch(() => {
            showError('reset-error', 'Something went wrong. Please try again.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = originalText;
        });
    }
</script>
@endpush
