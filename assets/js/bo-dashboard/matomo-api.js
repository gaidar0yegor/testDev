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

        fetch(`${config.host}/index.php?module=API&method=VisitsSummary.getVisits&idSite=${config.siteId}&period=day&date=last90&format=json`)
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
