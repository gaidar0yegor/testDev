(function (window, fetch) {
    'use strict';

    fetch('/api/dashboard/temps-du-mois')
        .then(response => response.json())
        .then(cra => {
            let text = '';
            let userValidated = true;

            if (!cra.hasTempsPasses) {
                text += `
                    <p class="text-success">
                        <i class="fa fa-check" aria-hidden="true"></i>
                        Ce mois-ci, vous n'avez pas de temps à saisir car vous n'étiez contributeur sur aucun projet.
                    </p>
                `;
            } else {
                if (cra.isTempsPassesSubmitted) {
                    text += `
                        <p class="text-success">
                            <i class="fa fa-check" aria-hidden="true"></i>
                            Vous avez saisi vos temps passés le ${cra.tempsPassesModifiedAt}.
                        </p>
                    `;
                } else {
                    userValidated = false;
                    text += `
                        <p class="text-warning">
                            <i class="fa fa-times" aria-hidden="true"></i>
                            Vous devriez enregistrer vos temps passés.
                        </p>
                    `;
                }
            }

            const mesTempsRappel = window['mes-temps-rappel'];

            const icon = userValidated
                ? 'calendar-check-o'
                : 'calendar-times-o'
            ;

            mesTempsRappel.querySelector('.temps-messages').innerHTML = text;
            mesTempsRappel.querySelector('i.fa').className = 'fa fa-'+icon;
        })
    ;
})(window, fetch);
