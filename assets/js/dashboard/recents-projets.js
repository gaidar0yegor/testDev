import $ from 'jquery';
import {initOwlCarousel} from "../owl-carousel";

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
                const projetPath = createProjetPath(projet.projetId);

                const $projet = $(`
                    <div class="item card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="${projetPath}">${projet.acronyme}</a>
                                <span class="badge d-inline-block rounded-circle mt-1 float-right" style="background-color: ${projet.colorCode};width: 15px;height: 15px;"></span>
                            </h5>
                            <ul class="list-unstyled">${projet.activity}</ul>
                            <div class="card-footer">
                                <small><i>${projet.datetime}</i></small>
                            </div>
                        </div>
                    </div>
                `);

                $cards.append($projet);
            });

            initOwlCarousel($cards);
        })
    ;
});

