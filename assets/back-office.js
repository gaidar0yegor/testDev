// Entrypoint for back office part (requires base + app)

import './styles/back-office.css';
import './js/bo-dashboard/matomo-api'

import $ from "jquery";
import {btnsDatatable, domDatatable} from "./js/datatable";
import {language_dt} from "./js/translation";
import initTippyTitle from "./js/popper";

$('.tab-filter-societes')
    .on('click', '.nav-link', function () {
        $('.tab-filter-societes').find('.nav-link').removeClass('active');
        $(this).addClass('active');
    })
    .on('click', '.enabled-societes', function () {
        $('#filter-societes-statut').val('Actif').trigger('change');
    })
    .on('click', '.disabled-societes', function () {
        $('#filter-societes-statut').val('Désactivé').trigger('change');
    })
    .on('click', '.all-societes', function () {
        $('#filter-societes-statut').val('').trigger('change');
    })
    .on('change', '#filter-societes-statut', function () {
        $('#societes_list_dt').DataTable().draw();
    });

var societes_list_dt = $('#societes_list_dt').DataTable( {
    dom: domDatatable,
    buttons: btnsDatatable,
    responsive: true,
    order: [[ 1, "asc" ]],
    columnDefs: [
        { "sortable": false, "searchable": false, "targets": [0] }
    ],
    searchHighlight: true,
    language: language_dt,
    initComplete: function(settings, json) {
        $('#filter-societes-statut').val('Actif').trigger('change');
    },
    createdRow: function(settings){
        initTippyTitle();
    }
});

$(document).ready( function () {
    $('.tab-filter-societes .enabled-societes').trigger('click');
});

$.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex ) {
        if (settings.sInstance === 'societes_list_dt'){
            var statut = data[5];

            var tab = $('#filter-societes-statut').val();


            return ( tab !== '' && statut.includes(tab) ) || ( tab === '' );
        }

        return true;
    }
);