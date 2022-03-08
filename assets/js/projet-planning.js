import $ from 'jquery';
import {detectedLocale} from './translation';

$(() => {
    if (!window['project_planning_content']) {
        return;
    }

    var weekScaleTemplate = function(date){
        var dateToStr = gantt.date.date_to_str("%d %M");
        var endDate = gantt.date.add(gantt.date.add(date, 1, "week"), -1, "day");
        return dateToStr(date) + " - " + dateToStr(endDate);
    };

    const projectId = window['project_planning_content'].dataset.projectId;

    gantt.i18n.setLocale(detectedLocale);

    gantt.templates.grid_row_class = function( start, end, task ){
        return task.$level === 0 ? "lot_task" : "";
    };

    gantt.config.columns = [
        {name: "wbs", label: "#", width: 50, align: "center", template: gantt.getWBSCode},
        {name: "text", tree: true, width: 200, resize: true},
        {name: "start_date", width:80, align: "center", editor: {type: "date", map_to: "start_date"}, resize: true},
        {name: "duration", width:40, align: "center", editor: {type: "number", map_to: "duration", min:1}, resize: true},
        {name: 'add', width: 44, min_width: 44, max_width: 44}
    ];

    gantt.config.scales = [
        {unit: "month", step: 1, format: "%F, %Y"},
        {unit: "week", step: 1, format: weekScaleTemplate},
    ];

    gantt.plugins({
        tooltip: true
    });
    gantt.config.xml_date = "%d/%m/%Y";
    gantt.config.autosize = true;
    gantt.config.scroll_size = 20;
    gantt.config.tooltip_timeout = 50;

    gantt.init("project_planning_content");
    gantt.load(`/api/projet/${projectId}/planning/list`);

    var dp = new gantt.dataProcessor(`/api/projet/${projectId}/planning`);
    dp.init(gantt);
    dp.setTransactionMode("REST");
});
