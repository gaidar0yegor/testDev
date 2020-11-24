(function ($) {
    'use strict';

    $('input.custom-file-input').on('change', function () {
        const $input = $(this);
        const path = $input.val();
        const filename = path.split(/[\/\\]+/).pop();

        $input
            .next('.custom-file-label')
            .html(filename)
        ;
    });

    $.fn.datepicker.dates['fr'] = {
        days: ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'],
        daysShort: ['dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam'],
        daysMin: ['di', 'lu', 'ma', 'me', 'je', 've', 'sa'],
        months: ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
        monthsShort: ['jan', 'fév', 'mar', 'avr', 'mai', 'jui', 'jui', 'aoû', 'sep', 'oct', 'nov', 'déc'],
        today: 'Aujourd\'hui',
        clear: 'Vider',
        format: 'dd/mm/yyyy',
        titleFormat: 'MM yyyy',
        weekStart: 0
    };

    $('.month-picker').datepicker({
        language: 'fr',
        minViewMode: 'months',
        format: 'MM yyyy',
        immediateUpdates: true,
    });

    $('.date-picker').datepicker({
        language: 'fr',
        format: 'dd MM yyyy',
        immediateUpdates: true,
    });
})(jQuery);
