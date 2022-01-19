import c3 from 'c3';
import {formatHours} from './utils';
import datesLocalize from './../dates.localize';

const chart = c3.generate({
    bindto: '#user-heures-par-projet-par-mois',
    data: {
        type: 'bar',
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

    fetch(`/api/dashboard/mes-temps-annee/${year}`)
        .then(response => response.json())
        .then(tempsPerProjetPerMonth => {

            chart.load({
                columns: tempsPerProjetPerMonth.heures,
            });

            chart.groups([
                tempsPerProjetPerMonth.projets
            ]);

        })
    ;
});
