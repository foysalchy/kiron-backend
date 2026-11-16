<!-- Checkout SMS / OTP Verification Modal -->
<div id="checkout-otp-modal" class="fixed inset-0 z-[99999] hidden flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity duration-300">
    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 transform transition-all duration-300 scale-95 opacity-0" id="checkout-otp-modal-container">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-amber-600 px-6 py-5 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-lg">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold leading-tight">অর্ডার ভেরিফিকেশন</h3>
                    <p class="text-xs text-orange-100">নিরাপদ অর্ডারের জন্য ওটিপি নিশ্চিত করুন</p>
                </div>
            </div>
            <button type="button" onclick="closeCheckoutOtpModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <div class="text-center mb-6">
                <p class="text-sm text-gray-600 leading-relaxed mb-1">
                    আপনার মোবাইল নাম্বারে <span id="checkout-otp-phone-display" class="font-bold text-gray-800"></span> একটি ৪ ডিজিটের ওটিপি পাঠানো হয়েছে।
                </p>
                <p class="text-xs text-gray-400">মেয়াদ শেষ হওয়ার আগেই কোডটি নিচে লিখুন</p>
            </div>

            <!-- Error Alert -->
            <div id="checkout-otp-error" class="hidden mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-xs font-semibold flex items-center gap-2">
                <i class="fas fa-exclamation-circle shrink-0"></i>
                <span id="checkout-otp-error-msg"></span>
            </div>

            <!-- Success Alert -->
            <div id="checkout-otp-success" class="hidden mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-xs font-semibold flex items-center gap-2">
                <i class="fas fa-check-circle shrink-0"></i>
                <span id="checkout-otp-success-msg"></span>
            </div>

            <!-- OTP Input Form -->
            <form id="checkout-otp-form" onsubmit="submitCheckoutOtp(event)">
                <input type="hidden" id="checkout-otp-order-id" value="">

                <div class="flex justify-center gap-3 mb-6" id="otp-inputs-wrapper">
                    <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-digit w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold rounded-xl border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 outline-none transition-all" autofocus>
                    <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-digit w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold rounded-xl border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 outline-none transition-all">
                    <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-digit w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold rounded-xl border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 outline-none transition-all">
                    <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-digit w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold rounded-xl border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 outline-none transition-all">
                </div>

                <!-- Submit Button -->
                <button type="submit" id="checkout-otp-verify-btn" class="w-full py-3.5 px-4 bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white font-bold rounded-xl shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2 text-base">
                    <span>ভেরিফাই করুন</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </form>

            <!-- Resend Section -->
            <div class="mt-5 text-center">
                <div id="checkout-otp-timer-container" class="text-xs font-semibold text-gray-500">
                    পুনরায় কোড পাঠান (<span id="checkout-otp-countdown" class="text-orange-600 font-bold">60</span>s)
                </div>
                <button type="button" id="checkout-otp-resend-btn" onclick="resendCheckoutOtp()" class="hidden text-xs font-bold text-orange-600 hover:text-orange-700 hover:underline">
                    পুনরায় কোড পাঠান
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let _otpCountdownInterval = null;
    let _otpCountdownSeconds = 60;
    let _otpSuccessCallback = null;

    function openCheckoutOtpModal(orderId, phone, successCallback = null) {
        const modal = document.getElementById('checkout-otp-modal');
        const container = document.getElementById('checkout-otp-modal-container');
        const orderIdInput = document.getElementById('checkout-otp-order-id');
        const phoneDisplay = document.getElementById('checkout-otp-phone-display');
        const errorDiv = document.getElementById('checkout-otp-error');
        const successDiv = document.getElementById('checkout-otp-success');

        if (!modal || !container) return;

        _otpSuccessCallback = successCallback;
        if (orderIdInput) orderIdInput.value = orderId || '';
        if (phoneDisplay) phoneDisplay.innerText = phone ? `(${phone})` : '';

        if (errorDiv) errorDiv.classList.add('hidden');
        if (successDiv) successDiv.classList.add('hidden');

        // Clear OTP input digits
        const digits = modal.querySelectorAll('.otp-digit');
        digits.forEach(d => d.value = '');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
            if (digits.length > 0) digits[0].focus();
        }, 50);

        startOtpCountdown();
        setupOtpInputListeners();
    }

    function closeCheckoutOtpModal() {
        const modal = document.getElementById('checkout-otp-modal');
        const container = document.getElementById('checkout-otp-modal-container');
        if (!modal || !container) return;

        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            clearInterval(_otpCountdownInterval);
        }, 200);
    }

    function setupOtpInputListeners() {
        const digits = document.querySelectorAll('.otp-digit');
        digits.forEach((input, index) => {
            input.oninput = function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 1 && index < digits.length - 1) {
                    digits[index + 1].focus();
                }
                // Auto submit if all 4 filled
                const allFilled = Array.from(digits).every(d => d.value.length === 1);
                if (allFilled) {
                    document.getElementById('checkout-otp-verify-btn')?.click();
                }
            };

            input.onkeydown = function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    digits[index - 1].focus();
                }
            };

            input.onpaste = function(e) {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
                if (pasteData) {
                    pasteData.split('').slice(0, 4).forEach((char, i) => {
                        if (digits[i]) digits[i].value = char;
                    });
                    const lastIdx = Math.min(pasteData.length, digits.length) - 1;
                    if (digits[lastIdx]) digits[lastIdx].focus();
                    if (pasteData.length >= 4) {
                        document.getElementById('checkout-otp-verify-btn')?.click();
                    }
                }
            };
        });
    }

    function startOtpCountdown() {
        clearInterval(_otpCountdownInterval);
        _otpCountdownSeconds = 60;
        const timerContainer = document.getElementById('checkout-otp-timer-container');
        const countdownEl = document.getElementById('checkout-otp-countdown');
        const resendBtn = document.getElementById('checkout-otp-resend-btn');

        if (timerContainer) timerContainer.classList.remove('hidden');
        if (resendBtn) resendBtn.classList.add('hidden');
        if (countdownEl) countdownEl.innerText = _otpCountdownSeconds;

        _otpCountdownInterval = setInterval(() => {
            _otpCountdownSeconds--;
            if (countdownEl) countdownEl.innerText = _otpCountdownSeconds;

            if (_otpCountdownSeconds <= 0) {
                clearInterval(_otpCountdownInterval);
                if (timerContainer) timerContainer.classList.add('hidden');
                if (resendBtn) resendBtn.classList.remove('hidden');
            }
        }, 1000);
    }

    function submitCheckoutOtp(e) {
        if (e) e.preventDefault();

        const digits = document.querySelectorAll('.otp-digit');
        let otpCode = '';
        digits.forEach(d => otpCode += d.value.trim());

        const orderId = document.getElementById('checkout-otp-order-id')?.value;
        const errorDiv = document.getElementById('checkout-otp-error');
        const errorMsg = document.getElementById('checkout-otp-error-msg');
        const verifyBtn = document.getElementById('checkout-otp-verify-btn');

        if (otpCode.length < 4) {
            if (errorDiv && errorMsg) {
                errorMsg.innerText = 'অনুগ্রহ করে ৪ ডিজিটের সম্পূর্ণ ওটিপি কোড দিন।';
                errorDiv.classList.remove('hidden');
            }
            return;
        }

        if (errorDiv) errorDiv.classList.add('hidden');

        const originalBtnHtml = verifyBtn ? verifyBtn.innerHTML : '';
        if (verifyBtn) {
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> ভেরিফাই হচ্ছে...';
        }

        fetch('{{ route("order.verify.otp") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                order_id: orderId,
                otp: otpCode
            })
        })
        .then(async (res) => {
            let data = {};
            try { data = await res.json(); } catch(e) { data = {}; }

            if (res.ok && data.success) {
                if (typeof _otpSuccessCallback === 'function') {
                    _otpSuccessCallback(data);
                } else if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    window.location.reload();
                }
            } else {
                if (errorDiv && errorMsg) {
                    errorMsg.innerText = data.message || 'ভুল ওটিপি কোড! অনুগ্রহ করে আবার চেষ্টা করুন।';
                    errorDiv.classList.remove('hidden');
                }
                digits.forEach(d => d.value = '');
                if (digits[0]) digits[0].focus();
            }
        })
        .catch(err => {
            console.error('OTP Verification Error:', err);
            if (errorDiv && errorMsg) {
                errorMsg.innerText = 'সার্ভারে সমস্যা হয়েছে। অনুগ্রহ করে পুনরায় চেষ্টা করুন।';
                errorDiv.classList.remove('hidden');
            }
        })
        .finally(() => {
            if (verifyBtn) {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = originalBtnHtml;
            }
        });
    }

    function resendCheckoutOtp() {
        const orderId = document.getElementById('checkout-otp-order-id')?.value;
        const resendBtn = document.getElementById('checkout-otp-resend-btn');
        const successDiv = document.getElementById('checkout-otp-success');
        const successMsg = document.getElementById('checkout-otp-success-msg');
        const errorDiv = document.getElementById('checkout-otp-error');
        const errorMsg = document.getElementById('checkout-otp-error-msg');

        if (!orderId) return;

        if (resendBtn) {
            resendBtn.disabled = true;
            resendBtn.innerText = 'কোড পাঠানো হচ্ছে...';
        }

        fetch('{{ route("order.resend.otp") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ order_id: orderId })
        })
        .then(async (res) => {
            let data = {};
            try { data = await res.json(); } catch(e) { data = {}; }

            if (res.ok && data.success) {
                if (successDiv && successMsg) {
                    successMsg.innerText = data.message || 'নতুন ওটিপি কোড পাঠানো হয়েছে।';
                    successDiv.classList.remove('hidden');
                    setTimeout(() => successDiv.classList.add('hidden'), 4000);
                }
                startOtpCountdown();
            } else {
                if (errorDiv && errorMsg) {
                    errorMsg.innerText = data.message || 'নতুন ওটিপি পাঠানো সম্ভব হয়নি।';
                    errorDiv.classList.remove('hidden');
                }
            }
        })
        .catch(err => {
            console.error('OTP Resend Error:', err);
            if (errorDiv && errorMsg) {
                errorMsg.innerText = 'সার্ভারে সমস্যা হয়েছে। পুনরায় চেষ্টা করুন।';
                errorDiv.classList.remove('hidden');
            }
        })
        .finally(() => {
            if (resendBtn) {
                resendBtn.disabled = false;
                resendBtn.innerText = 'পুনরায় কোড পাঠান';
            }
        });
    }
</script>
