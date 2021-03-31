import $ from 'jquery';

$(() => {
    if (!window['recents-projets']) {
        return;
    }

    const createProjetPath = id => window['recents-projets'].dataset.urlProjet.replace('0', id);

    fetch('/api/dashboard/recents-projets')
        .then(response => response.json())
        .then(({recentsProjets}) => {
            console.log(recentsProjets);

            if (0 === recentsProjets.length) {
                return;
            }

            const $wrapper = $('#recents-projets');
            const $cards = $('<div class="card-deck">');

            $wrapper.append('<h3>Mes projets récents</h3>');
            $wrapper.append($cards);

            recentsProjets.forEach(projet => {
                const projetPath = createProjetPath(projet.id);

                const $projet = $(`
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><a href="${projetPath}" class="stretched-link">${projet.acronyme}</a></h5>
                            <ul class="list-unstyled mb-0">
                            </ul>
                        </div>
                    </div>
                `);

                projet.activities.forEach(activity => {
                    $projet.find('ul').append($(`<li>${activity.text}</li>`));
                });

                $cards.append($projet);
            });
        })
    ;
});
