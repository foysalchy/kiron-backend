import '../css/app.css';
import './bootstrap';
import $ from 'jquery';
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';
import Swiper from 'swiper/bundle';
import 'swiper/css/bundle';
import Lenis from 'lenis';

window.$ = window.jQuery = $;
window.toastr = toastr;
window.Swiper = Swiper;

document.addEventListener('DOMContentLoaded', () => {
    const lenis = new Lenis({
        duration: 1.5,
        smoothWheel: true,
    });

    function raf(time) {
        lenis.raf(time);
        requestAnimationFrame(raf);
    }
    requestAnimationFrame(raf);

    window.lenis = lenis; // এটি টেস্ট করার জন্য জরুরি

    // Image Skeleton Loader
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
