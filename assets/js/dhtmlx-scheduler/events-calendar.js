import locale_fr from './locale/locale.fr';
import locale_en from './locale/locale.en';
import { detectedLocale } from './../translation';
let locale = detectedLocale === 'en' ? locale_en : locale_fr;

import apiGenerateIcsCalendar from './ics-export-api';
import $ from "jquery";

const projectId = window['project_events_calendar'].dataset.projectId;
const selectedEvent = window['project_events_calendar'].dataset.selectedEvent;

Scheduler.plugin(function(scheduler){ scheduler.locale = locale });

// START :: Disable inline edit
scheduler.config.icons_select = [ "icon_details", "icon_delete" ];
scheduler.edit = function(event){ return null; };
// END :: Disable inline edit

// START :: Custom Attach Events
scheduler.attachEvent("onParse", function(){
    scheduler.showEvent(selectedEvent,"week");
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
    }
});
$(document).on('click', '.dhx_cal_light_readonly .dhx_ics_calendar_btn_set', function (e) { // to generate ics file on readonly event
    let event = scheduler.getEvent(scheduler.getState().lightbox_id);
    if(event.is_invited){
        apiGenerateIcsCalendar(projectId, event.id)
    }
});
scheduler.attachEvent("onLightboxButton", function (id, node, e){ // to generate ics file on not readonly event
    let event = scheduler.getEvent(scheduler.getState().lightbox_id);
    if(id === "dhx_ics_calendar_btn" && event.is_invited){
        apiGenerateIcsCalendar(projectId, event.id)
    }
});
scheduler.attachEvent("onEventSave", function(id,event){
    if (!event.text) {
        dhtmlx.alert(locale.alerts.title_must_not_be_empty);
        return false;
    }
    return true;
});
scheduler.attachEvent("onSaveError", function(ids, response){
    dhtmlx.alert(JSON.parse(response.responseText).message);
});
// END :: Custom Attach Events

// START :: Custom colors
scheduler.templates.event_class = function (start, end, event) {
    switch (event.eventType) {
        case 'MEETING': return "meeting_projetEvent";
        case 'EVENT': return "event_projetEvent";
        case 'OTHER': return "other_projetEvent";
        default: return "";
    }
};
// END :: Custom colors

// START :: Agenda
scheduler.locale.labels.agenda_tab = "Agenda";
scheduler.templates.agenda_time = function(start, end, event){
    return scheduler.templates.format_date(start) + " &ndash; " +
        (scheduler.isOneDayEvent(event) ? scheduler.templates.event_date(end) : scheduler.templates.format_date(end));
};
scheduler.templates.agenda_text = function(start, end, event){
    return `${event.text}<small class="float-right mr-2"><i>${locale.labels.created_by} : ${event.createdByFullname}</i></small>`;
};
// END :: Agenda

// START :: LightBox
scheduler.templates.lightbox_header = function(start,end,ev){
    return scheduler.templates.event_header(ev.start_date,ev.end_date,ev)
    + (ev.createdByFullname ? `&nbsp;&nbsp;&nbsp;${locale.labels.created_by} : <b>${ev.createdByFullname}</b>` : '');
};
$(document).on('click', '.dhx_wrap_section .select-all', function (e) {
    e.preventDefault();
    $(this).parents('.dhx_wrap_section').find('input[type=checkbox]').prop('checked', $(this).data('state') !== 'selected');
    $(this).data('state', $(this).data('state') === 'selected' ? 'unselected' : 'selected')
});
// END :: LightBox

// START :: Read only
function block_readonly(id){
    if (!id) return true;
    return !this.getEvent(id).readonly;
}
scheduler.attachEvent("onBeforeDrag",block_readonly);
scheduler.attachEvent("onClick",block_readonly);
// END :: Read only

// START :: Custom configs
scheduler.locale.labels.new_event = "";
scheduler.locale.labels.section_text = locale.labels.section_text;
scheduler.locale.labels.section_location = locale.labels.section_location;
scheduler.locale.labels.section_eventType = locale.labels.section_eventType;
scheduler.locale.labels.section_requiredParticipant = `${locale.labels.invitation}<br/><a href='javascript:;' class='select-all text-left' data-state='unselected'><small>${locale.labels.select_all}</small></a>`;
scheduler.locale.labels.section_optionalParticipant = `${locale.labels.information}<br/><a href='javascript:;' class='select-all text-left' data-state='unselected'><small>${locale.labels.select_all}</small></a>`;
scheduler.config.details_on_dblclick = true;
scheduler.config.buttons_right = ["dhx_delete_btn", "dhx_ics_calendar_btn"];
scheduler.locale.labels["dhx_ics_calendar_btn"] = locale.labels.dhx_ics_calendar_btn;
scheduler.config.lightbox.sections = [
    { name:"text", height:40, map_to:"text", type:"textarea", focus:true },
    { name:"description", height:80, map_to:"description", type:"textarea" },
    { name:"eventType", height:30, map_to:"eventType", type:"select", options: scheduler.serverList("eventTypes") },
    { name:"location", height:30, map_to:"location", type:"textarea" },
    { name:"requiredParticipant", height:70, map_to:"required_participant_ids", type:"multiselect", options: scheduler.serverList("participants") },
    { name:"optionalParticipant", height:70, map_to:"optional_participant_ids", type:"multiselect", options: scheduler.serverList("participants") },
    { name:"time", height:72, type:"time", map_to:"auto" }
];
// END :: Custom configs

// START :: API
scheduler.init("project_events_calendar", new Date(), "month");
scheduler.load(`/api/projet/${projectId}/events`);

var dp = new dataProcessor(`/api/projet/${projectId}/events`);
dp.init(scheduler);
dp.setTransactionMode("REST", false);