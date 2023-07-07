import $ from 'jquery';
import {initOwlCarousel} from '../../owl-carousel';
import {dashboardContentsId} from '../utils';

const contentDiv = window[dashboardContentsId.general];

if (contentDiv){

    $(() => {

        $(`#${dashboardContentsId.general} .recent-activity`).each((_, divActivities) => {
            const societeUser = divActivities.dataset.societeUser;

            fetch(`/corp/api/mes-societes/dashboard/general/recents-projets/${societeUser}`)
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

                    initOwlCarousel($cards);
                })
            ;
        });

    });


}