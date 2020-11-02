(function (_export, $) {
    'use strict';

    /**
     * Réinitialise le cra à partir du mois donné.
     *
     * @param {jQuery} $calMonth Element à réinitialiser, exemple: $('.calmonth')
     * @param {String} yearStr Année '2020'
     * @param {String} monthStr Mois '11'
     * @param {Number[]} cra Cra par défaut ou déjà rempli, liste de 0, 1 ou 0.5
     */
    function initCalMonth($calMonth, yearStr, monthStr, cra) {
        if (!$calMonth) {
            throw new Error('Undefined calmonth element');
        }

        if (!yearStr || !monthStr) {
            throw new Error('yearStr and monthStr must be set');
        }

        /**
         * Vide le cra
         */
        $calMonth.empty();

        /**
         * Ajoute les noms des jours en header
         */
        $calMonth.append(createDaysHeader());

        /**
         * Ajoute chaque jour
         */
        let currentDate = new Date(yearStr, parseInt(monthStr, 10) - 1, 1);

        if (!currentDate) {
            throw new Error(['Date', yearStr, monthStr, 'in an invalid date.'].join(' '));
        }

        // Reviens au dernier lundi
        while (currentDate.getDay() !== 1) {
            currentDate.setDate(currentDate.getDate() - 1);
        }

        let $row = $('<div>').addClass('calmonth-row');
        let i = 0;

        // Ajoute un bouton par jour pour tout le mois
        do {
            const $dayButton = createDayButton(currentDate.getDate());

            if (parseInt(monthStr, 10) - 1 !== currentDate.getMonth()) {
                $dayButton.prop('disabled', true);
            } else {
                const worked = cra[currentDate.getDate() - 1];

                if (worked <= 0) {
                    $dayButton.addClass('btn-secondary');
                } else if (worked >= 1) {
                    $dayButton.addClass('btn-success');
                } else {
                    $dayButton.addClass('btn-info');
                }
            }

            $row.append($dayButton);

            currentDate.setDate(currentDate.getDate() + 1);

            if (0 === (i + 1) % 7) {
                $calMonth.append($row);
                $row = $('<div>').addClass('calmonth-row');
            }
        } while (++i < 42);
    }

    function createDaysHeader() {
        const $header = $('<div>').addClass('calmonth-row');

        ['lun', 'mar', 'mer', 'jeu', 'ven', 'sam', 'dim'].forEach(mois => {
            $header.append($('<p>').text(mois));
        });

        return $header;
    }

    function createDayButton(dayNumber) {
        const $button = $('<button>')
            .addClass('btn btn-block btn-jour')
        ;

        $button.append($('<span class="demi-journee">').text('½'));
        $button.append($('<span>').text(dayNumber));

        $button.data('daynumber', dayNumber)

        return $button;
    }

    _export.initCalMonth = initCalMonth;
})(window, jQuery);
