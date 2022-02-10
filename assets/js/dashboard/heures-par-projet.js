import c3 from 'c3';
import {formatHours} from './utils';

const chart = c3.generate({
    bindto: '#heures-par-projet',
    data: {
        type: 'bar',
        x: '_projects_year',
        columns: [],
        colors: [],
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

    setTimeout(function () {
        fetch(`/api/dashboard/heures-par-projet/${year}`)
            .then(response => response.json())
            .then(datas => {
                let heuresParProjet = datas.userProjetsHeuresPassees;

                heuresParProjet._projects_year = year;

                chart.load({
                    unload: true,
                    columns: Object.keys(heuresParProjet).map(projetName => [projetName, heuresParProjet[projetName]]),
                    colors: datas.codeColors,
                });
            })
        ;
    }, 1000);
});
