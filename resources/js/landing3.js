import Swiper from 'swiper';
import { Autoplay, Navigation, Pagination } from 'swiper/modules';

function initLanding3Swiper() {
    const el = document.querySelector('.rv-swiper');
    if (!el) return;

    if (el.swiper) {
        el.swiper.destroy(true, true);
    }

    const totalSlides = el.querySelectorAll('.swiper-slide').length;

    new Swiper(el, {
        modules: [Autoplay, Navigation, Pagination],
        slidesPerView: 1,
        spaceBetween: 20,
        loop: totalSlides >= 2,
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
        pagination: {
            el: el.parentElement.querySelector('.swiper-pagination') || '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            640: {
                slidesPerView: Math.min(2, totalSlides),
                spaceBetween: 20,
            },
            1024: {
                slidesPerView: Math.min(3, totalSlides),
                spaceBetween: 30,
            },
        },
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLanding3Swiper);
} else {
    initLanding3Swiper();
}

window.addEventListener('load', initLanding3Swiper);
setTimeout(initLanding3Swiper, 500);
