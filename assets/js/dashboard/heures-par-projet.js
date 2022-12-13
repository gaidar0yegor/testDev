import c3 from 'c3';
import $ from "jquery";
import {formatHours} from './utils';

const chart = c3.generate({
    bindto: '#heures-par-projet',
    data: {
        type: 'bar',
        x: '_projects_year',
        columns: [],
        colors: [],
    },
    legend: {
        position: 'bottom',
        // inset: {
        //         anchor: 'bottom',
        //         x: 150,
        //         y: -110,
        //         step: -1
        //     }
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
    padding: {
        bottom: 20
    }
});

window.addEventListener('loadYearlyCharts', event => {
    const {year} = event.detail;

    chart.unload();

    setTimeout(function () {
        fetch(`/corp/api/dashboard/heures-par-projet/${year}`)
            .then(response => response.json())
            .then(datas => {
                let heuresParProjet = datas.userProjetsHeuresPassees;
                const sumHeures = Object.values(heuresParProjet).reduce((a, b) => a + b);

                heuresParProjet._projects_year = year;

                // let projectsNames = Object.keys(heuresParProjet.forEach((item, index)=>{
                //     const spaces = 'ㅤ';
                //     projectsNames[item] = item + spaces;
                //     console.log(projectsNames[item]);
                // }));


                chart.load({
                    unload: true,
                    columns: Object.keys(heuresParProjet).map(projetName => [projetName, heuresParProjet[projetName]]),
                    colors: datas.codeColors,
                });

                document.querySelector('[data-target-highlight="heures-par-projet"]').innerText = formatHours(sumHeures);
            })
        ;
    }, 1000);
});
