import './styles/fiche-projet.css';

import $ from 'jquery';
import {detectedLocale,language_dt} from './js/translation';
import './js/datatable.js';
import './js/popup-fait-marquant';

import './js/fait-marquant-comment';
import initTippyTitle from "./js/popper";

language_dt.searchPlaceholder = detectedLocale === "en" ? 'Search within the striking facts ...' : 'Rechercher dans les faits marquants ...';
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
        createdRow: function(settings){
            initTippyTitle();
        }
    });

    $('#fait_marquant_timeline_dt').find('.fait-marquant-content').each((i, div) => {
        if (i >= 2 && $(div).height() > 200){
            $(div).height(200);
            $(div).find('.read-more').show();
        }
    });

    $( '.read-more' ).click(function() {
        $(this).parents('.fait-marquant-content').css('height','auto');
        $(this).hide();
    });
});

