import './styles/fiche-etude.css';

import $ from 'jquery';
import {detectedLocale,language_dt} from './js/translation';
import './js/datatable.js';
import initTippyTitle from "./js/popper";

language_dt.searchPlaceholder = detectedLocale === "en" ? 'Search within the notes' : 'Rechercher dans les notes';
language_dt.emptyTable = detectedLocale === "en" ? 'There is no note on this study' : "Il n'existe aucune note sur cette étude";

var etude_timeline_dt;
$(document).ready( function () {
    etude_timeline_dt = $('#etude_timeline_dt').DataTable( {
        dom: 'ft',
        paging: false,
        ordering: false,
        buttons: false,
        info: false,
        searchHighlight: true,
        language: language_dt,
        fnDrawCallback: function ( oSettings ) {
            $(oSettings.nTHead).hide();
            $('div.add-etude').prependTo('#etude_timeline_dt_filter');
        },
        createdRow: function(settings){
            initTippyTitle();
        }
    });
});

