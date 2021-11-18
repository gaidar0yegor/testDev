import './styles/fiche-projet.css';

import $ from 'jquery';
import { detectedLocale } from './js/translation';

import './js/datatable.js';

var fait_marquant_timeline_dt;
$(document).ready( function () {
    fait_marquant_timeline_dt = $('#fait_marquant_timeline_dt').DataTable( {
        dom: 'ft',
        paging: false,
        ordering: false,
        info: false,
        searchHighlight: true,
        language: {
            url: detectedLocale === 'fr' ? "https://cdn.datatables.net/plug-ins/1.11.3/i18n/fr_fr.json" : null,
        },
        fnDrawCallback: function ( oSettings ) {
            $(oSettings.nTHead).hide();
        }
    });
});

