@php
    $previewTheme = request('preview_theme');
    $themes = [
        '1' => 'Theme 1 (Classic)',
        '2' => 'Theme 2 (Modern)',
        '3' => 'Theme 3 (Minimal)',
        '4' => 'Theme 4 (Elegant)',
        '5' => 'Theme 5 (Storefront)',
    ];
@endphp

@if(!empty($previewTheme))
<div id="theme-preview-floating-bar" class="fixed right-3 md:right-5 top-1/2 -translate-y-1/2 z-50 transition-all duration-300 font-sans select-none">
    <!-- Collapsed Toggle Pill (Shown when minimized) -->
    <button 
        id="theme-preview-pill" 
        type="button" 
        onclick="toggleThemePreviewBar(true)"
        class="hidden items-center gap-2 bg-gray-900 text-white shadow-2xl px-3 py-2.5 rounded-full border border-gray-700 hover:bg-gray-800 transition transform hover:scale-105 cursor-pointer text-xs font-semibold"
        title="Open Theme Previewer"
    >
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
        <span>Theme {{ $previewTheme }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>

    <!-- Expanded Card Container -->
    <div id="theme-preview-card" class="bg-white/95 backdrop-blur-md text-gray-800 border border-gray-200/80 rounded-2xl shadow-2xl p-4 w-72 transition-all duration-300">
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Live Preview</span>
            </div>
            
            <button 
                type="button" 
                onclick="toggleThemePreviewBar(false)" 
                class="text-gray-400 hover:text-gray-700 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer"
                title="Minimize"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <!-- Current Theme Info -->
        <div class="mt-3">
            <div class="text-[11px] text-gray-500 font-medium">Currently Viewing:</div>
            <div class="text-sm font-semibold text-gray-900 flex items-center justify-between">
                <span>{{ $themes[$previewTheme] ?? ('Theme ' . $previewTheme) }}</span>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-indigo-50 text-indigo-700 border border-indigo-100">Active</span>
            </div>
        </div>

        <!-- Switch Theme Dropdown -->
        <div class="mt-3">
            <label for="theme-select-switcher" class="block text-[11px] font-medium text-gray-600 mb-1">
                Switch Theme Preview:
            </label>
            <div class="relative">
                <select 
                    id="theme-select-switcher" 
                    onchange="switchPreviewTheme(this.value)"
                    class="w-full appearance-none bg-gray-50 border border-gray-300 text-gray-800 text-xs rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block px-3 py-2 pr-8 cursor-pointer font-medium hover:bg-gray-100 transition"
                >
                    @foreach($themes as $key => $name)
                        <option value="{{ $key }}" {{ (string)$previewTheme === (string)$key ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Footer / Exit Preview -->
        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
            <button 
                type="button" 
                onclick="exitThemePreview()" 
                class="text-red-600 hover:text-red-700 font-medium hover:underline flex items-center gap-1 cursor-pointer"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Exit Preview</span>
            </button>
            <span class="text-[10px] text-gray-400">Admin Mode</span>
        </div>
    </div>
</div>

<script>
    function toggleThemePreviewBar(showCard) {
        const card = document.getElementById('theme-preview-card');
        const pill = document.getElementById('theme-preview-pill');
        if (showCard) {
            card.classList.remove('hidden');
            pill.classList.add('hidden');
            pill.classList.remove('flex');
            try { sessionStorage.setItem('theme_preview_collapsed', '0'); } catch(e) {}
        } else {
            card.classList.add('hidden');
            pill.classList.remove('hidden');
            pill.classList.add('flex');
            try { sessionStorage.setItem('theme_preview_collapsed', '1'); } catch(e) {}
        }
    }

    function switchPreviewTheme(themeNum) {
        if (!themeNum) return;
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('preview_theme', themeNum);
            window.location.href = url.toString();
        } catch(e) {
            window.location.search = '?preview_theme=' + themeNum;
        }
    }

    function exitThemePreview() {
        try {
            const url = new URL(window.location.href);
            url.searchParams.delete('preview_theme');
            window.location.href = url.toString();
        } catch(e) {
            window.location.href = window.location.pathname;
        }
    }

    // Restore state from sessionStorage
    (function() {
        try {
            if (sessionStorage.getItem('theme_preview_collapsed') === '1') {
                toggleThemePreviewBar(false);
            }
        } catch(e) {}
    })();
</script>
@endif

