import $ from 'jquery';
import EmbedForm from './EmbedForm';
import datesLocalize from './dates.localize';
import {detectedLocale, language_dt} from './translation';

import { domDatatable, btnsDatatable } from './datatable';

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
                    var statut = data[5]; break;
                case 'validation_temps_dt':
                    var statut = data[14]; break;
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
        dom: domDatatable,
        buttons: btnsDatatable,
        responsive: true,
        ordering: false,
        searchHighlight: true,
        language: language_dt,
        initComplete: function(settings, json) {
            $('#filter-users-statut').val('SOCIETE_USER_STATUT_ACTIVE').trigger('change');
        },
    });
    users_list_dt = $('#users_list_dt').DataTable( {
        dom: domDatatable,
        buttons: btnsDatatable,
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
        info: false,
        paging: false,
        dom: domDatatable,
        buttons: btnsDatatable,
        responsive: true,
        searchHighlight: true,
        language: language_dt,
    } );

    EmbedForm.init($('.fichier-projets-container'), {
        $addButton: $('.fichier-projets-container-tfoot .add-file-btn'),
        newItemAppend: $newItem => $('.fichier-projets-container').append($newItem),
        initSelect2: {
            placeholder: function(){
                $(this).data('placeholder');
            }
        },
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

function hexIsLight(color){
    const hex = color.replace('#', '');
    const c_r = parseInt(hex.substr(0, 2), 16);
    const c_g = parseInt(hex.substr(2, 2), 16);
    const c_b = parseInt(hex.substr(4, 2), 16);
    const brightness = ((c_r * 299) + (c_g * 587) + (c_b * 114)) / 1000;
    return brightness > 155;
}

// change societe color
$(document).on('input', 'input#societe_colorCode', function (e) {
    var color = $(this).val();
    $($('footer#footer')[0]).css("background-image", "linear-gradient(90deg, #ce352c 65%, " + color + " 100%)");
    $($('.user-menu .dropdown-societe')[0]).css("background-color", color);
    $($('.user-menu .dropdown-societe')[0]).css("color", hexIsLight(color) ? "#000000" : "#ffffff");
});
$(document).on('change', 'input#societe_colorCode', function (e) {
    if (!confirm('Êtes-vous sûr de vouloir modifier la couleur ?')) {
        return false;
    }

    const $input = $(this);

    $.ajax({
        url: $($input).data('href'),
        method: 'POST',
        data: {
            code_color: $($input).val()
        },
        success: function (response) {},
    });

    return false;
});

$(document).ready(function(){
    $(document).find('.img-expend').hover(function() {
        $(this).addClass('expend-transition');
    }, function() {
        $(this).removeClass('expend-transition');
    });
});