import 'owl.carousel2/dist/assets/owl.carousel.css';
import '../styles/owl-carousel-style.css'
import $ from 'jquery';
import 'owl.carousel2/dist/owl.carousel.min';

const initOwlCarousel = (elem) => {
    $(document).ready(function(){
        $(elem).owlCarousel({
            rewind: false,
            margin:10,
            nav:true,
            dots: false,
            autoplay: true,
            navText: [
                '<i class="fa fa-arrow-left" aria-hidden="true"></i>',
                '<i class="fa fa-arrow-right" aria-hidden="true"></i>'
            ],
            items:2,
            responsive:{
                0:{items: 1},
                600:{items: 2},
                1000:{items: 3}
            }
        });
    });
};

export {
    initOwlCarousel
};