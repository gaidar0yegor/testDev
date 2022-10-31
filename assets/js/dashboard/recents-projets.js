import $ from 'jquery';
import {initSlickCarousel} from "../slick-carousel/init-slick-carousel";

$(() => {
    if (!window['recents-projets']) {
        return;
    }

    $(document).on('click', '#recents-projets-menu .dropdown-menu', function (e) {
        e.stopPropagation();
    });

    const createProjetPath = id => window['recents-projets'].dataset.urlProjet.replace('0', id);
    var carousel;

    fetch('/corp/api/dashboard/recents-projets')
        .then(response => response.json())
        .then(({recentsProjets}) => {
            if (0 === recentsProjets.length) {
                return;
            }

            const $wrapper = $('#recents-projets');
            const $cards = $('<div class="slick-carousel">');

            $wrapper.append($cards);

            recentsProjets.forEach(projet => {
                const projetPath = createProjetPath(projet.projetId);

                const $projet = $(`
                    <div class="item card-actualite rounded shadow-sm border ${projet.filterType}" style="border-top: 5px solid ${projet.colorCode} !important;">
                        <div class="card-side">
                            <i class="fa fa-circle" style="color: ${projet.colorCode};"></i>
                        </div>
                        
                        <div class="card-body w-100">
                            <div class="m-0 p-0 w-100">
                                <h5 class="card-title">
                                    <a href="${projetPath}">${projet.acronyme}</a>
                                    <small><i>${projet.datetime}</i></small>
                                </h5>
                                <hr>
                                <ul class="list-unstyled">${projet.activity}</ul>
                            </div>
                        </div>
                    </div>
                `);

                $cards.append($projet);
            });

            $(document).ready(function(){
                carousel = initSlickCarousel($cards);
            });

        })
    ;

    $(document).on('change', '#recents-projets-menu input:checkbox', function (e) {
        var checkedCheckboxs = $('#recents-projets-menu input:checkbox:checked');
        var filterClass = checkedCheckboxs.map(function() { return $(this).val(); }).get().filter(function(n) { return n !== ""; }).join(', ');

        $(carousel).slick('slickUnfilter');
        $(carousel).slick('slickFilter', filterClass);

        setTimeout(function() {
            carousel.slick("slickGoTo", 0);
        },1000);
    });
});

