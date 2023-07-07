import $ from 'jquery';
import { addToastrFlashMessage } from './flash-messages';
import { format, fromUnixTime } from 'date-fns';
import locale from './dateFnsLocale';
import { t } from './translation';

const day0 = 'calmonth-day-0 btn-secondary';
const day05 = 'calmonth-day-05 btn-info';
const day1 = 'calmonth-day-1 btn-success';
var calMonthUpdated = false;
window.addEventListener('beforeunload', function (e) {
    if (calMonthUpdated){
        e.preventDefault();
        e.returnValue = '';
    }
});

/**
 * Init agenda page.
 *
 * @param {date} date Object with "year" and "month".
 * @param {Number[]} craJours Array of 0, 0.5 or 1, the cra of the month.
 */
function initAbsences(date, craJours) {
    initCalMonth($('.calmonth'), date.year, date.month, craJours);

    const $btnSubmit = $('.btn-validate-cra');

    $('.calmonth').on('click', 'button', function () {
        calMonthUpdated = true;
        $btnSubmit.html(t('save'));

        const $btn = $(this);
        const dayNumber = parseInt($btn.data('daynumber'), 10);

        craJours[dayNumber - 1] += 0.5;

        if (craJours[dayNumber - 1] > 1) {
            craJours[dayNumber - 1] = 0;
        }

        $btn.removeClass([day0, day05, day1].join(' ').split(' '));

        if (craJours[dayNumber - 1] <= 0) {
            $btn.addClass(day0);
        } else if (craJours[dayNumber - 1] >= 1) {
            $btn.addClass(day1);
        } else {
            $btn.addClass(day05);
        }
    });

    $btnSubmit.click(function () {
        $btnSubmit.prop('disabled', true);
        $btnSubmit.text(t('updating...'));

        $.post(['/corp/api/cra', date.year, date.month].join('/'), {
            cra: craJours,
        })
            .done(() => {
                $('.text-success').html('<i class="fa fa-check" aria-hidden="true"></i> ' + t('editions_updated'));
                $btnSubmit.prop('disabled', false);
                $btnSubmit.html('<i class="fa fa-check" aria-hidden="true"></i> ' + t('updated!'));
                $('.message-validation .text-warning').remove();
                addToastrFlashMessage('success', t('your_absences_have_been_updated'));
                calMonthUpdated = false;
            })
        ;
    });
}

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
                $dayButton.addClass(day0);
            } else if (worked >= 1) {
                $dayButton.addClass(day1);
            } else {
                $dayButton.addClass(day05);
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

    Array(7)
        .fill(0)
        .map((_, index) => 1604919600 + index * 24 * 3600)
        .map(timestamp => format(fromUnixTime(timestamp), 'EEE', {locale}))
        .forEach(mois => {
            $header.append($('<p>').text(mois));
        })
    ;

    return $header;
}

function createDayButton(dayNumber) {
    const $button = $('<button>')
        .addClass('btn btn-block btn-jour')
    ;

    $button.append($('<span class="demi-journee">').text('½'));
    $button.append($('<span>').text(dayNumber));

    $button.data('daynumber', dayNumber);

    return $button;
}

global.initAbsences = initAbsences;
