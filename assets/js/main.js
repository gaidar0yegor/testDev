import $ from 'jquery';
import EmbedForm from './EmbedForm';
import datesLocalize from './dates.localize';
import {hexIsLight} from "./utils";
import {detectedLocale, language_dt} from './translation';

import { domDatatable, btnsDatatable } from './datatable';
import initTippyTitle from "./popper";

$('form').on('change', 'input.custom-file-input', function () {
    const $input = $(this);
    const path = $input.val();
    const filename = path.split(/[\/\\]+/).pop();

    $input
        .next('.custom-file-label')
        .html(filename)
    ;
});

$(document).ready(function () {
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });
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
    todayHighlight:'TRUE',
    autoclose: true,
});

$('.date-picker:not(.numeric-format)').datepicker({
    language: detectedLocale,
    format: 'dd MM yyyy',
    immediateUpdates: true,
    todayHighlight:'TRUE',
    autoclose: true,
});

$('.date-picker.numeric-format').datepicker({
    language: detectedLocale,
    dateFormat: "yy-mm-dd",
    altFormat: 'mm/dd/yyyy',
    immediateUpdates: true,
    todayHighlight:'TRUE',
    autoclose: true,
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
        .on('click', '.nav-link:not(.organigramme-team)', function () {
            $('#users_list_dt_wrapper').slideDown(500);
            $('#users_list_organigramme').slideUp(500);
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
        createdRow: function(settings){
            initTippyTitle();
        }
    });
    users_list_dt = $('#users_list_dt').DataTable( {
        dom: domDatatable,
        buttons: btnsDatatable,
        responsive: true,
        order: [[ 6, "desc" ]],
        columnDefs: [
            { "sortable": false, "searchable": false, "targets": [7] },
            { "width": "10%", "targets": [5,6] }
        ],
        searchHighlight: true,
        language: language_dt,
        initComplete: function(settings, json) {
            $('#filter-users-statut').val('SOCIETE_USER_STATUT_ACTIVE').trigger('change');
        },
        createdRow: function(settings){
            initTippyTitle();
        }
    });
    $('#multi_societe_projets_dt').DataTable( {
        dom: domDatatable,
        buttons: btnsDatatable,
        responsive: true,
        orderFixed: [[0, 'asc']],
        rowGroup: {
            dataSrc: 0
        },
        order: [[2, 'asc']],
        paging: false,
        columnDefs: [{visible: false, targets: 0}],
        searchHighlight: true,
        language: language_dt,
        createdRow: function(settings){
            initTippyTitle();
        }
    });
});

// form projet
EmbedForm.init($('#projet_form_projetUrls'), {
    $addButton: $('.add-external-link'),
});
EmbedForm.init($('#projet_form_dossierFichierProjets'), {
    $addButton: $('.add-folder-file'),
});
$('.dossierFichierProjets, .projetUrls').on('keypress', 'input', function (e) {
    if (e.keyCode === 13) {
        e.preventDefault();
        $(this).parents('.dossierFichierProjets').find('button.add-folder-file').trigger('click');
        $(this).parents('.projetUrls').find('button.add-external-link').trigger('click');
    }
});
$('#projet_form_projetUrls').on('click', '.remove-row-btn', function (e) {
    e.preventDefault();
    $(this).closest('.row').remove();
});
$('#projet_form_dossierFichierProjets').on('click', '.remove-row-btn', function (e) {
    e.preventDefault();
    $(this).closest('.row').remove();
});


// form gestion projets participants
const $participants = $('#liste_projet_participants_projetParticipants');
EmbedForm.init($participants, {
    $addButton: $('#btn-ajouter-participant'),
    initSelect2: {},
});
$participants.on('click', '.embed-form-remove', function () {
    $(this).closest('.projet-participant-row').remove();
});

// form gestion projets participants
const $etudes = $('#equipe_etudes');
EmbedForm.init($etudes, {
    $addButton: $('#btn-ajouter-etude'),
    initSelect2: {},
});
$etudes.on('click', '.embed-form-remove', function () {
    $(this).closest('.equipe-etude-row').remove();
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
$('.toggle-target').on('click', function (e) {
    var $target = $('.' + $(this).data( "target" ));
    if ($(this).is(":checked")){
        $($target).slideDown();
    } else {
        $($target).slideUp();
        var $btns = $($target).find('.remove-row-btn');
        $($btns).each((i, btn) => {
            $(btn).trigger('click');
        });
    }
});

$(document).ready(function(){
    $(document).find('.img-expend').hover(function() {
        $(this).addClass('expend-transition');
    }, function() {
        $(this).removeClass('expend-transition');
    });
});

$('.sidebar.sidebar-projects .arrow').on('mouseenter click', function() {
    if (!$('.sidebar.sidebar-projects .projects-list').is(":visible")){
        $('.sidebar.sidebar-projects .projects-list').toggle( "slide" );
        $('.sidebar.sidebar-projects .arrow .fa')
            .toggleClass( "fa-arrow-left" )
            .toggleClass( "fa-arrow-right" );
    }
});

$('.sidebar.sidebar-projects').on('mouseleave', function() {
    if ($('.sidebar.sidebar-projects .projects-list').is(":visible")){
        $('.sidebar.sidebar-projects .projects-list').toggle( "slide" );
        $('.sidebar.sidebar-projects .arrow .fa')
            .toggleClass( "fa-arrow-left" )
            .toggleClass( "fa-arrow-right" );
    }
});

$(document).on('click', 'tr[data-href]', function (e) {
    if ($(this).data('href')) window.location.href = $(this).data('href');
});