import c3 from 'c3';
import { t } from './../translation';

const chartContents = window['planning-tasks-statuts'];

if (chartContents) {
    const projectId = chartContents.dataset.projetId;

    const chart = c3.generate({
        bindto: '#planning-tasks-statuts',
        data: {
            type: 'donut',
            columns: [],
        },
        donut: {
            title: "Planification"
        },
        color: {
            pattern: [
                '#1f77b4',
                '#28a745',
                '#ffc107',
            ],
        },
    });

    fetch(`/corp/api/stats/tasks-status/${projectId}`)
        .then(response => response.json())
        .then(datas => {
            chart.load({
                unload: true,
                columns: [
                    [t("in_progress") + ` (${datas["in_progress"]})`, datas["in_progress"]],
                    [t("ended") + ` (${datas["ended"]})`, datas["ended"]],
                    [t("upcoming") + ` (${datas["upcoming"]})`, datas["upcoming"]],
                ],
            });
        })
    ;

}