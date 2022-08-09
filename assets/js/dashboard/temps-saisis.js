import $ from 'jquery';

$(() => {
    const mesTempsRappel = window['mes-temps-rappel'];
    const urlTempsPasses = mesTempsRappel.getAttribute('data-url-temps-passes');

    const clearAlerts = () => mesTempsRappel.innerHTML = '';

    const monthName = n => ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'][n];
    const craMonthName = cra => monthName(parseInt(cra.month.split('-')[1], 10) - 1);

    const addAlert = (type, icon, html) => {
        mesTempsRappel.innerHTML += `
            <div class="alert alert-${type}" role="alert">
                <i class="fa fa-${icon}" aria-hidden="true"></i>
                ${html}
            </div>
        `;
    };

    const addAlertForCra = cra => {
        if (!cra.hasTempsPasses) {
            addAlert(
                'success',
                'check',
                `Pour ${craMonthName(cra)}, vous n'avez pas de temps à saisir car vous n'étiez contributeur sur aucun projet.`,
            );

            return;
        }

        if (cra.isTempsPassesSubmitted) {
            addAlert(
                'success',
                'calendar-check-o',
                'Vous êtes à jour dans la saisie de vos temps',
                // `Vous avez saisi vos temps passés de ${craMonthName(cra)} le ${cra.tempsPassesModifiedAt}.`,
            );

            return;
        }

        addAlert(
            'warning',
            'calendar-times-o',
            `Vous devriez <a href="${urlTempsPasses}/${cra.month.replace('-', '/')}" class="alert-link">enregistrer vos temps passés de ${craMonthName(cra)}</a>.`,
        );
    };

    const date = new Date();

    // Affiche la notification pour le mois d'avant jusque 20 jours après
    date.setDate(date.getDate() - 20);

    fetch(`/corp/api/dashboard/temps-du-mois/${date.getFullYear()}-${('0' + (date.getMonth() + 1)).substr(-2)}`)
        .then(response => response.json())
        .then(cra => {
            clearAlerts();
            addAlertForCra(cra);
        })
    ;
});