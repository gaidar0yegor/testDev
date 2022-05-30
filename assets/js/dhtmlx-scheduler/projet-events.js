import {locale} from "./dhtmlx-scheduler-imports";

const projectId = window['project_events_calendar'].dataset.projectId;

// START :: Custom Attach Events
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
    if (event.readonly){
        scheduler.formSection("eventType").node.innerHTML = locale.types[event.eventType];
    }
});
// END :: Custom Attach Events

// START :: API
scheduler.init("project_events_calendar", new Date(), "month");
scheduler.load(`/api/projet/${projectId}/evenement`);

var dp = new dataProcessor(`/api/projet/${projectId}/evenement`);
dp.init(scheduler);
dp.setTransactionMode("REST", false);