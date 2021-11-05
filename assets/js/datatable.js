import './datatable/dataTables.css';
import './datatable/dataTables';
import $ from "jquery";
import {detectedLocale} from "./translation";

$('.table.datatable:not(.custom-datatable)').DataTable( {
    language: {
        url: detectedLocale === 'fr' ? "https://cdn.datatables.net/plug-ins/1.11.3/i18n/fr_fr.json" : null,
    },
} );