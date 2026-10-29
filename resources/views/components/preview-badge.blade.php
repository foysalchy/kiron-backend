@props([
    'name' => '',
    'isDummy' => false,
])

@if(!empty($activePreviewTheme))
    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200 shadow-2xs select-none my-1" title="Managed from Admin Panel: {{ $name }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/>
            <path d="M7 7h.01"/>
        </svg>
        <span>{{ $name }}</span>
        @if($isDummy)
            <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 text-[10px] font-bold border border-amber-300">
                Sample Preview
            </span>
        @endif
    </div>
@endif

