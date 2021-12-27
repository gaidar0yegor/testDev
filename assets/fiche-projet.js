import './styles/fiche-projet.css';

import $ from 'jquery';
import {detectedLocale,language_dt} from './js/translation';
import './js/datatable.js';

language_dt.searchPlaceholder = detectedLocale === "en" ? 'Search within the striking facts' : 'Rechercher dans les faits marquants';
language_dt.emptyTable = detectedLocale === "en" ? 'There is no striking fact on this project' : "Il n'existe aucun fait marquant sur ce projet";

var fait_marquant_timeline_dt;
$(document).ready( function () {
    fait_marquant_timeline_dt = $('#fait_marquant_timeline_dt').DataTable( {
        dom: 'ft',
        paging: false,
        ordering: false,
        buttons: false,
        info: false,
        searchHighlight: true,
        language: language_dt,
        fnDrawCallback: function ( oSettings ) {
            $(oSettings.nTHead).hide();
            $('div.add-fait-marquant').prependTo('#fait_marquant_timeline_dt_filter');
        },
    });
});

