const projectId = window['project_events_calendar'].dataset.projectId;
const selectedEvent = window['project_events_calendar'].dataset.selectedEvent;

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
        scheduler.getLightbox().querySelector('.dhx_agenda_sync_btn').remove();
    }
    if (scheduler.formSection("text")){
        var textarea = scheduler.formSection("text").control;
        textarea.oninput = function () {
            if (this.value.length > 250) {
                this.value = this.value.substring(0, 250);
                dhtmlx.message({text: "Title is too long", type: "error"});
            }
        };
    }
});
scheduler.attachEvent("onSaveError", function(ids, response){
    dhtmlx.message({text: JSON.parse(response.responseText).message, type: "error"});
});
// END :: Custom Attach Events

// START :: Agenda
scheduler.locale.labels.agenda_tab = "Agenda";
scheduler.templates.agenda_time = function(start, end, event){
    return scheduler.templates.format_date(start) + " &ndash; " +
        (scheduler.isOneDayEvent(event) ? scheduler.templates.event_date(end) : scheduler.templates.format_date(end));
};
// END :: Agenda

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
scheduler.locale.labels.section_text = "Title";
scheduler.locale.labels.section_eventType = 'Type';
scheduler.locale.labels.section_userSelect = "Participants";
scheduler.config.details_on_dblclick = true;
scheduler.config.buttons_right = ["dhx_delete_btn", "dhx_agenda_sync_btn"];
scheduler.locale.labels["dhx_agenda_sync_btn"] = "Send to my agenda";
scheduler.config.lightbox.sections = [
    { name:"text", height:40, map_to:"text", type:"textarea", focus:true },
    { name:"description", height:80, map_to:"description", type:"textarea" },
    { name:"eventType", height:30, map_to:"eventType", type:"select", options: scheduler.serverList("eventTypes") },
    { name:"userSelect", height:70, map_to:"participant_id", type:"multiselect", options: scheduler.serverList("participants") },
    { name:"time", height:72, type:"time", map_to:"auto" }
];
// END :: Custom configs

// START :: API
scheduler.init("project_events_calendar", new Date(), "month");
scheduler.load(`/api/projet/${projectId}/events`);

var dp = new dataProcessor(`/api/projet/${projectId}/events`);
dp.init(scheduler);
dp.setTransactionMode("REST", false);