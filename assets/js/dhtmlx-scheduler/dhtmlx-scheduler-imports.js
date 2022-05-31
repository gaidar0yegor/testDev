import 'dhtmlx-scheduler/codebase/dhtmlxscheduler_terrace.css';
import './style.css';

import 'dhtmlx-scheduler/codebase/dhtmlxscheduler';
import 'dhtmlx-scheduler/codebase/ext/dhtmlxscheduler_container_autoresize';
import 'dhtmlx-scheduler/codebase/ext/dhtmlxscheduler_multiselect';
import 'dhtmlx-scheduler/codebase/ext/dhtmlxscheduler_tooltip';
import 'dhtmlx-scheduler/codebase/ext/dhtmlxscheduler_agenda_view';
import 'dhtmlx-scheduler/codebase/ext/dhtmlxscheduler_readonly';
import 'dhtmlx-scheduler/codebase/ext/dhtmlxscheduler_year_view';
import './ext/custom_full_day';

import locale_fr from './locale/locale.fr';
import locale_en from './locale/locale.en';
import { detectedLocale } from './../translation';
import $ from "jquery";
import apiGenerateIcsCalendar from "./ext/ics-export-api";

var locale = detectedLocale === 'en' ? locale_en : locale_fr;

Scheduler.plugin(function(scheduler){ scheduler.locale = locale });

const queryParams = new Proxy(new URLSearchParams(window.location.search), {
    get: (searchParams, prop) => searchParams.get(prop),
});
let selectedEvent = queryParams.event;

// START :: Disable inline edit
scheduler.config.icons_select = [ "icon_details", "icon_delete" ];
scheduler.edit = function(event){ return null; };
// END :: Disable inline edit

// START :: Read only
function block_readonly(id){
    if (!id) return true;
    return !this.getEvent(id).readonly;
}
scheduler.attachEvent("onBeforeDrag",block_readonly);
scheduler.attachEvent("onClick",block_readonly);
// END :: Read only

scheduler.attachEvent("onParse", function(){
    scheduler.showEvent(selectedEvent,"week");
});

scheduler.attachEvent("onEventSave", function(id,event){
    if (!event.text) {
        dhtmlx.alert(locale.alerts.title_must_not_be_empty);
        return false;
    }
    if (event.required_participants_ids === "") {
        dhtmlx.alert(locale.alerts.minimum_invitation_is_required);
        return false;
    }

    return true;
});

scheduler.attachEvent("onSaveError", function(ids, response){
    dhtmlx.alert(JSON.parse(response.responseText).message);
});

scheduler.templates.lightbox_header = function(start,end,ev){
    return scheduler.templates.event_header(ev.start_date,ev.end_date,ev)
        + (ev.createdByFullname ? `&nbsp;&nbsp;&nbsp;${locale.labels.created_by} : <b>${ev.createdByFullname}</b>` : '')
        + (ev.projetAcronyme ? `<span class="float-right">${locale.labels.projet} : <b>${ev.projetAcronyme}</b></span>` : '')
        ;
};

$(document).on('click', '.dhx_wrap_section .select-all', function (e) {
    e.preventDefault();
    $(this).parents('.dhx_wrap_section').find('input[type=checkbox]').prop('checked', $(this).data('state') !== 'selected');
    $(this).data('state', $(this).data('state') === 'selected' ? 'unselected' : 'selected')
});

$(document).on('click', '.dhx_cal_light_readonly .dhx_ics_calendar_btn_set', function (e) { // to generate ics file on readonly event
    let event = scheduler.getEvent(scheduler.getState().lightbox_id);
    if(event.is_invited){
        apiGenerateIcsCalendar(event.id)
    }
});
scheduler.attachEvent("onLightboxButton", function (id, node, e){ // to generate ics file on not readonly event
    let event = scheduler.getEvent(scheduler.getState().lightbox_id);
    if(id === "dhx_ics_calendar_btn" && event.is_invited){
        apiGenerateIcsCalendar(event.id)
    }
});
scheduler.attachEvent("onLightbox", function (id) {
    const event = scheduler.getEvent(id);
    if (!event.is_invited) {
        scheduler.getLightbox().querySelector('.dhx_btn_set.dhx_ics_calendar_btn_set').remove();
    }
    if (!event.readonly){
        var textarea = scheduler.formSection("text").control;
        textarea.oninput = function () {
            if (this.value.length > 250) {
                this.value = this.value.substring(0, 250);
                dhtmlx.alert(locale.alerts.title_is_too_long);
            }
        };

        if (scheduler.getState().new_event){
            var checkboxAutoUpdateCra = scheduler.formSection("autoUpdateCra");
            if(checkboxAutoUpdateCra){
                checkboxAutoUpdateCra.node.querySelectorAll("input[type='checkbox']")[0].checked = true;
            }

            var checkboxRequiredParticipant = scheduler.formSection("requiredParticipant");
            if (checkboxRequiredParticipant && checkboxRequiredParticipant.node.querySelectorAll("input[type='checkbox']").length === 1){
                checkboxRequiredParticipant.node.querySelectorAll("input[type='checkbox']")[0].checked = true;
            }
        }
    }
    if (event.readonly){
        scheduler.formSection("eventType").node.innerHTML = locale.types[event.eventType];
        scheduler.formSection("requiredParticipant").node.innerHTML = event.required_participants_names;
        scheduler.formSection("optionalParticipant").node.innerHTML = event.optional_participants_names;
    }
});

scheduler.templates.event_class = function (start, end, event) {
    let classNames = selectedEvent == event.id ? 'highlighted_event' : '';
    switch (event.eventType) {
        case 'MEETING': return classNames + " meeting_projetEvent";
        case 'EVENT': return classNames +  " event_projetEvent";
        case 'ABSENCE': return classNames +  " absence_projetEvent";
        case 'OTHER': return classNames +  " other_projetEvent";
        default: return classNames;
    }
};

// START :: Agenda
scheduler.locale.labels.agenda_tab = "Agenda";
var dateToStr = scheduler.date.date_to_str("%d %M %Y %H:%i");
scheduler.templates.agenda_time = function(start, end, event){
    return dateToStr(start) + " &ndash; " +
        (scheduler.isOneDayEvent(event) ? scheduler.templates.event_date(end) : dateToStr(end));
};
scheduler.templates.agenda_text = function(start, end, event){
    return `${event.text}<small class="float-right mr-2"><i>${locale.labels.created_by} : ${event.createdByFullname}</i></small>`;
};
// END :: Agenda

scheduler.locale.labels.new_event = "";
scheduler.locale.labels.section_text = locale.labels.section_text;
scheduler.locale.labels.section_location = locale.labels.section_location;
scheduler.locale.labels.section_eventType = locale.labels.section_eventType;
scheduler.locale.labels.section_requiredParticipant = `${locale.labels.invitation}<br/><a href='javascript:;' class='select-all text-left' data-state='unselected'><small>${locale.labels.select_all}</small></a>`;
scheduler.locale.labels.section_optionalParticipant = `${locale.labels.information}<br/><a href='javascript:;' class='select-all text-left' data-state='unselected'><small>${locale.labels.select_all}</small></a>`;
scheduler.config.details_on_dblclick = true;
scheduler.config.event_duration = 60;
scheduler.config.auto_end_date = true;
scheduler.config.full_day = true;
scheduler.config.buttons_right = ["dhx_delete_btn", "dhx_ics_calendar_btn"];
scheduler.locale.labels["dhx_ics_calendar_btn"] = locale.labels.dhx_ics_calendar_btn;
scheduler.config.lightbox.sections = [
    { name:"text", height:30 , map_to:"text", type:"textarea", focus:true },
    { name:"description", height:50 , map_to:"description", type:"textarea" },
    { name:"eventType", height:30, map_to:"eventType", type:"select", options: scheduler.serverList("eventTypes") },
    { name:"location", height:30, map_to:"location", type:"textarea" },
    { name:"requiredParticipant", height:53, map_to:"required_participants_ids", type:"multiselect", options: scheduler.serverList("participants") },
    { name:"optionalParticipant", height:53, map_to:"optional_participants_ids", type:"multiselect", options: scheduler.serverList("participants") },
    { name:"time", height:72, type:"time", map_to:"auto", time_format:["%d","%m","%Y","%H:%i"] }
];

export {
     locale
};