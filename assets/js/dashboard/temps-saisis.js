import $ from 'jquery';

$(() => {
    const mesTempsRappel = window['mes-temps-rappel'];
    const urlTempsPasses = mesTempsRappel.getAttribute('data-url-temps-passes');

    const clearAlerts = () => mesTempsRappel.innerHTML = '';

    const addAlert = (type, icon, html) => {
        mesTempsRappel.innerHTML += `
            <div class="alert alert-${type}" role="alert">
                <i class="fa fa-${icon}" aria-hidden="true"></i>
                ${html}
            </div>
        `;
    }

    fetch('/api/dashboard/temps-du-mois')
        .then(response => response.json())
        .then(cra => {
            clearAlerts();

            if (!cra.hasTempsPasses) {
                addAlert(
                    'success',
                    'check',
                    'Ce mois-ci, vous n\'avez pas de temps à saisir car vous n\'étiez contributeur sur aucun projet.',
                );

                return;
            }

            if (cra.isTempsPassesSubmitted) {
                addAlert(
                    'success',
                    'calendar-check-o',
                    `Vous avez saisi vos temps passés le ${cra.tempsPassesModifiedAt}.`,
                );

                return;
            }

            addAlert(
                'warning',
                'calendar-times-o',
                `Vous devriez <a href="${urlTempsPasses}" class="alert-link">enregistrer vos temps passés</a>.`,
            );
        })
    ;
});
