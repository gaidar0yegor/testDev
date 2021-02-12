import c3 from 'c3';
import datesFr from './dates.fr';

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
                categories: datesFr.monthsShort,
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

    window.addEventListener(
        'projet-chart-year-changed',
        e => {
            chart.unload();

            fetch(`/api/stats/admin/temps-par-user/${chartDiv.dataset.projetId}/${e.detail.year}`)
                .then(response => response.json())
                .then(tempsParUsers => {
                    const total = {};

                    tempsParUsers.months.forEach(month => {
                        Object.entries(month).forEach(([user, pourcentage]) => {
                            if (!total[user]) {
                                total[user] = 0;
                            }

                            total[user] += pourcentage;
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

                    setTimeout(() => {
                        chart.load({
                            columns,
                        });

                        chart.groups([
                            Object.keys(total),
                        ]);
                    }, 500);
                })
            ;
        },
    );
}

