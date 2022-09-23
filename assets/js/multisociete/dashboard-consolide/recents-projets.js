import $ from 'jquery';
import {initSlickCarousel} from "../../slick-carousel/init-slick-carousel";
import {dashboardContentsId} from '../utils';

$(() => {
    if (!window[dashboardContentsId.consolide]) {
        return;
    }

    const $wrapper = $('#recents-projets');
    const $cards = $('<div class="slick-carousel">');

    const dashboardConsolide = $wrapper.data('dashboardConsolide');

    fetch(`/corp/api/mes-societes/dashboard/consolide/recents-projets/${dashboardConsolide ? dashboardConsolide : ""}`)
        .then(response => response.json())
        .then(({recentsProjets}) => {
            if (0 === recentsProjets.length) {
                return;
            }

            $wrapper.append($cards);

            recentsProjets.forEach(projet => {
                var activity = document.createElement('div');
                activity.innerHTML = projet.activity.trim();

                const $projet = $(`
                    <div class="item card-actualite" style="border: 1px solid ${projet.colorCode};border-left: 5px solid ${projet.colorCode};">
                        <div class="card-side">
                            <i class="fa fa-exclamation-circle" style="color: ${projet.colorCode};"></i>
                        </div>
                        <div class="card-body">
                            <div class="m-0 p-0">
                                <h5 class="card-title">
                                    <a href="javascript:;">${projet.societe} / ${projet.acronyme}</a>
                                    <i class="fa fa-sm fa-circle" aria-hidden="true"></i>
                                    <small><i>${projet.datetime}</i></small>
                                </h5>
                                <ul class="list-unstyled">${activity.innerText}</ul>
                            </div>
                        </div>
                    </div>
                    `);

                $cards.append($projet);
            });

            $(document).ready(function(){
                initSlickCarousel($cards);
            });
        })
    ;
});

