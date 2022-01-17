import c3 from 'c3';

const generateChart = bindtoId => {
    return c3.generate({
        bindto: `#${bindtoId}`,
        data: {
            type : 'pie',
            columns: [
                ['En cours', 3],
                ['Terminés', 1],
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
};

window.addEventListener('loadSinceYearCharts', event => {
    const {societeUser} = event.detail;
    const {year} = event.detail;
    const chart = generateChart(`projets-statuts-${societeUser}`);

    fetch(`/api/multiSociete/dashboard/projets-statuts/${societeUser}/since-${year}`)
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
