import $ from "jquery";

import '../../../node_modules/dhtmlx-gantt/codebase/ext/dhtmlxgantt_tooltip';
import '../../../node_modules/dhtmlx-gantt/codebase/ext/dhtmlxgantt_drag_timeline';
import '../../../node_modules/dhtmlx-gantt/codebase/ext/dhtmlxgantt_click_drag';

if (window['project_planning_content']){
    const projectId = window['project_planning_content'].dataset.projectId;
    const societeRaisonSociale = window['project_planning_content'].dataset.societeRaisonSociale;
    const projectAcronyme = window['project_planning_content'].dataset.projectAcronyme;
    const projectStartDate = (window['project_planning_content'].dataset.startDate).split('-');
    const readonly = parseInt(window['project_planning_content'].dataset.canEdit) === 0;

    var progressEditor = {type: "number_progress", map_to: "progress"};
    var numberEditor = gantt.config.editor_types.number;
    gantt.config.editor_types.number_progress = gantt.mixin({
        set_value: function(value, id, column, node){
            return numberEditor.set_value.apply(this, [Math.round((value * 100)), id, column, node]);
        },
        get_value: function(id, column, node) {
            return numberEditor.get_value.apply(this, [id, column, node]) /100;
        },
    }, numberEditor);

    gantt.config.columns = [
        {name: "wbs", label: "#", width: 40, min_width: 40, max_width: 40, align: "center", template: gantt.getWBSCode},
        {name: "text", tree: true, width: '*', min_width: 250, resize: true },
        {name: "start_date", label: "Start", width:80, min_width: 80, max_width: 80, align: "center", editor: {type: "date", map_to: "start_date"}, resize: true},
        {name: "duration", width:50, min_width: 50, max_width: 50, align: "center", editor: {type: "number", map_to: "duration", min:1}, resize: true},
        {name: "end_date", label: "Finish", width:80, min_width: 80, max_width: 80, align: "center", editor: {type: "date", map_to: "end_date"}, resize: true},
        {name: "progress", label: "Progress", align:"center", width:50, min_width: 50, max_width: 50, editor: progressEditor, template : function(obj){ return Math.round((obj.progress * 100)) + "%" }},
        {name: "add_custom", align: "center", label:"", width: 30, min_width: 30, max_width: 30, template: function (task){ return `<a href="javascript:;" class="add-sub-task" data-task-id="${task.id}" title="Ajouter une tâche"><i class="fa fa-plus"></i></a>` }},
        {name: "people", align: "center", label:"People", width: 44, min_width: 44, max_width: 44, template:function(task){ return `<a href="javascript:;" class="show-assigned-to-task" title="Utilisateurs affectés" data-task-id="${task.id}"><i class="fa fa-users"></i></a>` } },
        {name: "fait_marquants", align: "center", label:"FM", width: 30, min_width: 30, max_width: 30, template:function(task){ return task.$level === 0 && task.id ? `<a href="/corp/projet/${projectId}/planning/task/${task.id}" title="Liste des faits marquants liés" target="_blank"><i class="fa fa-eye"></i></a>` : '' } }
    ];

    gantt.locale.labels.section_text = 'Title';
    gantt.config.buttons_left = ["gantt_cancel_btn", "gantt_delete_btn"];
    gantt.config.buttons_right = ["gantt_save_btn"];

    gantt.config.lightbox.sections = [
        {name:"text", height:40, map_to:"text", type:"textarea",focus:true},
        {name:"description", height:80, map_to:"description", type:"textarea"},
        {name:"time", height:40, type:"duration", map_to:"auto"}
    ];

    var format = gantt.date.date_to_str("%Y-%m-%d");
    gantt.templates.tooltip_text = function(start,end,task){
        let tooltipText = `<b>Task:</b> ${task.text}<br/>`;
        if(task.description){
            tooltipText += `<b>Description:</b> ${task.description}<br/>`
        }
        tooltipText += `<b>Start date:</b> ${format(start)}<br/>
                <b>End date:</b> ${format(end)}`;
        return tooltipText;
    };

    gantt.ext.inlineEditors.attachEvent("onBeforeEditStart", function(state){
        if (state.columnName === "progress"){
            var task = gantt.getTask(state.id);
            return gantt.getChildren(task.id).length === 0;
        }
        return true;
    });

    gantt.templates.task_end_date = function(date){
        return gantt.templates.task_date(new Date(date.valueOf() - 1));
    };

    var gridDateToStr = gantt.date.date_to_str("%Y-%m-%d");
    gantt.templates.grid_date_format = function(date, column){
        if(column === "end_date"){
            return gridDateToStr(new Date(date.valueOf() - 1));
        }else{
            return gridDateToStr(date);
        }
    }

    $('#project_planning_content').on('click', '.add-sub-task', function () {
        if($(this).data('taskId'))
            gantt.createTask({id: gantt.uid(), text: "New Task"}, parseInt($(this).data('taskId')));
    });

    gantt.templates.task_class = gantt.templates.grid_row_class = function (start, end, task) {
        let classNames = '';
        if (gantt.getChildren(task.id).length > 0){
            classNames += ' hide_progress_drag';
        }
        switch (task.$level) {
            case 0:
                classNames += " lot_level";break;
            case 1:
                classNames +=  " task_level";break;
            case 2:
                classNames +=  " subtask_level";break;
        }

        return classNames;
    };

    gantt.templates.task_text = function (start, end, task) {
        return `<span class="task-title" title="${task.text}">${task.text}</span>`
    };

    var weekScaleTemplate = function(date){
        var dateToStr = gantt.date.date_to_str("%d %M");
        var endDate = gantt.date.add(gantt.date.add(date, 1, "week"), -1, "day");
        return dateToStr(date) + " - " + dateToStr(endDate);
    };

    gantt.config.scales = [
        {unit: "month", step: 1, format: "%F, %Y"},
        {unit: "week", step: 1, format: weekScaleTemplate},
    ];

    if (projectStartDate.length === 3) {
        gantt.config.start_date = new Date(parseInt(projectStartDate[0]), parseInt(projectStartDate[1]) - 1, parseInt(projectStartDate[2]));
    }
    gantt.config.fit_tasks = true;
    gantt.config.smart_rendering = true;
    gantt.config.auto_scheduling = true;
    gantt.config.readonly = readonly;
    gantt.config.xml_date = "%d/%m/%Y";
    gantt.config.autosize = true;
    gantt.config.scroll_size = 20;
    gantt.config.tooltip_timeout = 50;
    gantt.config.drag_progress = true;
    gantt.config.drag_links = false;
    gantt.config.drag_timeline = {
        ignore:".gantt_task_line, .gantt_task_link",
        useKey: false
    };

    gantt.init("project_planning_content");

    fetch(`/corp/api/projet/${projectId}/planning/list`, {method: 'GET'})
        .then(response => response.json())
        .then(data => {
            gantt.parse(data);
        });

    var dp = gantt.createDataProcessor({
        url: `/corp/api/projet/${projectId}/planning`,
        mode:"REST"
    });
    dp.attachEvent("onAfterUpdate", function(id, action, tid, response){
        let task = gantt.isTaskExists(tid) ? gantt.getTask(tid) : null;

        if (task !== null){
            if (response.action !== "deleted" && task.parent !== 0) {
                updateParentProgress(task);
            }

            if (response.linkFaitMarquant){
                $('#createFaitMarquant').find('.link-fait-marquant').attr("href", response.linkFaitMarquant);
                $('#createFaitMarquant').modal('show');
            }
        }
    });
    gantt.attachEvent("onBeforeTaskDelete", function(id, task){
        updateParentProgress(task, true);
    });

    gantt.attachEvent("onTaskLoading", function(task){
        task.editable = !readonly || task.is_participant;
        return true;
    });
    gantt.attachEvent("onTaskCreated", function(task){
        return gantt.calculateTaskLevel(task) <= 2;
    });
    $('.gantt-controls').on('click', 'a[data-action="addTask"]', function () {
        gantt.createTask(null, null);
    });

// START :: Menu nav bar

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

// END :: Menu nav bar

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
    gantt.ext.zoom.setLevel("quarter");

// END :: zoom function

// START :: my tasks filter

    var myTasksFilterEnable = false;
    $(document).on('click', 'a[data-action="myTasksFilter"]', function () {
        var $a = $(this);
        myTasksFilterEnable = $($a).data('checked') == false;
        $($a).find('i').toggleClass( 'fa-square-o', !myTasksFilterEnable ).toggleClass( 'fa-check-square-o', myTasksFilterEnable );
        $($a).data('checked', myTasksFilterEnable);
        gantt.render();
    });
    gantt.attachEvent("onBeforeTaskDisplay", function(id, task){
        if(!myTasksFilterEnable) return true;
        return task.is_participant;
    });

// END :: my tasks filter

    const updateParentProgress = (child, isDeleted = false) => {
        if (child.parent){
            let parentTask = gantt.getTask(child.parent);
            let childs = gantt.getChildren(parentTask.id);
            let totProgress = 0;

            var tempTask;
            var countChilds = 0;
            for (var i = 0; i < childs.length; i++) {
                tempTask = gantt.getTask(childs[i]);
                if (!isDeleted || (isDeleted && child.id !== tempTask.id)){
                    totProgress += parseFloat(tempTask.progress);
                    countChilds++;
                }
            }

            parentTask.progress = countChilds > 0 ? (totProgress / countChilds).toFixed(2) : 0;
            gantt.updateTask(parentTask.id);
        }
    };
}