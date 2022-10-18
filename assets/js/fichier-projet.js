import './../styles/datatable-fichier.scss';

import $ from 'jquery';
import EmbedForm from './EmbedForm';
import {detectedLocale, language_dt} from './translation';
import { addToastrFlashMessage } from './flash-messages';

import {domDatatable, btnsDatatable} from './datatable';
import initTippyTitle from "./popper";

var files_list_dt = $("#files_list_dt:not('.rdi-popup-fait-marquant #files_list_dt')").DataTable({
    rowGroup: $('#filter-files-dossier').length > 0
        ? {
            dataSrc: [0],
            emptyDataGroup: null
        }
        : false,
    order: [[0, 'asc'], [3, 'desc']],
    columnDefs: [{visible: $('#filter-files-dossier').length === 0, targets: 0}, {sortable: false, targets: [4]}],
    info: false,
    paging: false,
    searching: !$('#files_list_dt').hasClass('no-searchBar'),
    dom: domDatatable,
    buttons: $('#files_list_dt').hasClass('no-exportBtn') ? [] : btnsDatatable,
    responsive: true,
    searchHighlight: true,
    language: language_dt,
    createdRow: function(settings){
        initTippyTitle();
    }
});

$('#files_list_dt').on('change', 'input.custom-file-input', function () {
    const $input = $(this);
    const path = $input.val();
    const filename = path.split(/[\/\\]+/).pop().split('.').slice(0, -1).join('.');

    let fileNameInput = $($input).parents('tr').find('.form-file-name');
    if (fileNameInput){
        $(fileNameInput).val(filename).focus().select();
    }
});

const activeDossierFichierTab = () => {
    const {hash} = window.location;

    if (!hash || !hash.startsWith('#dossier-')) {
        return;
    }

    const elementToActive = window[hash.substr(1)];

    if (!elementToActive) {
        return;
    }

    $(elementToActive).trigger('click');
};

$.fn.dataTable.ext.search.push(
    function (settings, data, dataIndex) {
        if (settings.sInstance === 'files_list_dt' && $('#filter-files-dossier').length > 0) {
            var dossier = (data[0]).replace(/\s/g, ''),
                tab = $('#filter-files-dossier').val().replace(/\s/g, '');

            tab !== '' ? files_list_dt.rowGroup().disable() : files_list_dt.rowGroup().enable();

            ((tab !== '' && tab === dossier) || (tab === ''))
                ? $(settings.oInstance.fnGetNodes(dataIndex)).show()
                : $(settings.oInstance.fnGetNodes(dataIndex)).hide();

            return true;
        }
        return true;
    }
);

$('.tab-filter-fichiers')
    .on('click', '.nav-link', function () {
        $('.tab-filter-fichiers').find('.nav-link').removeClass('active');
        $(this).addClass('active');
        $('#filter-files-dossier').val($(this).hasClass('all-fichiers') ? '' : $(this).find('.dossier-name').text()).trigger('change');
    })
    .on('change', '#filter-files-dossier', function () {
        $('#files_list_dt').DataTable().draw();
    });

EmbedForm.init($('.fichier-projets-container'), {
    $addButton: $('.fichier-projets-container-tfoot .add-file-btn'),
    newItemAppend: $newItem => $('.fichier-projets-container').append($newItem),
    initSelect2: {
        placeholder: function () {
            $(this).data('placeholder');
        }
    },
});

$(document).on('click', '.scroll-to-add-file-btn', function () {
    $('.add-file-btn').get(0).scrollIntoView();
    $('.add-file-btn').first().trigger('click');
});

$('.fichier-projets-container').on('click', '.remove-file-btn', function () {
    $(this).parents('tr').remove();
});

document.addEventListener('DOMContentLoaded', activeDossierFichierTab);

$(document).on('click', '.fichier-projets-container button[popup-target]', function(event) {
    let popup = $('#' + $(this).attr('popup-target'));
    if (popup){
        $(popup).show();
    }
});
$(document).on('click', '.fichier-projet-children-popup .rdi-popup .rdi-popup-close', function(event) {
    let popup = $(this).parents('.fichier-projet-children-popup');
    $(popup).hide();
});
$(document).on('change', '.fichier-projets-container input[type="file"]', function(event) {
    const fi = event.target;
    if (fi.files.length) {
        for (let i = 0; i <= fi.files.length - 1; i++) {
            if (Math.round((fi.files.item(i).size / 1024)) >= 5000) {
                addToastrFlashMessage('error', 'La taille de chaque pièce jointe est limitée à : <b>5 Mo</b>.<br>Vous pouvez joindre le fichier avec un lien externe.');
                $(fi).parents('tr').remove();
            }
        }
    }
});

