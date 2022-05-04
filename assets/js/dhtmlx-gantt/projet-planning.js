const projectId = window['project_planning_content'].dataset.projectId;
const societeRaisonSociale = window['project_planning_content'].dataset.societeRaisonSociale;
const projectAcronyme = window['project_planning_content'].dataset.projectAcronyme;
const readonly = parseInt(window['project_planning_content'].dataset.canEdit) === 0;

gantt.config.columns = [
    {name: "wbs", label: "#", width: 40, min_width: 40, max_width: 40, align: "center", template: gantt.getWBSCode},
    {name: "text", tree: true, width: 250, min_width: 250, max_width: 250, resize: true},
    {name: "start_date", label: "Start", width:80, min_width: 80, max_width: 80, align: "center", editor: {type: "date", map_to: "start_date"}, resize: true},
    {name: "duration", width:50, min_width: 50, max_width: 50, align: "center", editor: {type: "number", map_to: "duration", min:1}, resize: true},
    {name: "end_date", label: "Finish", width:80, min_width: 80, max_width: 80, align: "center", editor: {type: "date", map_to: "end_date"}, resize: true},
    {name: "progress", label: "Progress", align:"center", width:50, min_width: 50, max_width: 50, editor: {type: "number", map_to: "progress", min:0}, template : function(obj){ return (Math.round(obj.progress * 100)) + "%" }},
    {name: "add", align: "center", width: 30, min_width: 30, max_width: 30},
    {name: "people", align: "center", label:"People", width: 44, min_width: 44, max_width: 44, template:function(task){ return `<a href="javascript:;" class="show-assigned-to-task" data-task-id="${task.id}"><i class="fa fa-users"></i></a>` } },
    {name: "fait_marquants", align: "center", label:"FM", width: 30, min_width: 30, max_width: 30, template:function(task){ return task.$level === 0 && task.id ? `<a href="/projet/${projectId}/planning/task/${task.id}" target="_blank"><i class="fa fa-eye"></i></a>` : '' } }
];

gantt.templates.grid_row_class = function( start, end, task ){
    switch (task.$level) {
        case 0: return "lot_level";
        case 1: return "task_level";
        case 2: return "subtask_level";
        default: return "";
    }
};

gantt.attachEvent("onTaskCreated", function(task){
    return gantt.calculateTaskLevel(task) <= 2;
});

var weekScaleTemplate = function(date){
    var dateToStr = gantt.date.date_to_str("%d %M");
    var endDate = gantt.date.add(gantt.date.add(date, 1, "week"), -1, "day");
    return dateToStr(date) + " - " + dateToStr(endDate);
};

gantt.config.scales = [
    {unit: "month", step: 1, format: "%F, %Y"},
    {unit: "week", step: 1, format: weekScaleTemplate},
];

gantt.config.auto_scheduling = true;
gantt.config.readonly = readonly;
gantt.config.xml_date = "%d/%m/%Y";
gantt.config.autosize = true;
gantt.config.scroll_size = 20;
gantt.config.tooltip_timeout = 50;

gantt.init("project_planning_content");
gantt.load(`/api/projet/${projectId}/planning/list`);

var dp = new gantt.dataProcessor(`/api/projet/${projectId}/planning`);
dp.init(gantt);
dp.setTransactionMode("REST");

var menu = {
    zoomIn: function(){
        gantt.ext.zoom.zoomIn()
    },
    zoomOut: function(){
        gantt.ext.zoom.zoomOut()
    },
    collapseAll: function(){
        gantt.eachTask(function(task){
            task.$open = false;
        });
        gantt.render();

    },
    expandAll: function(){
        gantt.eachTask(function(task){
            task.$open = true;
        });
        gantt.render();
    },
    toPDF: function(){
        gantt.exportToPDF({
            name:`planification_projet_${projectAcronyme}.pdf`,
            header:`<h1 style="margin-left: 20px;">Planification du projet : ${projectAcronyme}</h1>`,
            footer:`<h4 style="margin-left: 20px;">${societeRaisonSociale}</h4>`,
            locale:"fr",
            raw:true
        });
    },
    toPNG: function(){
        gantt.exportToPNG({
            name:`planification_projet_${projectAcronyme}.png`,
            header:`<h1 style="margin-left: 20px;">Planification du projet : ${projectAcronyme}</h1>`,
            footer:`<h4 style="margin-left: 20px;">${societeRaisonSociale}</h4>`,
            locale:"fr",
            raw:true
        });
    },
    toExcel: function(){
        gantt.exportToExcel({
            name:`planification_projet_${projectAcronyme}.xlsx`,
        });
    },
    toMSProject: function(){
        gantt.exportToMSProject({
            name:`planification_projet_${projectAcronyme}.xml`,
        });
    }
};

var navBar = document.querySelector(".gantt-controls");
gantt.event(navBar, "click", function(e){
    var target = e.target || e.srcElement;
    while(!target.hasAttribute("data-action") && target !== document.body){
        target = target.parentNode;
    }

    if(target && target.hasAttribute("data-action")){
        var action = target.getAttribute("data-action");
        if(menu[action]){
            menu[action]();
        }
    }
});

// START :: zoom functionality

var zoomConfig = {
    levels: [
        {
            name:"day",
            scale_height: 27,
            min_column_width:80,
            scales:[
                {unit: "day", step: 1, format: "%d %M"}
            ]
        },
        {
            name:"week",
            scale_height: 50,
            min_column_width:50,
            scales:[
                {unit: "week", step: 1, format: function (date) {
                        var dateToStr = gantt.date.date_to_str("%d %M");
                        var endDate = gantt.date.add(date, 6, "day");
                        var weekNum = gantt.date.date_to_str("%W")(date);
                        return "#" + weekNum + ", " + dateToStr(date) + " - " + dateToStr(endDate);
                    }},
                {unit: "day", step: 1, format: "%j %D"}
            ]
        },
        {
            name:"month",
            scale_height: 50,
            min_column_width:120,
            scales:[
                {unit: "month", format: "%F, %Y"},
                {unit: "week", format: "Week #%W"}
            ]
        },
        {
            name:"quarter",
            height: 50,
            min_column_width:90,
            scales:[
                {unit: "month", step: 1, format: "%M"},
                {
                    unit: "quarter", step: 1, format: function (date) {
                        var dateToStr = gantt.date.date_to_str("%M");
                        var endDate = gantt.date.add(gantt.date.add(date, 3, "month"), -1, "day");
                        return dateToStr(date) + " - " + dateToStr(endDate);
                    }
                }
            ]},
        {
            name:"year",
            scale_height: 50,
            min_column_width: 30,
            scales:[
                {unit: "year", step: 1, format: "%Y"}
            ]}
    ]
};

gantt.ext.zoom.init(zoomConfig);

// END zoom function
