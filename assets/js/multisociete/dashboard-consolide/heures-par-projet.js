import c3 from 'c3';
import {formatHours} from '../../dashboard/utils';
import {dashboardContentsId} from '../utils';

const contentDiv = window[dashboardContentsId.consolide];

if (contentDiv){
    const generateChart = bindtoId => {
        return c3.generate({
            bindto: `#${dashboardContentsId.consolide} #${bindtoId}`,
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
    };

    window.addEventListener('loadYearlyCharts', event => {
        const {dashboardConsolide} = event.detail;
        const {year} = event.detail;
        const chart = generateChart(`heures-par-projet`);

        chart.unload();

        setTimeout(() => {
            fetch(`/corp/api/mes-societes/dashboard/consolide/heures-par-projet/${year}/${dashboardConsolide ? dashboardConsolide : ""}`)
                .then(response => response.json())
                .then(datas => {
                    let heuresParProjet = datas.multisosieteProjetsHeuresPassees;

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

}