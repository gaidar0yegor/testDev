import 'owl.carousel2/dist/assets/owl.carousel.css';
import '../../styles/owl-carousel-style.css'
import $ from 'jquery';
import 'owl.carousel2/dist/owl.carousel.min';

$(() => {
    if (!window['recents-projets']) {
        return;
    }

    const createProjetPath = id => window['recents-projets'].dataset.urlProjet.replace('0', id);

    fetch('/api/dashboard/recents-projets')
        .then(response => response.json())
        .then(({recentsProjets}) => {
            if (0 === recentsProjets.length) {
                return;
            }

            const $wrapper = $('#recents-projets');
            const $cards = $('<div class="owl-carousel owl-theme">');

            $wrapper.append($cards);

            recentsProjets.forEach(projet => {
                const projetPath = createProjetPath(projet.id);

                const $projet = $(`
                    <div class="item card">
                        <div class="card-body">
                            <h5 class="card-title"><a href="${projetPath}">${projet.acronyme}</a></h5>
                            <ul class="list-unstyled mb-0">${projet.activity}</ul>
                            <small><i>${projet.datetime}</i></small>
                        </div>
                    </div>
                `);

                $cards.append($projet);
            });

            $(document).ready(function(){
                $($cards).owlCarousel({
                    loop:true,
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
        })
    ;
});

