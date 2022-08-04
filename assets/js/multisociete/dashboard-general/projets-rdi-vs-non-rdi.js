import c3 from 'c3';
import {dashboardContentsId} from '../utils';


const contentDiv = window[dashboardContentsId.general];

if (contentDiv){
    const currentYear = (new Date()).getFullYear();

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
                    ratio: 0.6,
                },
            },
            color: {
                pattern: [
                    '#1f77b4',
                    '#28a745',
                ],
            },
        });
    };

    const yearsAxisSince = sinceYear => {
        const yearsAxis = ['_projects_year'];

        for (let i = sinceYear; i <= currentYear; ++i) {
            yearsAxis.push(i);
        }

        return yearsAxis;
    }

    window.addEventListener('loadSinceYearCharts', event => {
        const {societeUser} = event.detail;
        const {year} = event.detail;
        const chart = generateChart(`projets-rdi-vs-non-rdi-${societeUser}`);

        chart.unload();

        setTimeout(() => {
            fetch(`/corp/api/mes-societes/dashboard/general/projets-type/${societeUser}/since-${year}`)
                .then(response => response.json())
                .then(projetsType => {
                    const nbProjets = ['Projets'];
                    const nbProjetsRdi = ['dont projets RDI'];

                    for (let i = year; i <= currentYear; ++i) {
                        nbProjets.push(projetsType[i].projets);
                        nbProjetsRdi.push(projetsType[i].projetsRdi);
                    }

                    chart.load({
                        unload: true,
                        columns: [
                            yearsAxisSince(year),
                            nbProjets,
                            nbProjetsRdi,
                        ],
                    });
                })
            ;
        }, 1000);
    });

}