import "./dhtmlx-scheduler-imports";

const projectId = window['project_events_calendar'].dataset.projectId;

// START :: API
scheduler.init("project_events_calendar", new Date(), "month");
scheduler.load(`/api/projet/${projectId}/evenement`);

var dp = new dataProcessor(`/api/projet/${projectId}/evenement`);
dp.init(scheduler);
dp.setTransactionMode("REST", false);