import c3 from 'c3';
import {formatHours} from './utils';

const chart = c3.generate({
    bindto: '#heures-par-projet',
    data: {
        type: 'bar',
        x: '_projects_year',
        columns: [],
    },
    bar: {
        width: {
            ratio: 1,
        },
    },
    axis: {
        y: {
            tick: {
                format: formatHours,
            },
        },
    },
});

window.addEventListener('loadYearlyCharts', event => {
    const {year} = event.detail;

    chart.unload();

    fetch(`/api/dashboard/heures-par-projet/${year}`)
        .then(response => response.json())
        .then(heuresParProjet => {

            heuresParProjet._projects_year = year;

            chart.load({
                columns: Object.keys(heuresParProjet).map(projetName => [projetName, heuresParProjet[projetName]]),
            });
        })
    ;
});
