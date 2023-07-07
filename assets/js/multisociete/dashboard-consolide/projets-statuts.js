import c3 from 'c3';
import {dashboardContentsId} from '../utils';

const contentDiv = window[dashboardContentsId.consolide];

if (contentDiv){
    const generateChart = bindtoId => {
        return c3.generate({
            bindto: `#${dashboardContentsId.consolide} #${bindtoId}`,
            data: {
                type : 'pie',
                columns: [
                    ['En cours', 0],
                    ['Terminés', 0],
                    ['Suspendus', 0],
                ],
            },
            pie: {
                label: {
                    format: function (value, ratio, id) {
                        return value;
                    }
                }
            },
            color: {
                pattern: [
                    '#1f77b4',
                    '#28a745',
                    '#ffc107',
                ],
            },
        });
    };

    window.addEventListener('loadSinceYearCharts', event => {
        const {dashboardConsolide} = event.detail;
        const {year} = event.detail;
        const chart = generateChart(`projets-statuts`);

        chart.unload();

        setTimeout(() => {
            fetch(`/corp/api/mes-societes/dashboard/consolide/projets-statuts/since-${year}/${dashboardConsolide ? dashboardConsolide : ""}`)
                .then(response => response.json())
                .then(heuresParProjet => {
                    chart.load({
                        unload: true,
                        columns: [
                            ['En cours', heuresParProjet.active],
                            ['Terminés', heuresParProjet.finished],
                            ['Suspendus', heuresParProjet.suspended]
                        ],
                    });
                })
            ;
        }, 1000);
    });

}