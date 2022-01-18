import c3 from 'c3';

const chart = c3.generate({
    bindto: '#projets-statuts',
    data: {
        type : 'pie',
        columns: [
            ['En cours', 0],
            ['Terminés', 0],
            ['Suspendus', 0], // Fake data, commercial purpose
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

window.addEventListener('loadSinceYearCharts', event => {
    const {year} = event.detail;

    fetch(`/api/dashboard/projets-statuts/since-${year}`)
        .then(response => response.json())
        .then(heuresParProjet => {
            chart.load({
                columns: [
                    ['En cours', heuresParProjet.active],
                    ['Terminés', heuresParProjet.finished],
                    ['Suspendus', heuresParProjet.suspended]
                ],
            });
        })
    ;
});
