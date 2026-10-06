import './bootstrap';
import $ from 'jquery';
import toastr from 'toastr';

window.$ = window.jQuery = $;
window.toastr = toastr;

window.SwiperPromise = document.querySelector('.rv-swiper, .mainHeroSwiper, .heroSwiper')
    ? Promise.all([
        import('swiper/bundle'),
        import('swiper/css/bundle'),
    ]).then(([{ default: Swiper }]) => {
        window.Swiper = Swiper;
        return Swiper;
    })
    : Promise.resolve(null);

window.lenisReady = new Promise((resolve, reject) => {
    const initializeLenis = () => {
        import('lenis').then(({ default: Lenis }) => {
            const lenis = new Lenis({
                duration: 1.5,
                smoothWheel: true,
            });

            function raf(time) {
                lenis.raf(time);
                requestAnimationFrame(raf);
            }
            requestAnimationFrame(raf);

            window.lenis = lenis;
            resolve(lenis);
        }, reject);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeLenis, { once: true });
    } else {
        initializeLenis();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    lazyImages.forEach(img => {
        if (!img.complete) {
            const wrapper = img.closest('.aspect-square, picture') || img.parentElement;
            if (wrapper) {
                wrapper.classList.add('animate-pulse', 'bg-gray-200');
                img.style.opacity = '0';

                img.addEventListener('load', function() {
                    wrapper.classList.remove('animate-pulse', 'bg-gray-200');
                    img.style.transition = 'opacity 0.3s ease-in-out';
                    img.style.opacity = '1';
                });
            }
        }
    });
});
