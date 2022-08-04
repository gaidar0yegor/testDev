import c3 from 'c3';
import datesLocalize from './../dates.localize';

const chartDiv = window['chart-projet-users-temps'];

if (chartDiv) {
    const chart = c3.generate({
        bindto: chartDiv,
        data: {
            type: 'bar',
            order: 'asc',
            columns: [],
        },
        axis: {
            x: {
                type: 'category',
                categories: datesLocalize.monthsShort,
            },
        },
        bar: {
            width: {
                ratio: 0.8,
            },
        },
        transition: {
            duration: 100,
        },
    });

    window.addEventListener('projet-chart-year-changed', event => {
            chart.unload();

            setTimeout(() => {
                fetch(`/corp/api/stats/temps-par-user/${chartDiv.dataset.projetId}/${event.detail.year}/${event.detail.unit}`)
                    .then(response => response.json())
                    .then(tempsParUsers => {
                        const total = {};

                        tempsParUsers.months.forEach(month => {
                            Object.entries(month).forEach(([user, value]) => {
                                if (!total[user]) {
                                    total[user] = 0;
                                }

                                total[user] += value;
                            });
                        });

                        const columns = Object
                            .keys(total)
                            .sort((a, b) => total[b] - total[a])
                            .map(projet => ([
                                projet,
                                ...tempsParUsers.months.map(month => month[projet] ?? 0),
                            ]))
                        ;

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
        },
    );
}

