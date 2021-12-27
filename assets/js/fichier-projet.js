import $ from 'jquery';
import EmbedForm from './EmbedForm';
import {detectedLocale, language_dt} from './translation';

import {domDatatable, btnsDatatable} from './datatable';

$.fn.dataTable.ext.search.push(
    function (settings, data, dataIndex) {
        if (settings.sInstance === 'files_list_dt') {
            var dossier = (data[0]).replace(/\s/g, ''),
                tab = $('#filter-files-dossier').val().replace(/\s/g, '');

            return (tab !== '' && tab === dossier) || (tab === '');
        }
        return true;
    }
);

$('.tab-filter-fichiers')
    .on('click', '.nav-link', function () {
        $('.tab-filter-fichiers').find('.nav-link').removeClass('active');
        $(this).addClass('active');
        $('#filter-files-dossier').val($(this).hasClass('all-fichiers') ? '' : $(this).text()).trigger('change');
    })
    .on('change', '#filter-files-dossier', function () {
        $('#files_list_dt').DataTable().draw();
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

var files_list_dt = $('#files_list_dt').DataTable({
    rowGroup: {dataSrc: [0], emptyDataGroup: null},
    order: [[0, 'asc'], [3, 'desc']],
    columnDefs: [{visible: false, targets: 0}, {sortable: false, targets: [4]}],
    info: false,
    paging: false,
    dom: domDatatable,
    buttons: btnsDatatable,
    responsive: true,
    searchHighlight: true,
    language: language_dt,
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

$('.fichier-projets-container').on('click', '.remove-file-btn', function () {
    $(this).parents('tr').remove();
});

document.addEventListener('DOMContentLoaded', activeDossierFichierTab);

