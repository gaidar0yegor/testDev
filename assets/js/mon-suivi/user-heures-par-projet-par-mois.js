import c3 from 'c3';
import {formatHours} from './../dashboard/utils';
import datesLocalize from './../dates.localize';
import userContext from './../userContext';

const chart = c3.generate({
    bindto: '#user-heures-par-projet-par-mois',
    data: {
        type: 'bar',
        order: 'asc',
        columns: [],
        groups: []
    },
    axis: {
        x: {
            type: 'category',
            categories: datesLocalize.monthsShort,
            tick: {
                rotate: 30,
            }
        },
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

    setTimeout(function () {
        fetch(`/corp/api/stats/temps-par-projet/${userContext.societeUserId}/${year}/hour`)
            .then(response => response.json())
            .then(tempsParProjets => {
                const total = {};

                tempsParProjets.months.forEach(month => {
                    Object.entries(month).forEach(([projet, value]) => {
                        if (!total[projet]) {
                            total[projet] = 0;
                        }

                        total[projet] += value;
                    });
                });

                const columns = Object
                    .keys(total)
                    .sort((a, b) => total[b] - total[a])
                    .map(projet => ([
                        projet,
                        ...tempsParProjets.months.map(month => month[projet] ?? 0),
                    ]))
                ;

                if (0 === columns.length) {
                    return;
                }

                chart.load({
                    unload: true,
                    columns: columns
                });

                chart.groups([
                    Object.keys(total),
                ]);

            })
        ;
    }, 1000);

});

