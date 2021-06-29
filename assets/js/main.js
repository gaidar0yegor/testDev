import $ from 'jquery';
import EmbedForm from './EmbedForm';
import datesLocalize from './dates.localize';
import { detectedLocale } from './translation';

$('form').on('change', 'input.custom-file-input', function () {
    const $input = $(this);
    const path = $input.val();
    const filename = path.split(/[\/\\]+/).pop();

    $input
        .next('.custom-file-label')
        .html(filename)
    ;
});

$.fn.datepicker.dates[detectedLocale] = datesLocalize;

$('.month-picker').datepicker({
    language: detectedLocale,
    minViewMode: 'months',
    format: 'MM yyyy',
    immediateUpdates: true,
});

$('.date-picker').datepicker({
    language: detectedLocale,
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

// form projet
EmbedForm.init($('#projet_form_projetUrls'), {
    $addButton: $('.add-external-link'),
});
$('#projet_form_projetUrls').on('click', '.remove-row-btn', function (e) {
    e.preventDefault();
    $(this).closest('.row').remove();
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
        const $group = $('.notifications-group');

        if (allNotifications) {
            $group.show();
        } else {
            $group.hide();
        }
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
