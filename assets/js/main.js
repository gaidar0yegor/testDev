import $ from 'jquery';
import EmbedForm from './EmbedForm';
import datesLocalize from './dates.localize';
import {detectedLocale, language_dt} from './translation';

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
        if (settings.sInstance === 'users_list_dt' || settings.sInstance === 'validation_temps_dt' ){
            switch (settings.sInstance) {
                case 'users_list_dt':
                    var statut = data[4]; break;
                case 'validation_temps_dt':
                    var statut = data[13]; break;
            }

            var tab = $('#filter-users-statut').val();


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
var validation_temps_dt;
$(document).ready( function () {

    $('.tab-filter-users')
        .on('click', '.nav-link', function () {
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
            $('#users_list_dt').DataTable().draw();
            $('#validation_temps_dt').DataTable().draw();
        });

    validation_temps_dt = $('#validation_temps_dt').DataTable( {
        info: false,
        responsive: true,
        ordering: false,
        searchHighlight: true,
        language: language_dt,
        initComplete: function(settings, json) {
            $('#filter-users-statut').val('SOCIETE_USER_STATUT_ACTIVE').trigger('change');
        },
    });
    users_list_dt = $('#users_list_dt').DataTable( {
        info: false,
        responsive: true,
        order: [[ 5, "desc" ]],
        columnDefs: [{"sortable": false, "searchable": false, "targets": [6]}],
        searchHighlight: true,
        language: language_dt,
        initComplete: function(settings, json) {
            $('#filter-users-statut').val('SOCIETE_USER_STATUT_ACTIVE').trigger('change');
        },
    });
});

// form fait marquants
var files_list_dt;
$(document).ready( function () {
    files_list_dt = $('#files_list_dt').DataTable( {
        dom: 'lftp',
        responsive: true,
        searchHighlight: true,
        language: language_dt,
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

// delete an activity
$('.list-activities.list-group .link-delete-activity').click(function () {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette activité ?')) {
        return false;
    }

    const $btn = $(this);

    $.ajax({
        url: $($btn).data('href'),
        method: 'POST',
        success: function (response) {
            $($btn).parents('li.list-group-item').remove();
        },
    });

    return false;
});

//project colors input
$(document).on('change', '.radio-choice-colors input[type=radio]', function (e) {
    var $inputColor = $('input[type=color]');
    if ($inputColor) {
        $inputColor.val(this.value);
    }
});
$(document).on('change', '.input-color-container input[type=color]', function (e) {
    var $radioColors = $('.radio-choice-color[name=usedColorCodes]');
    if ($radioColors) {
        $radioColors.prop("checked", false);
    }
});