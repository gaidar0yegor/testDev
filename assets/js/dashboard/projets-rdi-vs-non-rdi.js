import c3 from 'c3';

const currentYear = (new Date()).getFullYear();

const chart = c3.generate({
    bindto: '#projets-rdi-vs-non-rdi',
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

const yearsAxisSince = sinceYear => {
    const yearsAxis = ['_projects_year'];

    for (let i = sinceYear; i <= currentYear; ++i) {
        yearsAxis.push(i);
    }

    return yearsAxis;
}

window.addEventListener('loadSinceYearCharts', event => {
    const {year} = event.detail;

    chart.unload();

    setTimeout(() => {
        fetch(`/api/dashboard/projets-type/since-${year}`)
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
