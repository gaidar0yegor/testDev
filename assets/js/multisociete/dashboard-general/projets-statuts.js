import c3 from 'c3';

const generateChart = bindtoId => {
    return c3.generate({
        bindto: `#dashboard-general #${bindtoId}`,
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
};

window.addEventListener('loadSinceYearCharts', event => {
    const {societeUser} = event.detail;
    const {year} = event.detail;
    const chart = generateChart(`projets-statuts-${societeUser}`);

    chart.unload();

    setTimeout(() => {
        fetch(`/api/multiSociete/dashboard/general/projets-statuts/${societeUser}/since-${year}`)
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
