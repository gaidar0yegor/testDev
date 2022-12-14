import c3 from 'c3';

const chart = c3.generate({
    bindto: '#projets-statuts',
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
    padding: {
        bottom: 20
    }
});

window.addEventListener('loadSinceYearCharts', event => {
    const {year} = event.detail;

    chart.unload();

    setTimeout(() => {
        fetch(`/corp/api/dashboard/projets-statuts/since-${year}`)
            .then(response => response.json())
            .then(heuresParProjet => {
                chart.load({
                    unload: true,
                    columns: [
                        ['En coursㅤㅤ', heuresParProjet.active],
                        ['Terminésㅤㅤ', heuresParProjet.finished],
                        ['Suspendusㅤㅤ', heuresParProjet.suspended]
                    ],
                });

                document.querySelector('[data-target-highlight="projets-statuts"]').innerText = heuresParProjet.active + ' en cours';
            })
        ;
    }, 1000);
});
