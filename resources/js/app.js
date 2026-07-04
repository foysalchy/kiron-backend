import './bootstrap';
import $ from 'jquery';
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';

import Swiper from 'swiper';
import { Pagination, Autoplay, EffectFade } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';

window.$ = window.jQuery = $;
window.toastr = toastr;

window.Swiper = Swiper;
window.SwiperModules = { Pagination, Autoplay, EffectFade };
