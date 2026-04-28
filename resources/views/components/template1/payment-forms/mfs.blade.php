{{-- resources/views/components/template1/payment-forms/mfs.blade.php --}}
@props(['method', 'mode' => 'modal', 'slug'])

@php
    $name = strtolower($method->name);
    // custom theme for popular MFS (for better UX), otherwise default
    $theme = [
        'bkash'  => ['color' => '#e2136e', 'bg' => '#fdf2f7'],
        'nagad'  => ['color' => '#f58220', 'bg' => '#fff5ed'],
        'rocket' => ['color' => '#8c3494', 'bg' => '#f9f2f9'],
        'upay'   => ['color' => '#ffc40c', 'bg' => '#fffdf2'],
    ];

    $currentTheme = ['color' => '#666', 'bg' => '#f9f9f9'];
    foreach($theme as $key => $val) {
        if(str_contains($name, $key)) { $currentTheme = $val; break; }
    }
@endphp

<div class="payment-form hidden" id="form-{{ $slug }}">
    <div class="space-y-3">
        <div class="p-4 rounded-xl text-sm" style="background:{{ $currentTheme['bg'] }}; border:1px solid {{ $currentTheme['color'] }}30;">
            <p style="color:{{ $currentTheme['color'] }};" class="font-medium mb-1">Send money to this {{ $method->name }} number:</p>
            <span style="color:{{ $currentTheme['color'] }};" class="text-lg font-black">{{ $method->account_number ?? $method->phone }}</span>
            <p class="text-[10px] text-gray-400 mt-1 uppercase font-bold">Type: {{ $method->method_details['type'] ?? 'Personal' }}</p>
        </div>

        <div class="grid grid-cols-1 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Paid Amount <span class="text-red-500">*</span></label>
                <input type="number" name="amount" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none" style="border-color:{{ $currentTheme['color'] }}50">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Transaction ID <span class="text-red-500">*</span></label>
                <input type="text" name="transaction_id" required oninput="this.value=this.value.toUpperCase()" class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-mono focus:outline-none" style="border-color:{{ $currentTheme['color'] }}50">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Reference No <span class="text-red-500">(Optional)</span></label>
                <input type="text" name="reference_no"  oninput="this.value=this.value.toUpperCase()" class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-mono focus:outline-none" style="border-color:{{ $currentTheme['color'] }}50">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Sender {{ $method->name }} Number <span class="text-red-500">*</span></label>
                <input type="tel" name="sender_number" required maxlength="11" class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none" style="border-color:{{ $currentTheme['color'] }}50">
            </div>
        </div>

        {{-- Screenshot logic (Common for all) --}}
        <div class="mt-2">
            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-2">Screenshot</label>
            <div id="{{ $slug }}-preview" class="flex gap-2 mb-2"></div>
            <div onclick="document.getElementById('file-{{ $slug }}').click()" class="border-2 border-dashed border-gray-300 rounded-lg p-3 text-center cursor-pointer hover:bg-gray-50">
                <span class="text-xs text-gray-400">Click to upload receipt</span>
            </div>
            <input type="file" id="file-{{ $slug }}" class="hidden" onchange="previewImages(this, '{{ $slug }}')">
            <div id="{{ $slug }}-hidden-files"></div>
        </div>
    </div>
</div>
