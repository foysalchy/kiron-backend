@extends('template5.layouts.front')

@section('meta')
@include('components.meta-info.ecommerce-meta.index-meta', ['setup' => $setup])
@endsection

@section('content')

<!-- ============ PAGE HEADER ============ -->
<section class="relative bg-white pt-24 pb-12 border-b border-[var(--lumina-coal)]/10">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 text-center">
        <span class="font-mono text-[11px] tracking-[0.22em] uppercase text-[var(--primary-color)]">
            Experience the best
        </span>
        <h1 class="font-display font-semibold text-4xl sm:text-5xl mt-3 mb-4 text-black">
            Book a Table
        </h1>
        <p class="text-[var(--lumina-smoke)] max-w-lg mx-auto text-sm leading-relaxed">
            Reserve your spot for an unforgettable dining experience. Choose your date, time, and table preferences below.
        </p>
    </div>
</section>

<!-- ============ RESERVATION FORM ============ -->
<section class="max-w-6xl mx-auto px-6 lg:px-10 py-12 md:py-20">
    
    <div class="flex border-b border-gray-200 mb-8 max-w-xs mx-auto md:max-w-none md:mx-0">
        <button type="button" id="tabBook" class="flex-1 md:flex-none px-6 py-3 border-b-2 border-[var(--primary-color)] text-[var(--primary-color)] font-medium text-sm md:text-base">Book a Table</button>
        <button type="button" id="tabStatus" class="flex-1 md:flex-none px-6 py-3 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm md:text-base transition-colors">Check Status</button>
    </div>

    <div id="bookingContainer" class="bg-gray-50/50 rounded-2xl border border-gray-200 p-4 md:p-8">
        
        <form id="reservationForm" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf
            
            <!-- Left Column (Form Inputs) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Card 1: Slot Selection -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <h3 class="font-semibold text-lg border-b border-gray-100 pb-3 mb-5 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Select a Slot
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                            <input type="date" name="reservation_date" id="res_date" required min="{{ date('Y-m-d') }}"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:outline-none focus:ring-1 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] transition-colors text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Guests</label>
                            <input type="number" name="guest_count" id="res_guests" required min="1" placeholder="Number of guests"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:outline-none focus:ring-1 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] transition-colors text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                            <input type="time" name="start_time" id="res_time" required
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:outline-none focus:ring-1 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] transition-colors text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                            <input type="time" name="end_time" id="res_end_time" required
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:outline-none focus:ring-1 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] transition-colors text-sm">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="button" id="btnCheckAvailability"
                            class="bg-[var(--primary-color)]/10 text-[var(--primary-color)] border border-[var(--primary-color)]/20 px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-[var(--primary-color)] hover:text-white transition-all duration-300">
                            Check Availability
                        </button>
                    </div>
                </div>

                <!-- Hidden sections shown after checking availability -->
                <div id="availabilityResults" class="hidden opacity-0 transition-opacity duration-500 space-y-6">
                    
                    <!-- Card 2: Tables -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-[var(--primary-color)]"></div>
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-5">
                            <h3 class="font-semibold text-lg">Choose Table(s)</h3>
                            <div class="text-sm font-medium">
                                Capacity: <span id="selectedCapacity" class="text-gray-900">0</span> / <span id="targetCapacity" class="text-gray-500">0</span>
                            </div>
                        </div>
                        
                        <div id="tablesList" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            <!-- Tables rendered here -->
                        </div>
                    </div>

                    <!-- Card 3: Contact Details -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm relative overflow-hidden">
                        <div class="flex items-start gap-3 border-b border-gray-100 pb-4 mb-5">
                            <div class="mt-1 bg-gray-100 p-2 rounded-full">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg">Contact Details</h3>
                                <p class="text-xs text-gray-500 mt-1">Enter the details on which you want to receive booking information.</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Guest Name <span class="text-red-500">*</span></label>
                                <input type="text" name="guest_name" id="res_name" required placeholder="Full name"
                                    value="{{ old('guest_name', $customer->name ?? '') }}"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:outline-none focus:ring-1 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] transition-colors text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number <span class="text-red-500">*</span></label>
                                <input type="text" name="guest_phone" id="res_phone" required placeholder="Phone number"
                                    value="{{ old('guest_phone', $customer->phone ?? '') }}"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:outline-none focus:ring-1 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] transition-colors text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                <textarea name="notes" id="res_notes" rows="2" placeholder="Special requests or notes..."
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:outline-none focus:ring-1 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] transition-colors text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column (Summary) -->
            <div class="flex flex-col h-full">
                <!-- Sticky Track for Summary Card -->
                <div class="flex-grow relative">
                    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm lg:sticky lg:top-24">
                        <h3 class="font-semibold text-lg border-b border-gray-100 pb-3 mb-5">Booking Details</h3>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between items-start pb-4 border-b border-gray-50 text-sm">
                                <span class="text-gray-500">Date & Time</span>
                                <div class="text-right font-medium text-gray-900">
                                    <div id="summaryDate">-</div>
                                    <div id="summaryTime" class="text-xs text-gray-500 mt-1">-</div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center pb-4 border-b border-gray-50 text-sm">
                                <span class="text-gray-500">Guests</span>
                                <span id="summaryGuests" class="font-medium text-gray-900">-</span>
                            </div>

                            <div class="flex justify-between items-start pb-4 border-b border-gray-50 text-sm">
                                <span class="text-gray-500">Table(s)</span>
                                <span id="summaryTables" class="font-medium text-gray-900 text-right max-w-[50%]">-</span>
                            </div>
                        </div>

                        <button type="submit" id="btnSubmitReservation" disabled
                            class="w-full bg-[#407BFF] text-white px-6 py-3.5 rounded-lg text-sm font-medium hover:bg-blue-600 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                            Confirm Reservation
                        </button>
                        <div id="resMessage" class="hidden mt-4 text-xs p-3 rounded-lg"></div>
                    </div>
                </div>

                <!-- Help Card (Non-sticky, sits at bottom of column) -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm mt-6">
                    <div class="flex items-center gap-3 mb-3">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <h3 class="font-semibold text-base text-gray-900">We can help you</h3>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed mb-4">
                        Call us at <strong class="text-gray-700">{{$setup->phone ?? ''}}</strong> or reach out to our customer support team to clear all your doubts.
                    </p>
                </div>
            </div>
            
        </form>

    </div>

    <!-- Status Check Container -->
    <div id="statusContainer" class="hidden bg-white rounded-2xl border border-gray-200 p-6 md:p-10 max-w-3xl mx-auto shadow-sm">
        <h3 class="font-semibold text-xl mb-2 text-gray-900">Check Reservation Status</h3>
        <p class="text-gray-500 text-sm mb-6">Enter the phone number you used during booking to see your reservation details.</p>
        
        <form id="statusForm" class="flex flex-col md:flex-row gap-4 mb-8">
            <div class="flex-1">
                <input type="text" id="status_phone" required placeholder="Enter your phone number"
                    class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:ring-1 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] transition-colors">
            </div>
            <button type="submit" id="btnCheckStatus" class="bg-[var(--primary-color)] text-white px-8 py-3 rounded-lg font-medium hover:bg-opacity-90 transition-all whitespace-nowrap">
                Find Reservations
            </button>
        </form>

        <div id="statusResults" class="space-y-4">
            <!-- Results rendered here via JS -->
        </div>
    </div>

</section>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnCheck = document.getElementById('btnCheckAvailability');
    const form = document.getElementById('reservationForm');
    const resultsDiv = document.getElementById('availabilityResults');
    const tablesList = document.getElementById('tablesList');
    const msgDiv = document.getElementById('resMessage');
    const selectedCapSpan = document.getElementById('selectedCapacity');
    const targetCapSpan = document.getElementById('targetCapacity');
    const btnSubmit = document.getElementById('btnSubmitReservation');
    
    // Summary elements
    const summaryDate = document.getElementById('summaryDate');
    const summaryTime = document.getElementById('summaryTime');
    const summaryGuests = document.getElementById('summaryGuests');
    const summaryTables = document.getElementById('summaryTables');
    
    // Inputs
    const inputDate = document.getElementById('res_date');
    const inputTime = document.getElementById('res_time');
    const inputEndTime = document.getElementById('res_end_time');
    const inputGuests = document.getElementById('res_guests');

    function updateSummary() {
        if(inputDate.value) summaryDate.textContent = new Date(inputDate.value).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
        else summaryDate.textContent = '-';
        
        if(inputTime.value && inputEndTime.value) summaryTime.textContent = `${inputTime.value} - ${inputEndTime.value}`;
        else summaryTime.textContent = '-';
        
        if(inputGuests.value) summaryGuests.textContent = inputGuests.value + (inputGuests.value > 1 ? ' Guests' : ' Guest');
        else summaryGuests.textContent = '-';
    }

    [inputDate, inputTime, inputEndTime, inputGuests].forEach(el => {
        el.addEventListener('change', updateSummary);
        el.addEventListener('input', updateSummary);
    });

    function updateCapacityDisplay() {
        let total = 0;
        let selectedTableNumbers = [];
        const checkboxes = document.querySelectorAll('input[name="table_ids[]"]:checked');
        checkboxes.forEach(cb => {
            total += parseInt(cb.dataset.capacity || 0);
            selectedTableNumbers.push('T' + cb.dataset.table);
        });
        
        selectedCapSpan.textContent = total;
        const target = parseInt(inputGuests.value || 0);
        
        if(selectedTableNumbers.length > 0) {
            summaryTables.textContent = selectedTableNumbers.join(', ');
        } else {
            summaryTables.textContent = '-';
        }
        
        if (total >= target && target > 0) {
            selectedCapSpan.className = 'text-green-600 font-bold';
            btnSubmit.disabled = false;
        } else if (total > 0) {
            selectedCapSpan.className = 'text-amber-500 font-bold';
            btnSubmit.disabled = true;
        } else {
            selectedCapSpan.className = 'text-gray-900';
            btnSubmit.disabled = true;
        }
    }

    // Auto-calculate end time initially if empty
    inputTime.addEventListener('change', function() {
        if(this.value && !inputEndTime.value) {
            let [h, m] = this.value.split(':');
            let date = new Date();
            date.setHours(parseInt(h) + 2, parseInt(m));
            let eh = date.getHours().toString().padStart(2, '0');
            let em = date.getMinutes().toString().padStart(2, '0');
            inputEndTime.value = `${eh}:${em}`;
            updateSummary();
        }
    });

    if(btnCheck) {
        btnCheck.addEventListener('click', function() {
            const date = inputDate.value;
            const time = inputTime.value;
            const endTime = inputEndTime.value;
            const guests = inputGuests.value;

            if(!date || !time || !endTime || !guests) {
                toastr.error('Please fill date, start time, end time and number of guests');
                return;
            }

            btnCheck.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Checking...';
            btnCheck.disabled = true;
            updateSummary();

            fetch(`{{ route('reservation.availability') }}?reservation_date=${date}&start_time=${time}&end_time=${endTime}&guest_count=${guests}`)
            .then(res => res.json())
            .then(data => {
                btnCheck.innerHTML = 'Check Availability';
                btnCheck.disabled = false;
                
                if(data.success && data.tables.length > 0) {
                    resultsDiv.classList.remove('hidden');
                    
                    // Trigger fade in
                    setTimeout(() => {
                        resultsDiv.classList.remove('opacity-0');
                        resultsDiv.classList.add('opacity-100');
                    }, 50);

                    msgDiv.classList.add('hidden');
                    targetCapSpan.textContent = guests;
                    selectedCapSpan.textContent = '0';
                    summaryTables.textContent = '-';
                    btnSubmit.disabled = true;
                    
                    let html = '';
                    const sortedTables = data.tables.sort((a,b) => a.table_number - b.table_number);

                    sortedTables.forEach(table => {
                        if (table.is_available) {
                            html += `
                                <label class="cursor-pointer bg-white border border-gray-200 rounded-lg p-3 flex flex-col items-center text-center hover:border-[var(--primary-color)] hover:shadow-sm transition-all duration-200 relative">
                                    <input type="checkbox" name="table_ids[]" value="${table.id}" data-table="${table.table_number}" data-capacity="${table.capacity}" class="hidden peer table-checkbox">
                                    <div class="absolute top-2 left-2 w-4 h-4 border border-gray-300 rounded flex items-center justify-center peer-checked:bg-[var(--primary-color)] peer-checked:border-[var(--primary-color)]">
                                        <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <span class="font-display font-semibold text-gray-900 mt-2">Table ${table.table_number}</span>
                                    <span class="text-[11px] text-gray-500 mt-0.5">Cap: ${table.capacity}</span>
                                </label>
                            `;
                        } else {
                            html += `
                                <div class="bg-gray-50 border border-gray-100 rounded-lg p-3 flex flex-col items-center text-center opacity-50 cursor-not-allowed">
                                    <span class="font-display font-semibold text-gray-500 mt-2">Table ${table.table_number}</span>
                                    <span class="text-[11px] text-gray-400 mt-0.5">Cap: ${table.capacity}</span>
                                </div>
                            `;
                        }
                    });
                    
                    tablesList.innerHTML = html;
                    
                    document.querySelectorAll('.table-checkbox').forEach(cb => {
                        cb.addEventListener('change', function() {
                            if(this.checked) {
                                this.parentElement.classList.remove('border-gray-200');
                                this.parentElement.classList.add('border-[var(--primary-color)]', 'ring-1', 'ring-[var(--primary-color)]', 'shadow-sm');
                            } else {
                                this.parentElement.classList.add('border-gray-200');
                                this.parentElement.classList.remove('border-[var(--primary-color)]', 'ring-1', 'ring-[var(--primary-color)]', 'shadow-sm');
                            }
                            updateCapacityDisplay();
                        });
                    });

                } else {
                    resultsDiv.classList.add('hidden');
                    resultsDiv.classList.remove('opacity-100');
                    resultsDiv.classList.add('opacity-0');
                    msgDiv.className = 'mt-6 text-sm p-4 rounded-lg bg-red-50 text-red-600 border border-red-100 flex items-center gap-2 font-medium';
                    msgDiv.innerHTML = '<i class="fas fa-exclamation-circle text-lg"></i> Error checking availability or no tables left.';
                    msgDiv.classList.remove('hidden');
                }
            })
            .catch(err => {
                btnCheck.innerHTML = 'Check Availability';
                btnCheck.disabled = false;
                toastr.error('Error checking availability');
            });
        });
    }

    if(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const selected = document.querySelectorAll('input[name="table_ids[]"]:checked');
            if(selected.length === 0) {
                toastr.error('Please select at least one table');
                return;
            }

            const formData = new FormData(form);
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            btnSubmit.disabled = true;

            fetch(`{{ route('reservation.store') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        return Promise.reject({type: 'validation', errors: data.errors, message: data.message});
                    }
                    return Promise.reject({type: 'server', message: data.message});
                }
                return data;
            })
            .then(data => {
                btnSubmit.innerHTML = 'Confirm Reservation';
                
                // Clear any previous inline errors
                document.querySelectorAll('.inline-error').forEach(el => el.remove());
                form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

                if(data.success) {
                    resultsDiv.classList.add('hidden');
                    resultsDiv.classList.remove('opacity-100');
                    resultsDiv.classList.add('opacity-0');
                    form.reset();
                    updateSummary();
                    msgDiv.className = 'mt-4 text-sm p-4 rounded-lg bg-green-50 text-green-700 border border-green-200 flex items-center gap-2 font-medium';
                    msgDiv.innerHTML = '<i class="fas fa-check-circle text-lg text-green-500"></i> ' + data.message;
                    msgDiv.classList.remove('hidden');
                    btnSubmit.disabled = true;
                } else {
                    btnSubmit.disabled = false;
                    toastr.error(data.message || 'Error creating reservation');
                }
            })
            .catch(err => {
                btnSubmit.innerHTML = 'Confirm Reservation';
                btnSubmit.disabled = false;

                // Clear any previous inline errors
                document.querySelectorAll('.inline-error').forEach(el => el.remove());
                form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

                if (err.type === 'validation') {
                    for (const field in err.errors) {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            const errorSpan = document.createElement('span');
                            errorSpan.className = 'inline-error text-red-500 text-xs mt-1 block';
                            errorSpan.textContent = err.errors[field][0];
                            input.parentElement.appendChild(errorSpan);
                            input.classList.add('border-red-500');
                        }
                    }
                    toastr.error(err.message || 'Please check the form for errors.');
                } else {
                    toastr.error(err.message || 'An error occurred. Please try again.');
                }
            });
        });
    }

    // Tabs logic
    const tabBook = document.getElementById('tabBook');
    const tabStatus = document.getElementById('tabStatus');
    const bookingContainer = document.getElementById('bookingContainer');
    const statusContainer = document.getElementById('statusContainer');

    function switchTab(activeTab) {
        if (activeTab === 'book') {
            tabBook.classList.add('border-[var(--primary-color)]', 'text-[var(--primary-color)]');
            tabBook.classList.remove('border-transparent', 'text-gray-500');
            tabStatus.classList.remove('border-[var(--primary-color)]', 'text-[var(--primary-color)]');
            tabStatus.classList.add('border-transparent', 'text-gray-500');
            bookingContainer.classList.remove('hidden');
            statusContainer.classList.add('hidden');
        } else {
            tabStatus.classList.add('border-[var(--primary-color)]', 'text-[var(--primary-color)]');
            tabStatus.classList.remove('border-transparent', 'text-gray-500');
            tabBook.classList.remove('border-[var(--primary-color)]', 'text-[var(--primary-color)]');
            tabBook.classList.add('border-transparent', 'text-gray-500');
            statusContainer.classList.remove('hidden');
            bookingContainer.classList.add('hidden');
        }
    }

    tabBook.addEventListener('click', () => switchTab('book'));
    tabStatus.addEventListener('click', () => switchTab('status'));

    // Status Check logic
    const statusForm = document.getElementById('statusForm');
    const btnCheckStatus = document.getElementById('btnCheckStatus');
    const statusResults = document.getElementById('statusResults');

    if (statusForm) {
        statusForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const phone = document.getElementById('status_phone').value;
            if (!phone) return;

            btnCheckStatus.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Searching...';
            btnCheckStatus.disabled = true;

            const formData = new FormData();
            formData.append('guest_phone', phone);
            
            fetch(`{{ route('reservation.status') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => {
                if (res.status === 429) {
                    throw new Error('Too many requests. Please wait a minute before trying again.');
                }
                return res.json();
            })
            .then(data => {
                btnCheckStatus.innerHTML = 'Find Reservations';
                btnCheckStatus.disabled = false;

                if (data.success) {
                    if (data.reservations.length === 0) {
                        statusResults.innerHTML = `
                            <div class="bg-gray-50 border border-gray-100 rounded-xl p-8 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 text-gray-400 mb-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <h4 class="text-gray-900 font-medium mb-1">No reservations found</h4>
                                <p class="text-sm text-gray-500">We couldn't find any reservations associated with this phone number.</p>
                            </div>
                        `;
                    } else {
                        let html = '';
                        data.reservations.forEach(res => {
                            let statusColor = 'bg-gray-100 text-gray-700';
                            if (res.status === 'Confirmed') statusColor = 'bg-green-100 text-green-700';
                            if (res.status === 'Pending') statusColor = 'bg-yellow-100 text-yellow-700';
                            if (res.status === 'Cancelled') statusColor = 'bg-red-100 text-red-700';
                            if (res.status === 'Completed') statusColor = 'bg-blue-100 text-blue-700';

                            html += `
                                <div class="border border-gray-200 rounded-xl p-5 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center hover:shadow-md transition-shadow bg-white">
                                    <div>
                                        <div class="flex items-center gap-3 mb-2">
                                            <h4 class="font-semibold text-gray-900">${res.reservation_date}</h4>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColor}">${res.status}</span>
                                        </div>
                                        <div class="text-sm text-gray-500 flex flex-wrap items-center gap-x-4 gap-y-2">
                                            <span class="flex items-center gap-1.5"><i class="far fa-clock text-gray-400"></i> ${res.start_time} - ${res.end_time}</span>
                                            <span class="flex items-center gap-1.5"><i class="fas fa-users text-gray-400"></i> ${res.guest_count} Guests</span>
                                            <span class="flex items-center gap-1.5"><i class="fas fa-chair text-gray-400"></i> Table(s): ${res.table_numbers || 'N/A'}</span>
                                        </div>
                                        ${res.confirmation_token ? `
                                        <div class="mt-3 text-sm text-gray-700 bg-gray-50 inline-block px-3 py-1.5 rounded-lg border border-gray-100">
                                            <span class="text-gray-500 mr-1">Confirmation code:</span> <span class="font-mono font-medium tracking-wide">${res.confirmation_token}</span>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                        });
                        statusResults.innerHTML = html;
                    }
                } else {
                    toastr.error(data.message || 'Failed to fetch status');
                }
            })
            .catch(err => {
                btnCheckStatus.innerHTML = 'Find Reservations';
                btnCheckStatus.disabled = false;
                toastr.error(err.message || 'An error occurred. Please try again.');
            });
        });
    }
});
</script>
@endpush

