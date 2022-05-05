import gauge from 'chartjs-gauge';

const chartContents = window['projet-stats-charts'];

if (chartContents) {
    let efficaciteDiv = window['efficacite'];
    let effectiviteDiv = window['effectivite'];

    Chart.defaults.gauge.responsive = true;
    Chart.defaults.gauge.needle = {
        radiusPercentage: 2,
        widthPercentage: 3.2,
        lengthPercentage: 100,
        color: 'rgb(69,174,243)',
    };
    Chart.defaults.gauge.valueLabel = {
        display: true,
        bottomMarginPercentage: 10,
        fontSize: 35,
        color: 'rgba(0, 0, 0, 1)',
        backgroundColor: 'rgb(52,92,183,0)',
        borderRadius: 5,
        padding: {
            top: 10,
            bottom: 10
        }
    };

    if (efficaciteDiv){
        let efficacite = efficaciteDiv.dataset.efficacite ? parseFloat(efficaciteDiv.dataset.efficacite) : 0;

        new Chart(
            efficaciteDiv.getContext("2d"),
            {
                type: 'gauge',
                data: {
                    datasets: [{
                        value: efficacite,
                        minValue: -1,
                        maxValue: 1,
                        data: [-1, -0.66, -0.33, 0, 0.5, 1],
                        backgroundColor: ['', '#FF0000', '#F97600', '#F6C600', '#60B044', '#308428'],
                    }]
                }
            });
    }

    if (effectiviteDiv){
        let effectivite = effectiviteDiv.dataset.effectivite ? parseFloat(effectiviteDiv.dataset.effectivite) : 0;

        new Chart(
            effectiviteDiv.getContext("2d"),
            {
                type: 'gauge',
                data: {
                    datasets: [{
                        value: effectivite,
                        minValue: 0,
                        maxValue: 100,
                        data: [0, 0.33, 0.66, 1],
                        backgroundColor: ['','#A15D3F', '#B0B6BC', '#F6C600'],
                    }]
                },
                options: {
                    valueLabel: {
                        formatter: (value) => {
                            return Math.round(value * 100) + "%";
                        }
                    }
                }
            });
    }
}

