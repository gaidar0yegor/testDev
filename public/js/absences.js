(function (_export, $) {
    'use strict';

    function initAbsences(date) {
        let currentCra = null;

        $.get(['/api/cra', date.year, date.month].join('/'), function (cra) {
            currentCra = cra;

            initCalMonth($('.calmonth'), date.year, date.month, cra);
        });

        $('.calmonth').on('click', 'button', function () {
            const $btn = $(this);
            const dayNumber = parseInt($btn.data('daynumber'), 10);

            currentCra[dayNumber - 1] += 0.5;

            if (currentCra[dayNumber - 1] > 1) {
                currentCra[dayNumber - 1] = 0;
            }

            $btn.removeClass(['btn-success', 'btn-secondary', 'btn-info']);

            if (currentCra[dayNumber - 1] <= 0) {
                $btn.addClass('btn-secondary');
            } else if (currentCra[dayNumber - 1] >= 1) {
                $btn.addClass('btn-success');
            } else {
                $btn.addClass('btn-info');
            }
        });

        const $btnSubmit = $('.btn-validate-cra');

        $btnSubmit.click(function () {
            $btnSubmit.prop('disabled', true);
            $btnSubmit.text('Enregistrement...');

            $.post(['/api/cra', date.year, date.month].join('/'), {
                cra: currentCra,
            })
                .done(function (result) {
                    console.log({result});

                    $btnSubmit.prop('disabled', false);
                    $btnSubmit.text('✓ Enregistré !');
                })
            ;
        });
    }

    _export.initAbsences = initAbsences;
})(window, jQuery);
