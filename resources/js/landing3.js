const slider = document.querySelector('.rv-swiper');

if (slider) {
    const initializeSlider = async () => {
        const [{ default: Swiper }, { Autoplay, Pagination }] = await Promise.all([
            import('swiper'),
            import('swiper/modules'),
        ]);

        new Swiper(slider, {
            modules: [Autoplay, Pagination],
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            centeredSlides: false,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
            },
        });
    };

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(([entry]) => {
            if (entry.isIntersecting) {
                observer.disconnect();
                initializeSlider();
            }
        }, { rootMargin: '300px' });

        observer.observe(slider);
    } else {
        initializeSlider();
    }
}
