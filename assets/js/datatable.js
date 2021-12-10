import './datatable/dataTables.css';
import './datatable/dataTables.searchHighlight.css';
import '../../node_modules/datatables.net-responsive-bs4/css/responsive.bootstrap4.css';

import './datatable/dataTables';
import './datatable/jquery.highlight';
import './datatable/dataTables.responsive.min';

import $ from "jquery";
import {language_dt} from "./translation";

(function() {
    $(document).on('init.dt.dth', function(e, settings, json) {
        var table;
        table = new $.fn.dataTable.Api(settings);
        if (settings.oInit.searchHighlight) {
            return table.on('draw', function() {
                var body;
                body = $(table.table().body());
                body.unhighlight();
                return body.highlight(table.search());
            });
        }
    });

}).call(this);

$('.table.datatable:not(.custom-datatable)').DataTable( {
    info: false,
    searchHighlight: true,
    responsive: true,
    language: language_dt,
} );