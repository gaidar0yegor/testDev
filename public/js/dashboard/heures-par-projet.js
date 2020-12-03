(function (c3, fetch, window) {
    'use strict';

    const {formatHours} = window;

    const chart = c3.generate({
        bindto: '#heures-par-projet',
        data: {
            type: 'bar',
            x: '_projects_year',
            columns: [],
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

    window.addEventListener('loadYearlyCharts', event => {
        const {year} = event.detail;

        chart.unload();

        fetch(`/api/dashboard/heures-par-projet/${year}`)
            .then(response => response.json())
            .then(heuresParProjet => {

                heuresParProjet._projects_year = year;

                chart.load({
                    columns: Object.keys(heuresParProjet).map(projetName => [projetName, heuresParProjet[projetName]]),
                });
            })
        ;
    });

})(c3, fetch, window);
