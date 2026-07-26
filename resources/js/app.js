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
});
