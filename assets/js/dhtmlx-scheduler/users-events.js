import $ from "jquery";
import "./dhtmlx-scheduler-imports";

// START :: filter form
$('#select-all-users').click(function(event) {
    $('#filter_user_event_users :checkbox').each(function(key, elem) {
        elem.checked = event.currentTarget.checked;
    });
});
// END :: filter form

scheduler.locale.labels.section_autoUpdateCra = '';
scheduler.config.lightbox.sections.push(
    {
        name:"autoUpdateCra",
        height:30,
        type:"multiselect",
        map_to:"auto_update_cra",
        options:[ { key: true, label: 'Mettre à jour les absences dans le suivi du temps ?' } ]
    }
);

// START :: API
scheduler.init("societe_users_events_calendar", new Date(), "month");
scheduler.load("/corp/api/utilisateur/evenement?" + $('form[name="filter_user_event"]').serialize());

var dp = new dataProcessor(`/corp/api/utilisateur/evenement`);
dp.init(scheduler);
dp.setTransactionMode("REST", false);