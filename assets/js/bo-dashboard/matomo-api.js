import $ from 'jquery';
import ApexCharts from "apexcharts";

const statsMatomo = (host, siteId) => {
    let statsContent = window['statsMatomo'];
    let token = "04c512486c62b78128de1fa730a1dbd0";

    if (statsContent){

        var options = {
            series: [{ name: "", data: [] }],
            xaxis: { categories: [] },
            title: { text: "", align: "left" },
            colors: ["#008000"],
            dataLabels: { enabled: false },
            chart: {
                type: "area",
                stacked: false,
                height: 350,
                zoom: {
                    type: "x",
                    enabled: true,
                    autoScaleYaxis: true,
                },
                toolbar: {
                    autoSelected: "zoom",
                },
            },
        };

        fetch(`${host}/index.php?module=API&method=VisitsSummary.getVisits&idSite=1&period=day&date=last90&format=json`)
            .then(response => response.json())
            .then(datas => {
                options.title.text = "Visites";
                options.xaxis.categories = Object.keys(datas);
                options.series[0].data = Object.values(datas);

                var chart = new ApexCharts(
                    document.querySelector("#VisitsSummary_getVisits"),
                    options
                );
                chart.render();
            });

        fetch(`${host}/index.php?module=API&method=UserCountry.getCountry&idSite=1&period=year&date=today&format=JSON`)
            .then(response => response.json())
            .then(datas => {
                datas.forEach(data => {
                    $('#UserCountry_getCountry tbody').append(`<tr>
                        <td>${data.label} <img src="${host + data.logo}" class="float-right" alt="${data.label}_logo" width="30"></td>
                        <td>${data.nb_visits}</td>
                        <td>${data.nb_actions}</td>
                    </tr>`)
                });
            });

        fetch(`${host}/index.php?module=API&method=ImageGraph.get&idSite=1&period=month&date=today&apiModule=UserCountry&apiAction=getCountry&outputType=0&showLegend=1&fontSize=9&aliasedGraph=1&textColor=222222&backgroundColor=FFFFFF&gridColor=CCCCCC&legendAppendMetric=1&format=JSON&token_auth=04c512486c62b78128de1fa730a1dbd0&force_api_session=1`)
            .then(res=>{return res.blob()})
            .then(blob=>{
                var img = URL.createObjectURL(blob);

                $('#UserCountry_getCountry').append(`<img src="${img}" class="w-100"/>`)
            });



        fetch(`${host}/index.php?module=API&method=Actions.getPageUrls&idSite=1&period=month&date=today&format=JSON`)
            .then(response => response.json())
            .then(datas => {
                console.log(datas);
                // datas.forEach(data => {
                //     $('#UserCountry_getCountry tbody').append(`<tr>
                //         <td>${data.label} <img src="${host + data.logo}" class="float-right" alt="${data.label}_logo" width="30"></td>
                //         <td>${data.nb_visits}</td>
                //         <td>${data.nb_actions}</td>
                //     </tr>`)
                // });
            });
    }

    //
};

export default statsMatomo;
