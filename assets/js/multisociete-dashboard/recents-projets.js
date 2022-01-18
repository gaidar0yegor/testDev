import 'owl.carousel2/dist/assets/owl.carousel.css';
import '../../styles/owl-carousel-style.css'
import $ from 'jquery';
import 'owl.carousel2/dist/owl.carousel.min';

$(() => {

    $('.recent-activity').each((_, divActivities) => {
        const societeUser = divActivities.dataset.societeUser;

        fetch(`/api/multiSociete/dashboard/recents-projets/${societeUser}`)
            .then(response => response.json())
            .then(({recentsProjets}) => {
                if (0 === recentsProjets.length) {
                    return;
                }

                const $wrapper = $(divActivities);
                const $cards = $('<div class="owl-carousel owl-theme">');

                $wrapper.append($cards);

                recentsProjets.forEach(projet => {

                    var activity = document.createElement('div');
                    activity.innerHTML = projet.activity.trim();

                    const $projet = $(`
                    <div class="item card">
                        <div class="card-body">
                          <h5 class="card-title">
                                <span>${projet.acronyme}</span>
                                <span class="badge d-inline-block rounded-circle mt-1 float-right" style="background-color: ${projet.colorCode};width: 15px;height: 15px;"></span>
                            </h5>
                            <ul class="list-unstyled">${activity.innerText}</ul>
                              <div class="card-footer">
                                    <small><i>${projet.datetime}</i></small>
                                </div>
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

});

