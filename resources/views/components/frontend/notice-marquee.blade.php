@if(isset($notices) && $notices->isNotEmpty())
<div class="w-full bg-amber-50/80 border-b border-amber-200/80 text-gray-800 text-sm overflow-hidden py-2 px-3 md:px-4 shadow-[0_1px_2px_rgba(0,0,0,0.03)] transition-all">
    <div class="container mx-auto flex items-center gap-3">
        <!-- Flash News Badge -->
        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider text-white shadow-xs shrink-0 select-none primary-bg">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
            </span>
            <i class="fas fa-bullhorn text-[11px]"></i>
            <span>{{ __('Notice') }}</span>
        </div>

        <!-- Marquee Ticker Content -->
        <div class="flex-1 overflow-hidden relative group/ticker py-0.5">
            <div class="inline-flex items-center whitespace-nowrap animate-notice-ticker group-hover/ticker:[animation-play-state:paused] will-change-transform">
                @foreach($notices as $notice)
                    <a href="{{ route('notice.details', $notice->slug) }}" 
                       class="inline-flex items-center gap-2 text-gray-700 hover:text-brand font-medium transition-colors duration-150 mx-4 text-xs md:text-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                        <span>{{ $notice->title }}</span>
                    </a>
                @endforeach

                {{-- Duplicate once for seamless infinite loop when few notices exist --}}
                @if($notices->count() < 4)
                    @foreach($notices as $notice)
                        <a href="{{ route('notice.details', $notice->slug) }}" 
                           class="inline-flex items-center gap-2 text-gray-700 hover:text-brand font-medium transition-colors duration-150 mx-4 text-xs md:text-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                            <span>{{ $notice->title }}</span>
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<style>
@keyframes noticeTicker {
    0% {
        transform: translate3d(0, 0, 0);
    }
    100% {
        transform: translate3d(-50%, 0, 0);
    }
}
.animate-notice-ticker {
    display: inline-flex;
    animation: noticeTicker 25s linear infinite;
}
</style>
@endif
