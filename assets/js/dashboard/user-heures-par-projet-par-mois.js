import c3 from 'c3';
import {formatHours} from './utils';

const axe_X = ['_months', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

const chart = c3.generate({
    bindto: '#user-heures-par-projet-par-mois',
    data: {
        type: 'bar',
        x: '_months',
        columns: [],
        groups: []
    },
    axis: {
        x: {
            type: 'category',
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

            tempsPerProjetPerMonth.heures.push(axe_X);

            chart.load({
                columns: tempsPerProjetPerMonth.heures,
            });

            chart.groups([
                tempsPerProjetPerMonth.projets
            ]);

        })
    ;
});
