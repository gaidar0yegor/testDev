import gauge from 'chartjs-gauge';

const displayGraph = year => {
    if (!window['projets-efficacite-moyenne']) {
        return;
    }

    let efficaciteMoyenneDiv = window['efficacite-moyenne'];

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
            top: 20,
            bottom: 10
        }
    };

    fetch(`/corp/api/dashboard/projets-efficacite-moyenne/${year}`)
        .then(response => response.json())
        .then(efficaciteMoyenne => {
            const efficacite = Math.round(efficaciteMoyenne * 100) / 100;
            new Chart(
                efficaciteMoyenneDiv.getContext("2d"),
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

            document.querySelector('[data-target-highlight="efficacite-moyenne"]').innerText = efficacite;
        })
    ;
};

window.addEventListener('loadYearlyCharts', event => displayGraph(parseInt(event.detail.year, 10)));