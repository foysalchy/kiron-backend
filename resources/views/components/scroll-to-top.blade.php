@props([
    'threshold' => 200,
    'bottom' => 'bottom-20 md:bottom-8',
    'right' => 'right-4 md:right-8',
])

<button
    id="scroll-to-top-btn"
    type="button"
    aria-label="Scroll to top"
    title="Scroll to top"
    onclick="scrollToTop()"
    {{ $attributes->merge([
        'class' => "group fixed {$bottom} {$right} z-40 flex items-center justify-center w-11 h-11 md:w-13 md:h-13 rounded-full bg-white shadow-xl hover:shadow-2xl hover:scale-110 active:scale-95 transition-all duration-300 ease-in-out opacity-0 translate-y-6 pointer-events-none cursor-pointer focus:outline-none"
    ]) }}
>
    <!-- Circular Progress SVG -->
    <svg class="absolute inset-0 w-full h-full -rotate-90 pointer-events-none p-0.5" viewBox="0 0 100 100">
        <!-- Background Track -->
        <circle
            cx="50"
            cy="50"
            r="43"
            fill="none"
            stroke="#e5e7eb"
            stroke-width="7"
        />
        <!-- Progress Circle -->
        <circle
            id="scroll-progress-circle"
            cx="50"
            cy="50"
            r="43"
            fill="none"
            stroke="var(--primary-color, #BD4F00)"
            stroke-width="7"
            stroke-linecap="round"
            stroke-dasharray="270.18"
            stroke-dashoffset="270.18"
            style="transition: stroke-dashoffset 0.1s linear;"
        />
    </svg>

    <!-- Up Arrow Icon -->
    <svg
        xmlns="http://www.w3.org/2000/svg"
        class="w-5 h-5 md:w-6 md:h-6 text-[var(--primary-color,#BD4F00)] group-hover:-translate-y-0.5 transition-transform duration-200 relative z-10"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        stroke-width="3.5"
    >
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
    </svg>
</button>

<script>
    (function() {
        const btn = document.getElementById('scroll-to-top-btn');
        const circle = document.getElementById('scroll-progress-circle');
        if (!btn || !circle) return;

        const threshold = {{ (int) $threshold }};
        const circumference = 270.18; // 2 * Math.PI * 43

        function updateProgress() {
            const scrollY = window.scrollY || document.documentElement.scrollTop;
            const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
            
            // Toggle visibility
            if (scrollY > threshold) {
                btn.classList.remove('opacity-0', 'translate-y-6', 'pointer-events-none');
                btn.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');
            } else {
                btn.classList.add('opacity-0', 'translate-y-6', 'pointer-events-none');
                btn.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');
            }

            // Calculate progress and update circle stroke
            if (scrollHeight > 0) {
                const progress = Math.min(Math.max(scrollY / scrollHeight, 0), 1);
                const offset = circumference - (progress * circumference);
                circle.style.strokeDashoffset = offset;
            }
        }

        window.scrollToTop = function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        };

        window.addEventListener('scroll', updateProgress, { passive: true });
        window.addEventListener('resize', updateProgress);
        updateProgress();
    })();
</script>

@if(!empty($activePreviewTheme))
<x-theme-preview-switcher />
<script>
    (function() {
        const previewTheme = @json($activePreviewTheme);
        if (!previewTheme) return;

        function appendPreviewTheme(urlStr) {
            try {
                const url = new URL(urlStr, window.location.origin);
                if (url.origin === window.location.origin && !url.searchParams.has('preview_theme')) {
                    url.searchParams.set('preview_theme', previewTheme);
                    return url.toString();
                }
            } catch (e) {}
            return urlStr;
        }

        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link || !link.href) return;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('tel:') || href.startsWith('mailto:')) {
                return;
            }

            try {
                const targetUrl = new URL(link.href, window.location.origin);
                if (targetUrl.origin === window.location.origin) {
                    if (!targetUrl.searchParams.has('preview_theme')) {
                        targetUrl.searchParams.set('preview_theme', previewTheme);
                        link.href = targetUrl.toString();
                    }
                }
            } catch (err) {}
        }, true);
    })();
</script>
@endif
