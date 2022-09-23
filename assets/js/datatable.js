import './datatable/dataTables.css';
import './datatable/dataTables.responsive.min.css';
import './datatable/buttons.dataTables.min.css';
import './datatable/dataTables.searchHighlight.css';

import '../../node_modules/datatables.net/js/jquery.dataTables.min';
import './datatable/jquery.highlight';
import '../../node_modules/datatables.net-responsive/js/dataTables.responsive.min';
import '../../node_modules/datatables.net-buttons/js/dataTables.buttons.min';
import '../../node_modules/datatables.net-rowgroup/js/dataTables.rowGroup.min';
import '../../node_modules/datatables.net-buttons/js/buttons.print.min';
import '../../node_modules/datatables.net-buttons/js/buttons.html5.min';
import '../../node_modules/datatables.net-buttons/js/buttons.colVis.min';
import '../../node_modules/datatables.net-buttons/js/buttons.flash.min';
import jsZip from './datatable/jszip.min';

import initTippyTitle from './popper';

window.JSZip = jsZip;

import $ from "jquery";
import {language_dt} from "./translation";

const domDatatable = "lfBrtip";
const btnsDatatable = [
    {
        extend: 'collection',
        text: 'Export',
        buttons: [
            'copy',
            'excel',
            'csv',
            'print'
        ]
    }
];

(function () {
    $(document)
        .on('init.dt.dth', function (e, settings, json) {
            var table;
            table = new $.fn.dataTable.Api(settings);
            if (settings.oInit && settings.oInit.searchHighlight) {
                return table.on('draw', function () {
                    var body;
                    body = $(table.table().body());
                    body.unhighlight();
                    return body.highlight(table.search());
                });
            }
        })
        .on('order.dt.dth', function (e, settings, json) {
            initTippyTitle();
        });
}).call(this);

$('.table.datatable:not(.custom-datatable)').DataTable( {
    dom: domDatatable,
    buttons: btnsDatatable,
    searchHighlight: true,
    responsive: true,
    language: language_dt,
    createdRow: function(settings){
        initTippyTitle();
    }
} );

export {
    domDatatable,
    btnsDatatable
};