import ApexCharts from "apexcharts";

var options = {
  series: [
    {
      name: "",
      data: [],
    },
  ],
  xaxis: {
    categories: [],
  },
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
  dataLabels: {
    enabled: false,
  },
  markers: {
    size: 0,
  },
  title: {
    text: "Utilisateurs créés",
    align: "left",
  },
  fill: {
    type: "gradient",
    gradient: {
      shadeIntensity: 1,
      inverseColors: false,
      opacityFrom: 0.5,
      opacityTo: 0,
      stops: [0, 90, 100],
    },
  },
};

fetch(`/back-office/api/historique-user`)
  .then((response) => response.json())
  .then((data) => {
    var axes = data.axes;
    window.historiqueData.users = data.data;
    options.xaxis.categories = axes.x;
    options.series[0].data = axes.y;

    var chart = new ApexCharts(
      document.querySelector("#allTimeUserChart"),
      options
    );
    chart.render();
  });
