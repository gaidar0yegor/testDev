import $ from 'jquery';
import EmbedForm from './EmbedForm';
import datesLocalize from './dates.localize';
import { detectedLocale } from './translation';

import './datatable';

$('form').on('change', 'input.custom-file-input', function () {
    const $input = $(this);
    const path = $input.val();
    const filename = path.split(/[\/\\]+/).pop();

    $input
        .next('.custom-file-label')
        .html(filename)
    ;
});

$.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex ) {
        if (settings.sInstance === 'users_list_dt'){
            var tab = $('#filter-users-statut').val();
            var statut = data[4];

            return ( tab !== '' && tab === statut ) || ( tab === '' );
        }

        return true;
    }
);

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

// liste utilisateurs
var users_list_dt;
$(document).ready( function () {
    users_list_dt = $('#users_list_dt').DataTable( {
        order: [[ 5, "desc" ]],
        columnDefs: [{"sortable": false, "searchable": false, "targets": [6]}],
        language: {
            url: detectedLocale === 'fr' ? "https://cdn.datatables.net/plug-ins/1.11.3/i18n/fr_fr.json" : null,
        },
        initComplete: function(settings, json) {
            $('#filter-users-statut').val('SOCIETE_USER_STATUT_ACTIVE').trigger('change');
        },
    });

    $('.tab-filter-users').on('click', '.nav-link', function () {
            $('.tab-filter-users').find('.nav-link').removeClass('active');
            $(this).addClass('active');
        })
        .on('click', '.enabled-users', function () {
            $('#filter-users-statut').val('SOCIETE_USER_STATUT_ACTIVE').trigger('change');
        })
        .on('click', '.disabled-users', function () {
            $('#filter-users-statut').val('SOCIETE_USER_STATUT_DISABLED').trigger('change');
        })
        .on('click', '.all-users', function () {
            $('#filter-users-statut').val('').trigger('change');
        })
        .on('click', '.all-users', function () {
            $('#filter-users-statut').val('').trigger('change');
        })
        .on('change', '#filter-users-statut', function () {
            users_list_dt.draw();
        });
});

// form fait marquants
var files_list_dt;
$(document).ready( function () {
    files_list_dt = $('#files_list_dt').DataTable( {
        dom: 'ift',
        paging: false,
        language: {
            url: detectedLocale === 'fr' ? "https://cdn.datatables.net/plug-ins/1.11.3/i18n/fr_fr.json" : null,
        },
    } );

    EmbedForm.init($('.fichier-projets-container'), {
        $addButton: $('.fichier-projets-container-tfoot .add-file-btn'),
        newItemAppend: $newItem => $('.fichier-projets-container').append($newItem),
    });
    $('.fichier-projets-container').on('click', '.remove-file-btn', function () {
        files_list_dt.row( $(this).parents('tr') ).remove().draw();
    });
} );

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
                    if ($.fn.DataTable.isDataTable( '#files_list_dt' )){
                        files_list_dt.row( $a.parents('tr') ).remove().draw();
                    } else {
                        $a.closest('tr').remove();
                    }
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
