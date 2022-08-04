import $ from "jquery";
import { t } from './translation';

const chartDiv = window['users_list_organigramme'];
let avatarPublicUrl;
let orgChartRows = [];

if (chartDiv){
    google.charts.load('current', {packages:["orgchart"]});
    google.charts.setOnLoadCallback(drawChart);

    $(document).on('click', '.show_user_organigramme', function () {
        $('#users_list_organigramme').hide();
        chartDiv.dataset.societeUserId = $(this).data('societeUserId');
        drawChart();
        $('#users_list_organigramme').show();

        $([document.documentElement, document.body]).animate({
            scrollTop: $('#users_list_organigramme').offset().top
        }, 1000);
    });
}

function drawChart() {
    var superiorId = chartDiv.dataset.societeUserId;
    chartDiv.innerHTML = "";
    fetch(`/corp/api/equipe/organigramme/${superiorId}`)
        .then(response => response.json())
        .then(response => {
            avatarPublicUrl = response.avatarPublicUrl;
            orgChartRows = [];
            pushRow(response.data, '');


            var googleData = new google.visualization.DataTable();
            googleData.addColumn('string', 'Name');
            googleData.addColumn('string', 'Superior');
            googleData.addRows(orgChartRows);

            var chart = new google.visualization.OrgChart(chartDiv);
            chart.draw(googleData, {'allowHtml':true});
        });
}

function pushRow(data, parent) {
    orgChartRows.push([
        {
            'v' : data.id + '',
            'f' : generateHtmlBloc(data),
        },
        parent + ''
    ]);

    if (data.hasOwnProperty('teamMembers') && data.teamMembers.length > 0){
        $.each(data.teamMembers, function(i, obj) {
            pushRow(obj, data.id)
        });
    }
}

function generateHtmlBloc(data) {
    return `
        <div>
            <img src="${avatarPublicUrl + data.user.avatar.nomMd5}"
                alt="${data.user.prenom} ${data.user.nom}"
                class="rounded-circle mb-2" width="60" height="60"/>
            <p><strong>${data.user.prenom} ${data.user.nom}</strong></p>
            <p>${t(data.role)}</p>
            <p><i>${data.user.email}</i></p>
        </div>`;
}