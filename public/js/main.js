(function ($, EmbedForm) {
    'use strict';

    $('form').on('change', 'input.custom-file-input', function () {
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

    // form fait marquants
    EmbedForm.init($('.fichier-projets-container'), {
        $addButton: $('.fichier-projets-container .add-file-btn'),
        newItemAppend: $newItem => $('.fichier-projets-container .add-file-btn').before($newItem),
    });
    $('.fichier-projets-container').on('click', '.remove-file-btn', function () {
        $(this).closest('tr').remove();
    });


    // form gestion projets participants
    const $participants = $('#liste_projet_participants_projetParticipants');
    EmbedForm.init($participants, {
        $addButton: $('#btn-ajouter-participant'),
    });
    $participants.on('click', '.embed-form-remove', function () {
        $(this).closest('.projet-participant-row').remove();
    });

    // link-get-to-post
    $('a.link-delete-file').click(function () {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?')) {
            return false;
        }

        const $a = $(this);

        $.ajax({
            url: $a.attr('href'),
            method: 'DELETE',
            success: function () {
                $a.closest('tr').animate(
                    {
                        opacity: 0,
                    },
                    400,
                    () => {
                        $a.closest('tr').remove();
                    },
                );
            },
        });

        return false;
    });

    if ($('#user_notification')) {
        const updateCheckboxes = () => {
            const allNotifications = $('[name="user_notification[notificationEnabled]"]').is(':checked');
            const inputNames = [
                'user_notification[notificationSaisieTempsEnabled]',
                'user_notification[notificationCreateFaitMarquantEnabled]',
                'user_notification[notificationLatestFaitMarquantEnabled]',
            ];

            inputNames.forEach(inputName => {
                const $element = $(`[name="${inputName}"]`)
                    .closest('.form-group')
                ;

                if (allNotifications) {
                    $element.show();
                } else {
                    $element.hide();
                }
            });
        }

        updateCheckboxes();

        $('[name="user_notification[notificationEnabled]"]').change(updateCheckboxes);
    }

    if ($('#projets-year-filter')) {
        $('#projets-year-filter').change(({currentTarget}) => {
            const url = $('#projets-year-filter').data('url');
            const year = currentTarget.value;

            if ('all' === year) {
                window.location.href = $('#projets-year-filter').data('urlAll');
                return;
            }

            window.location.href = $('#projets-year-filter')
                .data('urlByYear')
                .replace('2000', year)
            ;
        });
    }
})(jQuery, EmbedForm);
