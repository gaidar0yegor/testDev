import c3 from 'c3';
import $ from "jquery";

const chartContents = window['projet-stats-charts'];

if (chartContents) {
    let efficaciteDiv = window['efficacite'];
    let effectiviteDiv = window['effectivite'];
    let efficacite = efficaciteDiv.dataset.efficacite ? parseFloat(efficaciteDiv.dataset.efficacite) : 0;
    let effectivite = effectiviteDiv.dataset.effectivite ? parseFloat(effectiviteDiv.dataset.effectivite) : 0;

    const efficaciteChart = c3.generate({
        bindto: efficaciteDiv,
        data: {
            columns: [
                ['Efficacite', efficacite]
            ],
            type: 'gauge'
        },
        gauge: {
            label: {
                format: function (value, ratio) {
                    return value;
                },
                show: true // to turn off the min/max labels.
            },
            min: -1,
            max: 1,
            units: '',
        },
        color: {
            pattern: ['#FF0000', '#F97600', '#F6C600', '#60B044'],
            threshold: {
                max: 1,
                unit: 'value',
                values: [-0.66, -0.33, 0, 1]
            }
        },
        size: {
            height: 250
        }
    });

    const effectiviteChart = c3.generate({
        bindto: effectiviteDiv,
        data: {
            columns: [
                ['Effectivite', effectivite]
            ],
            type: 'gauge'
        },
        color: {
            pattern: ['#df7e08', '#d1d7da', '#f9ad0e'],
            threshold: {
                values: [33, 66, 100]
            }
        },
        size: {
            height: 250
        }
    });
}

