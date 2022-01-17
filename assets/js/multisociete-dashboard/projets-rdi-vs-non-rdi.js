import c3 from 'c3';
import {formatHours} from "../dashboard/utils";

const currentYear = (new Date()).getFullYear();


const generateChart = bindtoId => {
    return c3.generate({
        bindto: `#${bindtoId}`,
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

    fetch(`/api/multiSociete/dashboard/projets-type/${societeUser}/since-${year}`)
        .then(response => response.json())
        .then(projetsType => {
            const nbProjets = ['Projets'];
            const nbProjetsRdi = ['dont projets RDI'];

            for (let i = year; i <= currentYear; ++i) {
                nbProjets.push(projetsType[i].projets);
                nbProjetsRdi.push(projetsType[i].projetsRdi);
            }

            chart.load({
                columns: [
                    yearsAxisSince(year),
                    nbProjets,
                    nbProjetsRdi,
                ],
            });
        })
    ;
});