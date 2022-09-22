import $ from 'jquery';
import { addToastrFlashMessage } from './flash-messages';
import {btnsDatatable, domDatatable} from "./datatable";
import {language_dt} from "./translation";
import initTippyTitle from "./popper";

$(document).on('click', '.btn-patchnote-readed', function (e) {
    fetch('/api/patchnote/readed', { method: 'post' })
        .then(response => response.json())
        .then(response => {
            if ("readed" in response && response.readed === true){
                $('.patchnote-modal').remove();
            } else if ("message" in response) {
                addToastrFlashMessage('error', response.message);
            }
        });
});
$(document).on('click', '.btn-patchnote-close', function (e) {
    $('.patchnote-modal').remove();
});

$('#patchnotes_list_dt').DataTable( {
    dom: domDatatable,
    buttons: btnsDatatable,
    responsive: true,
    orderFixed: [[0, 'desc']],
    rowGroup: {
        dataSrc: 0
    },
    order: [[1, 'desc']],
    paging: false,
    columnDefs: [{visible: false, targets: 0}],
    searchHighlight: true,
    language: language_dt,
    createdRow: function(settings){
        initTippyTitle();
    }
});

