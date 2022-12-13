import c3 from 'c3';

const currentYear = (new Date()).getFullYear();

const chart = c3.generate({
    bindto: '#projets-rdi-vs-non-rdi',
    data: {
        type: 'bar',
        x: '_projects_year',
        columns: [],
        groups: [
            ['dont RDI > 50%', 'dont RDI > 30%']
        ]
    },
    // legend: {
    //     position: 'inset',
    //     inset: {
    //             anchor: 'bottom',
    //             x: 150,
    //             y: -80,
    //             step: -1
    //         }
    // },
    bar: {
        width: {
            ratio: 0.6,
        },
    },
    color: {
        pattern: [
            '#1f77b4',
            '#f58c08',
            '#28a745',
        ],
    },
    padding: {
        bottom: 20
    }
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
        fetch(`/corp/api/dashboard/projets-type/since-${year}`)
            .then(response => response.json())
            .then(projetsType => {
                const nbProjets = ['Projetsㅤ'];
                const nbProjetsRdi50 = ['dont RDI > 50%'];
                const nbProjetsRdi30 = ['dont RDI > 30%ㅤㅤㅤ'];

                for (let i = year; i <= currentYear; ++i) {
                    nbProjets.push(projetsType[i].projets);
                    nbProjetsRdi50.push(projetsType[i].projetsRdi50);
                    nbProjetsRdi30.push(projetsType[i].projetsRdi30);
                }

                chart.load({
                    unload: true,
                    columns: [
                        yearsAxisSince(year),
                        nbProjets,
                        nbProjetsRdi30,
                        nbProjetsRdi50,
                    ],
                });

                const elligibleRDICurrentYear = projetsType[currentYear].projetsRdi50;
                document.querySelector('[data-target-highlight="projets-rdi-vs-non-rdi"]').innerText = elligibleRDICurrentYear +
                    (elligibleRDICurrentYear <= 1 ? ' projet éligible' : ' projets éligibles');
            })
        ;
    }, 1000);
});
