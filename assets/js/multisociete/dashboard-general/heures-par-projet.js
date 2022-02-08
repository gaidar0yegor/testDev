import c3 from 'c3';
import {formatHours} from '../../dashboard/utils';
import {dashboardContentsId} from '../utils';

const contentDiv = window[dashboardContentsId.general];

if (contentDiv){

    const generateChart = bindtoId => {
        return c3.generate({
            bindto: `#${dashboardContentsId.general} #${bindtoId}`,
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
    };



    window.addEventListener('loadYearlyCharts', event => {
        const {societeUser} = event.detail;
        const {year} = event.detail;
        const chart = generateChart(`heures-par-projet-${societeUser}`);

        chart.unload();

        setTimeout(() => {
            fetch(`/api/mes-societes/dashboard/general/heures-par-projet/${societeUser}/${year}`)
                .then(response => response.json())
                .then(heuresParProjet => {

                    heuresParProjet._projects_year = year;

                    chart.load({
                        unload: true,
                        columns: Object.keys(heuresParProjet).map(projetName => [projetName, heuresParProjet[projetName]]),
                    });
                })
            ;
        }, 1000);
    });

}