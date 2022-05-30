import $ from "jquery";
import {locale} from "./dhtmlx-scheduler-imports";

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
        scheduler.formSection("requiredParticipant").node.innerHTML = event.required_participants_names;
        scheduler.formSection("optionalParticipant").node.innerHTML = event.optional_participants_names;
    }
});
// END :: Custom Attach Events

// START :: filter form
$('#select-all-users').click(function(event) {
    if(this.checked) {
        $('#filter_user_event_users :checkbox').each(function() {
            this.checked = true;
        });
    } else {
        $('#filter_user_event_users :checkbox').each(function() {
            this.checked = false;
        });
    }
});
// END :: filter form

// START :: API
scheduler.init("societe_users_events_calendar", new Date(), "month");
scheduler.load("/api/utilisateur/evenement?" + $('form[name="filter_user_event"]').serialize());

var dp = new dataProcessor(`/api/utilisateur/evenement`);
dp.init(scheduler);
dp.setTransactionMode("REST", false);