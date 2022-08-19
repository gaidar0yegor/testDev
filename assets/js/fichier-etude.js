import $ from 'jquery';
import EmbedForm from './EmbedForm';
import {detectedLocale, language_dt} from './translation';

import {domDatatable, btnsDatatable} from './datatable';
import initTippyTitle from "./popper";

var lab_files_list_dt = $('#lab_files_list_dt').DataTable({
    order: [[1, 'desc']],
    info: false,
    paging: false,
    searching: !$('#lab_files_list_dt').hasClass('no-searchBar'),
    dom: domDatatable,
    buttons: $('#lab_files_list_dt').hasClass('no-exportBtn') ? [] : btnsDatatable,
    responsive: true,
    searchHighlight: true,
    language: language_dt,
    createdRow: function(settings){
        initTippyTitle();
    }
});

$('.tab-filter-fichiers')
    .on('click', '.nav-link', function () {
        $('.tab-filter-fichiers').find('.nav-link').removeClass('active');
        $(this).addClass('active');
        $('#filter-files-dossier').val($(this).hasClass('all-fichiers') ? '' : $(this).find('.dossier-name').text()).trigger('change');
    });

EmbedForm.init($('.fichier-etudes-container'), {
    $addButton: $('.fichier-etudes-container-tfoot .add-file-btn'),
    newItemAppend: $newItem => $('.fichier-etudes-container').append($newItem)
});

$('.fichier-etudes-container').on('click', '.remove-file-btn', function () {
    $(this).parents('tr').remove();
});

