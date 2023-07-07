import $ from 'jquery';
import ApexCharts from "apexcharts";
import { domDatatable, btnsDatatable } from './../datatable.js';
import {language_dt} from "../translation";

const statsMatomo = (config) => {
    let statsContent = window['statsMatomo'];

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

        // START :: VisitsSummary getVisits

        $('#VisitsSummary_getVisits').on('click', '.scale-period-visits', function (e) {
            $('#VisitsSummary_getVisits .scale-period-visits').removeClass('active');
            $(this).addClass('active');

            let apiDate,
                period = $(this).data('period'),
                startDate = new Date('2020-01-01'),
                endDate = new Date();

            switch (period) {
                case 'day':
                    apiDate = 'last365';
                    break;
                case 'week':
                    apiDate = 'last' + (Math.ceil(Math.abs(endDate.getTime() - startDate.getTime()) / (1000 * 60 * 60 * 24 * 7)));
                    break;
                case 'month':
                    apiDate = 'last' + (endDate.getMonth() - startDate.getMonth() + 12 * (endDate.getFullYear() - startDate.getFullYear()));
                    break;
                default:
                    apiDate = 'last' + (endDate.getFullYear() - startDate.getFullYear() + 1);
                    break;
            }

            fetch(`${config.host}/index.php?module=API&method=VisitsSummary.getVisits&idSite=${config.siteId}&period=${period}&date=${apiDate}&format=json`)
                .then(response => response.json())
                .then(datas => {
                    options.xaxis.categories = Object.keys(datas);
                    options.series[0].data = Object.values(datas);
                    let getVisitsChartDiv = document.querySelector("#VisitsSummary_getVisits .getVisits-chart");
                    getVisitsChartDiv.innerHTML = "";

                    var chart = new ApexCharts(
                        getVisitsChartDiv,
                        options
                    );
                    chart.render();
                });
        });

        $('#VisitsSummary_getVisits .scale-period-visits.active').trigger('click');

        // END :: VisitsSummary getVisits

        fetch(`${config.host}/index.php?module=API&method=UserCountry.getCountry&idSite=${config.siteId}&period=year&date=today&format=JSON`)
            .then(response => response.json())
            .then(datas => {
                datas.forEach(data => {
                    $('#UserCountry_getCountry tbody').append(`<tr>
                        <td>${data.label} <img src="${config.host + data.logo}" class="float-right" alt="${data.label}_logo" width="30"></td>
                        <td>${data.nb_visits}</td>
                        <td>${data.nb_actions}</td>
                    </tr>`)
                });
            });

        // img UserCountry

        fetch(`${config.host}/index.php?module=API&method=ImageGraph.get&idSite=${config.siteId}&period=month&date=today&apiModule=UserCountry&apiAction=getCountry&outputType=0&showLegend=1&fontSize=9&aliasedGraph=1&textColor=222222&backgroundColor=FFFFFF&gridColor=CCCCCC&legendAppendMetric=1&format=JSON`)
            .then(res=>{return res.blob()})
            .then(blob=>{
                var img = URL.createObjectURL(blob);

                $('#UserCountry_getCountryGraph').append(`<tr><td><img src="${img}" class="w-100"/></td></tr>`)
            });



        // fetch(`${config.host}/index.php?module=API&method=Actions.getPageUrls&idSite=${config.siteId}&period=month&date=today&format=JSON`)
        fetch(`${config.host}/index.php?module=API&format=JSON&idSite=${config.siteId}&period=month&date=today&method=API.getProcessedReport&apiModule=Actions&apiAction=getPageTitles`)
            .then(response => response.json())
            .then(datas => {
                $('#Actions_getPageTitles thead').append(`<tr>
                        <td>${datas.columns.label}</td>
                        <td>${datas.columns.nb_hits}</td>
                        <td>${datas.columns.nb_visits}</td>
                        <td>${datas.columns.bounce_rate}</td>
                        <td>${datas.columns.avg_time_on_page}</td>
                        <td>${datas.columns.exit_rate}</td>
                    </tr>`)
                datas.reportData.forEach(columnData => {
                    $('#Actions_getPageTitles tbody').append(`<tr>
                        <td class="text-left">${columnData.label}</td>
                        <td>${columnData.nb_hits}</td>
                        <td>${columnData.nb_visits}</td>
                        <td>${columnData.bounce_rate}</td>
                        <td>${columnData.avg_time_on_page}</td>
                        <td>${columnData.exit_rate}</td>
                    </tr>`)
                });
                initDatatable('#Actions_getPageTitles table');
            });
    }
};

const initDatatable = ($table) => {
    $($table).DataTable( {
        dom: domDatatable,
        buttons: btnsDatatable,
        searchHighlight: true,
        responsive: true,
        language: language_dt,
    });
};

export default statsMatomo;
