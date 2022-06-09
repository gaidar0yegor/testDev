import '../../node_modules/c3/c3.css';
import c3 from 'c3';
import datesLocalize from './dates.localize';

const chartDiv = window['chart-user-projets-temps'];

if (chartDiv) {
    const chart = c3.generate({
        bindto: chartDiv,
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
        }
    });

    window.addEventListener('user-chart-year-changed', event => {
            chart.unload();

            setTimeout(function () {
                fetch(`/corp/api/stats/temps-par-projet/${chartDiv.dataset.userId}/${event.detail.year}/${event.detail.unit}`)
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
                            unload:true,
                            columns: columns
                        });

                        chart.groups([
                            Object.keys(total),
                        ]);

                    });
            }, 1000);
        },
    );
}
